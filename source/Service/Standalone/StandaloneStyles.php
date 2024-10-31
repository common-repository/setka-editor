<?php
namespace Setka\Editor\Service\Standalone;

use Setka\Editor\Admin\Options\ForceUseSetkaCDNOption;
use Setka\Editor\Admin\Options\Standalone\StylesOption;
use Setka\Editor\Admin\Options\Standalone\UseAssetsAndUseStylesOption;
use Setka\Editor\Admin\Options\Standalone\UseCriticalOption;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\Styles\AbstractStyles;

class StandaloneStyles extends AbstractStyles
{
    /**
     * @var boolean
     */
    private $configReady;

    /**
     * @var boolean
     */
    private $forceUseSetkaCDN;

    /**
     * @var boolean
     */
    private $useCritical;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var array
     */
    private $urls = array();

    /**
     * StandaloneStyles constructor.
     *
     * @param StylesOption $styles
     * @param UseAssetsAndUseStylesOption $useStyles
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param PostThemePostMeta $postThemePostMeta
     * @param PostLayoutPostMeta $postLayoutPostMeta
     * @param array $postTypes
     * @param string $assetIDPrefix
     * @param ForceUseSetkaCDNOption $forceUseSetkaCDNOption
     * @param UseCriticalOption $useCriticalOption
     * @param string $baseUrl
     */
    public function __construct(
        StylesOption $styles,
        UseAssetsAndUseStylesOption $useStyles,
        UseEditorPostMeta $useEditorPostMeta,
        PostThemePostMeta $postThemePostMeta,
        PostLayoutPostMeta $postLayoutPostMeta,
        array $postTypes,
        string $assetIDPrefix,
        ForceUseSetkaCDNOption $forceUseSetkaCDNOption,
        UseCriticalOption $useCriticalOption,
        string $baseUrl
    ) {
        parent::__construct(
            $styles,
            $useStyles,
            $useEditorPostMeta,
            $postThemePostMeta,
            $postLayoutPostMeta,
            $postTypes,
            $assetIDPrefix
        );
        $this->forceUseSetkaCDN = $forceUseSetkaCDNOption->get();
        $this->baseUrl          = trailingslashit($baseUrl);
        $this->useCritical      = $useCriticalOption->get();
        $this->configReady      = $styles->isValid();
    }

    protected function executeBuild(): void
    {
        $collectUrl = array($this, 'collectUrl');
        if ($this->isCriticalReadyToUse()) {
            $collectID = array($this, 'collectID');

            $sectionConfigs = array(
                array(StylesOption::COMMON_CRITICAL, $collectID, null),
                array(StylesOption::COMMON_DEFERRED, $collectUrl, null),
                array(StylesOption::THEMES_CRITICAL, $collectID, $this->required[StylesOption::THEMES]),
                array(StylesOption::THEMES_DEFERRED, $collectUrl, $this->required[StylesOption::THEMES]),
                array(StylesOption::LAYOUTS,         $collectID, $this->required[StylesOption::LAYOUTS]),
            );
        } else {
            $sectionConfigs = array(
                array(StylesOption::COMMON, $collectUrl, null),
                array(StylesOption::THEMES, $collectUrl, $this->required[StylesOption::THEMES]),
                array(StylesOption::LAYOUTS, $collectUrl, $this->required[StylesOption::LAYOUTS]),
            );
        }

        foreach ($sectionConfigs as &$sectionConfig) {
            $this->collect($sectionConfig[0], $sectionConfig[1], $sectionConfig[2]);
        }
    }

    protected function collectUrl(string $sectionName, array $file): void
    {
        $this->urls[implode(
            '-',
            array($this->assetIDPrefix, $sectionName, $file[StylesOption::FILE_ID])
        )] = $this->buildUrl($file);
        $this->collectFonts($sectionName, $file);
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     * @param array $file
     *
     * @return string
     */
    private function buildUrl(array $file): string
    {
        if (isset($file[StylesOption::FILE_WP_PATH]) && parent::isReadyToUse() && !$this->forceUseSetkaCDN) {
            return $this->baseUrl . untrailingslashit($file[StylesOption::FILE_WP_PATH]);
        } else {
            return $file[StylesOption::FILE_URL];
        }
    }

    /**
     * Method is different because Standalone styles can be used even if sync doesn't finished yet.
     * @inheritDoc
     */
    public function isReadyToUse(): bool
    {
        return $this->configReady;
    }

    /**
     * @return bool
     */
    private function isCriticalReadyToUse(): bool
    {
        return $this->useCritical && $this->configReady && parent::isReadyToUse();
    }
}
