<?php
namespace Setka\Editor\Service\QuerySniffer;

use Setka\Editor\Admin\Service\WPQueryFactory;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\AMP\AMP;
use Setka\Editor\Service\Config\AMPConfig;
use Setka\Editor\Service\QuerySniffer\Exceptions\AMPStylesNotReadyException;
use Setka\Editor\Service\QuerySniffer\Exceptions\UseCombinedStylesException;
use Setka\Editor\Service\ScriptStyles;
use Setka\Editor\Service\Standalone\StandaloneStyles;
use Setka\Editor\Service\WPPostFactory;

class QuerySniffer
{
    /**
     * @var \WP_Query
     */
    private $main;

    /**
     * @var \WP_Query[]
     */
    private $queries = array();

    /**
     * @var boolean True if Gutenberg available and should be used.
     */
    private $gutenbergSupport;

    /**
     * @var AMP
     */
    private $amp;

    /**
     * @var StandaloneStyles
     */
    private $standalone;

    /**
     * @var UseEditorPostMeta
     */
    private $useEditorPostMeta;

    /**
     * @var ScriptStyles
     */
    private $scriptStyles;

    /**
     * @var boolean
     */
    private $postFound = false;

    /**
     * QuerySniffer constructor.
     *
     * @param bool $gutenbergSupport
     * @param \WP_Query $mainQuery
     * @param AMP $amp
     * @param StandaloneStyles $standaloneStyles
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param ScriptStyles $scriptStyles
     */
    public function __construct(
        bool $gutenbergSupport,
        \WP_Query $mainQuery,
        AMP $amp,
        StandaloneStyles $standaloneStyles,
        UseEditorPostMeta $useEditorPostMeta,
        ScriptStyles $scriptStyles
    ) {
        $this->queries[]         = $mainQuery;
        $this->main              = $mainQuery;
        $this->gutenbergSupport  = $gutenbergSupport;
        $this->amp               = $amp;
        $this->standalone        = $standaloneStyles;
        $this->useEditorPostMeta = $useEditorPostMeta;
        $this->scriptStyles      = $scriptStyles;
    }

    /**
     * Main method.
     */
    public function scan(): void
    {
        try {
            $this->scanQueries();

            if ($this->postFound) {
                if ($this->isAmp()) {
                    $this->finishAMPScan();
                } else {
                    $this->finishStandaloneScan();
                }
            }
        } catch (UseCombinedStylesException $exception) {
            if ($this->postFound) {
                $this->finishRegularScan();
            }
        } catch (AMPStylesNotReadyException $exception) {
        }
    }

    /**
     * @throws AMPStylesNotReadyException
     * @throws UseCombinedStylesException
     */
    private function scanQueries(): void
    {
        foreach ($this->queries as $query) {
            if (!$this->isValidQuery($query)) {
                continue;
            }

            if ($this->isMainQueryAndNotSingular($query)) {
                // Don't enqueue anything on page with multiple posts.
                continue;
            }

            foreach ($query->posts as $post) {
                if (!$this->isValidPost($post)) {
                    continue;
                }

                if ($this->isSetkaPost($post)) {
                    $this->postFound = true;
                    $this->scanPost($post);
                }
            }
        }
    }

    /**
     * @param \WP_Post $post
     * @throws AMPStylesNotReadyException
     * @throws UseCombinedStylesException
     */
    private function scanPost(\WP_Post $post): void
    {
        if ($this->isAmp()) {
            if ($this->amp->isReadyToUse()) {
                $this->amp->requireForPost($post);
                if ($this->gutenbergSupport) {
                    do_blocks($post->post_content);
                }
            } else {
                throw new AMPStylesNotReadyException();
            }
        } else {
            if ($this->standalone->isReadyToUse()) {
                $this->standalone->requireForPost($post);
                if ($this->gutenbergSupport) {
                    do_blocks($post->post_content);
                }
            } else {
                throw new UseCombinedStylesException();
            }
        }
    }

    private function finishAMPScan(): void
    {
        $this->amp->build();
        $this->scriptStyles
            ->setAmpCSS($this->amp->getCSS())
            ->setFonts($this->amp->getFonts())
            ->enableAMPAssets();
    }

    private function finishStandaloneScan(): void
    {
        $this->standalone->build();
        $this->scriptStyles
            ->setStandaloneCriticalCSS($this->standalone->getCSS())
            ->setStandalone($this->standalone->getUrls())
            ->setFonts($this->standalone->getFonts())
            ->enableStandaloneAssets();
    }

    private function finishRegularScan(): void
    {
        $this->scriptStyles->enableRegularAssets();
    }

    /**
     * @param \WP_Query $query
     *
     * @return bool
     */
    private function isValidQuery(\WP_Query $query): bool
    {
        return WPQueryFactory::isValidAndNotEmptyQuery($query);
    }

    /**
     * @param mixed $post
     *
     * @return bool
     */
    private function isValidPost($post): bool
    {
        return WPPostFactory::isValidPost($post);
    }

    /**
     * @param \WP_Query $query
     *
     * @return bool
     */
    private function isMainQueryAndNotSingular(\WP_Query $query): bool
    {
        return $this->main === $query && !$query->is_singular();
    }

    /**
     * @param \WP_Post $post
     *
     * @return boolean True if post created (contains) in Setka Editor.
     */
    private function isSetkaPost(\WP_Post $post): bool
    {
        return $this->useEditorPostMeta->setPostId($post->ID)->get();
    }

    /**
     * Check if we should handle request as AMP.
     * @return bool True if AMP page requested and we should use AMP styles.
     */
    private function isAmp(): bool
    {
        return $this->amp->isAmpSupport() && $this->isAMPEndpoint();
    }

    /**
     * @return bool
     */
    private function isAMPEndpoint(): bool
    {
        return AMPConfig::isAMPEndpoint();
    }

    /**
     * @param \WP_Query $query
     * @return $this
     */
    public function addQuery(\WP_Query $query): QuerySniffer
    {
        $this->queries[] = $query;
        return $this;
    }
}
