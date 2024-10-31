<?php
namespace Setka\Editor\Service\Styles;

use Setka\Editor\Admin\Options\Styles\AbstractStylesAggregateOption;
use Setka\Editor\Admin\Options\Styles\AbstractUseStylesOption;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\PostStatuses;

abstract class AbstractStyles
{
    /**
     * @var AbstractStylesAggregateOption
     */
    private $configOption;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var boolean
     */
    private $readyToUse;

    /**
     * @var UseEditorPostMeta
     */
    private $useEditorPostMeta;

    /**
     * @var PostThemePostMeta
     */
    private $postThemePostMeta;

    /**
     * @var PostLayoutPostMeta
     */
    private $postLayoutPostMeta;

    /**
     * @var array
     */
    private $postTypes;

    /**
     * @var string
     */
    protected $assetIDPrefix;

    /**
     * @var boolean
     */
    private $built = false;

    /**
     * @var integer[]
     */
    protected $IDs = array();

    /**
     * @var string CSS styles for current page.
     */
    protected $CSS = '';

    /**
     * @var string[] List of URL of fonts for current page.
     */
    protected $fonts = array();

    /**
     * @var array
     */
    protected $required = array(
        AbstractStylesAggregateOption::COMMON => array(),
        AbstractStylesAggregateOption::THEMES => array(),
        AbstractStylesAggregateOption::LAYOUTS => array(),
    );

    /**
     * AbstractStyles constructor.
     *
     * @param AbstractStylesAggregateOption $configOption
     * @param AbstractUseStylesOption $readyToUse
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param PostThemePostMeta $postThemePostMeta
     * @param PostLayoutPostMeta $postLayoutPostMeta
     * @param array $postTypes
     * @param string $assetIDPrefix
     */
    public function __construct(
        AbstractStylesAggregateOption $configOption,
        AbstractUseStylesOption $readyToUse,
        UseEditorPostMeta $useEditorPostMeta,
        PostThemePostMeta $postThemePostMeta,
        PostLayoutPostMeta $postLayoutPostMeta,
        array $postTypes,
        string $assetIDPrefix
    ) {
        $this->configOption       = $configOption;
        $this->readyToUse         = $readyToUse->get();
        $this->useEditorPostMeta  = $useEditorPostMeta;
        $this->postThemePostMeta  = $postThemePostMeta;
        $this->postLayoutPostMeta = $postLayoutPostMeta;
        $this->postTypes          = $postTypes;
        $this->assetIDPrefix      = $assetIDPrefix;
    }

    public function build(): void
    {
        if (!$this->built && $this->isReadyToUse() && !$this->isRequiredEmpty()) {
            $this->setup();
            $this->executeBuild();
            $this->setupInlineCSS();
            $this->built = true;
        }
    }

    private function setup(): void
    {
        $this->config = $this->configOption->get();
    }

    abstract protected function executeBuild(): void;

    protected function collect(string $sectionName, array $collector, ?array $required = null): void
    {
        if (is_array($required)) {
            foreach ($required as $slug => $true) {
                $key = array_search(
                    (string) $slug,
                    array_column($this->config[$sectionName], AbstractStylesAggregateOption::FILE_ID),
                    true
                );
                if (is_int($key) || is_string($key)) {
                    call_user_func($collector, $sectionName, $this->config[$sectionName][$key]);
                }
            }
        } else {
            foreach ($this->config[$sectionName] as &$file) {
                call_user_func($collector, $sectionName, $file);
            }
        }
    }

    protected function collectID(string $sectionName, array $file): void
    {
        $this->IDs[] = $file[AbstractStylesAggregateOption::FILE_WP_ID];
        $this->collectFonts($sectionName, $file);
    }

    protected function collectFonts(string $sectionName, array $file): void
    {
        if (isset($file[AbstractStylesAggregateOption::FILE_FONTS])) {
            foreach ($file[AbstractStylesAggregateOption::FILE_FONTS] as $key => $url) {
                $this->fonts[implode(
                    '-',
                    array(
                        $this->assetIDPrefix,
                        $sectionName,
                        $file[AbstractStylesAggregateOption::FILE_ID],
                        'font',
                        $key
                    )
                )] = $url;
            }
        }
    }

    private function setupInlineCSS(): void
    {
        if (empty($this->IDs)) {
            return;
        }

        $query = new \WP_Query(array(
            'post_type' => $this->postTypes,
            'post_status' => PostStatuses::PUBLISH,
            'post__in' => $this->IDs,
            'orderby' => 'post__in',
            'posts_per_page' => count($this->IDs),
        ));

        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $this->CSS .= $post->post_content;
            }
        }
    }

    /**
     * @param \WP_Post $post
     *
     * @return $this
     */
    public function requireForPost(\WP_Post $post): AbstractStyles
    {
        $this->preparePostMetas($post);
        if ($this->useEditorPostMeta->get()) {
            $this->requireTheme($this->postThemePostMeta->get());
            $this->requireLayout($this->postLayoutPostMeta->get());
        }
        return $this;
    }

    /**
     * @param \WP_Post $post
     */
    private function preparePostMetas(\WP_Post $post): void
    {
        foreach (array($this->useEditorPostMeta, $this->postThemePostMeta, $this->postLayoutPostMeta) as $meta) {
            $meta->setPostId($post->ID)->deleteLocal();
        }
    }

    /**
     * @param string $theme
     *
     * @return $this
     */
    public function requireTheme($theme): AbstractStyles
    {
        if (is_string($theme) && !empty($theme)) {
            $this->required[AbstractStylesAggregateOption::THEMES][$theme] = true;
        }
        return $this;
    }

    /**
     * @param string $layout
     *
     * @return $this
     */
    public function requireLayout($layout): AbstractStyles
    {
        if (is_string($layout) && !empty($layout)) {
            $this->required[AbstractStylesAggregateOption::LAYOUTS][$layout] = true;
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function isRequiredEmpty(): bool
    {
        foreach ($this->required as $section) {
            if (!empty($section)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isReadyToUse(): bool
    {
        return $this->readyToUse;
    }

    /**
     * Return inline CSS.
     *
     * @return string
     */
    public function getCSS(): string
    {
        return $this->CSS;
    }

    /**
     * Return fonts.
     *
     * @return array List of required fonts.
     */
    public function getFonts(): array
    {
        return $this->fonts;
    }
}
