<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Admin\Options\AMP\AMPCssOption;
use Setka\Editor\Admin\Options\AMP\AMPFontsOption;
use Setka\Editor\Admin\Options\AMP\AMPStylesOption;
use Setka\Editor\Admin\Options\AMP\UseAMPStylesOption;
use Setka\Editor\Plugin;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\Styles\AbstractStyles;

class AMP extends AbstractStyles
{
    /**
     * @var boolean True if AMP plugin active.
     */
    private $ampSupport = false;

    /**
     * @var ?string Name of AMP mode (classic, native, paired).
     */
    private $mode;

    /**
     * @var AMPCssOption CSS for AMP pages.
     */
    private $ampCSSOption;

    /**
     * @var AMPFontsOption Font urls for AMP pages.
     */
    private $ampFontsOption;

    /**
     * @var boolean True if we should add animation JS components in classic mode.
     */
    protected $animations = false;

    /**
     * AMP constructor.
     *
     * @param AMPStylesOption $ampStylesOption
     * @param UseAMPStylesOption $useAMPStylesOption
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param PostThemePostMeta $postThemePostMeta
     * @param PostLayoutPostMeta $postLayoutPostMeta
     * @param array $postTypes
     * @param string $assetIDPrefix
     * @param bool $ampSupport
     * @param ?string $mode
     * @param AMPCssOption $ampCSSOption
     * @param AMPFontsOption $ampFontsOption
     */
    public function __construct(
        AMPStylesOption $ampStylesOption,
        UseAMPStylesOption $useAMPStylesOption,
        UseEditorPostMeta $useEditorPostMeta,
        PostThemePostMeta $postThemePostMeta,
        PostLayoutPostMeta $postLayoutPostMeta,
        array $postTypes,
        string $assetIDPrefix,
        bool $ampSupport,
        ?string $mode,
        AMPCssOption $ampCSSOption,
        AMPFontsOption $ampFontsOption
    ) {
        parent::__construct(
            $ampStylesOption,
            $useAMPStylesOption,
            $useEditorPostMeta,
            $postThemePostMeta,
            $postLayoutPostMeta,
            $postTypes,
            $assetIDPrefix
        );

        $this->ampSupport     = $ampSupport;
        $this->mode           = $mode;
        $this->ampCSSOption   = $ampCSSOption;
        $this->ampFontsOption = $ampFontsOption;
    }

    protected function executeBuild(): void
    {
        $collectID = array($this, 'collectID');

        $sectionConfigs = array(
            array(AMPStylesOption::COMMON, $collectID, null),
            array(AMPStylesOption::THEMES, $collectID, $this->required[AMPStylesOption::THEMES]),
            array(AMPStylesOption::LAYOUTS, $collectID, $this->required[AMPStylesOption::LAYOUTS]),
        );

        foreach ($sectionConfigs as &$sectionConfig) {
            $this->collect($sectionConfig[0], $sectionConfig[1], $sectionConfig[2]);
        }

        $this->CSS .= $this->ampCSSOption->get();

        foreach ($this->ampFontsOption->get() as $key => $url) {
            $this->fonts[Plugin::NAME . '-' . $key] = $url;
        }
    }

    /**
     * Modify config for AMP template.
     *
     * Classic mode.
     *
     * @param array $data Config for AMP template.
     * @param \WP_Post $post WordPress post object.
     *
     * @return array Modified data.
     */
    public function classicTemplateData(array $data, \WP_Post $post): array
    {
        $this->requireForPost($post);
        $this->build();
        return $this->updateData($data, $post);
    }

    /**
     * Return CSS for post.
     *
     * Classic mode.
     *
     * @param \WP_Post $post WordPress post object.
     *
     * @return string CSS styles for post.
     */
    public function classicTemplateCss(\WP_Post $post): string
    {
        $this->requireForPost($post);
        $this->build();
        return $this->getCSS();
    }

    /**
     * Add custom fonts on AMP pages and require animations.
     *
     * Classic mode.
     *
     * @param $data array \AMP_Post_Template config.
     * @param $post \WP_Post WordPress post object.
     *
     * @return array Modified data.
     */
    public function updateData(array $data, \WP_Post $post): array
    {
        if ($this->fonts) {
            $data['font_urls'] = array_merge($data['font_urls'], $this->fonts);
        }

        if ($this->animations) {
            $data['amp_component_scripts']['amp-animation']         = true;
            $data['amp_component_scripts']['amp-position-observer'] = true;
        }

        return $data;
    }

    /**
     * AMP plugin enabled or disabled.
     *
     * @return bool True if AMP plugin active.
     */
    public function isAmpSupport(): bool
    {
        return $this->ampSupport;
    }

    /**
     * Return one of three allowed AMP plugin mode.
     *
     * Allowed mode names: classic, paired, native.
     *
     * @return ?string AMP plugin mode.
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * Set animation status (existence) on current page.
     *
     * @param bool $animations Animations status.
     */
    public function setAnimations(bool $animations): void
    {
        $this->animations = $animations;
    }
}
