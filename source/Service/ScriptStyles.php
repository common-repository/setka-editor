<?php
namespace Setka\Editor\Service;

use Korobochkin\WPKit\Pages\PageInterface;
use Korobochkin\WPKit\ScriptsStyles\AbstractScriptsStyles;
use Korobochkin\WPKit\ScriptsStyles\ScriptsStylesInterface;
use Setka\Editor\Admin\Notices\NoticesStack;
use Setka\Editor\Admin\Service\Js\EditorAdapterJsSettings;
use Setka\Editor\Plugin;
use Setka\Editor\Service\Gutenberg\EditorGutenbergModule;
use Setka\Editor\Service\Gutenberg\SetkaEditorBlock;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class ScriptStyles extends AbstractScriptsStyles implements ScriptsStylesInterface
{
    /**
     * @var boolean
     */
    private $regularAssets = false;

    /**
     * @var boolean
     */
    private $standaloneAssets = false;

    /**
     * @var boolean
     */
    private $ampAssets = false;

    /**
     * @var string
     */
    private $ampCSS;

    /**
     * @var array Fonts CSS urls.
     */
    private $fonts = array();

    /**
     * @var array
     */
    private $standalone = array();

    /**
     * @var string
     */
    private $standaloneCriticalCSS;

    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var PageInterface
     */
    private $pluginSettingsPage;

    /**
     * @var EditorGutenbergModule
     */
    private $editorGutenbergModule;

    /**
     * @var array List of script names with async attr.
     */
    private $asyncScripts = array();

    /**
     * @var EditorAdapterJsSettings
     */
    private $editorAdapterJsSettings;

    /**
     * @var NoticesStack
     */
    private $noticesStack;

    /**
     * This function register most of CSS and JS files for plugin. It's just registered, not enqueued,
     * so we (or someone else) can enqueue this files only by need. Fired (attached) to `wp_enqueue_scripts` action
     * in \Setka\Editor\Plugin::run().
     *
     * @since 0.0.1
     *
     * @see \Setka\Editor\Plugin::run()
     */
    public function register()
    {
        $prefix  = Plugin::NAME . '-';
        $url     = $this->getBaseUrl();
        $version = Plugin::VERSION;
        $debug   = $this->isDev();

        wp_register_script(
            'uri-js',
            $url . 'assets/build/uri-js/' . (($debug) ? 'URI.js' : 'URI.min.js' ),
            array(),
            $version,
            true
        );

        wp_register_script(
            'dompurify',
            $url . 'assets/build/dompurify/' . (($debug) ? 'purify.js' : 'purify.min.js'),
            array(),
            $version,
            true
        );

        // Setka Editor JS
        wp_register_script(
            $prefix . 'editor',
            $this->setkaEditorAccount->getEditorJSOption()->get(),
            array(),
            $version,
            true
        );

        // Setka Editor CSS
        wp_register_style(
            $prefix . 'editor',
            $this->setkaEditorAccount->getEditorCSSOption()->get(),
            array(),
            $version
        );

        wp_register_style($prefix . 'amp', false); // See enqueueing AMP styles below.
        wp_register_style($prefix . 'standalone', false);

        return $this;
    }

    /**
     * @return $this For chain calls.
     */
    public function registerThemeResources()
    {
        $prefix  = Plugin::NAME . '-';
        $version = Plugin::VERSION;

        // Theme CSS
        wp_register_style(
            $prefix . 'theme-resources',
            $this->setkaEditorAccount->getThemeResourceCSSOption()->get(),
            array(),
            $version
        );

        // Theme Plugins JS
        wp_register_script(
            $prefix . 'theme-plugins',
            $this->setkaEditorAccount->getThemePluginsJSOption()->get(),
            array('jquery'),
            $version,
            true
        );
        $this->asyncScripts[] = $prefix . 'theme-plugins';

        return $this;
    }

    /**
     * @return $this For chain calls.
     */
    public function registerGutenberg()
    {
        wp_register_script(
            'setka-editor-wp-admin-gutenberg-modules',
            $this->getBaseUrl() . 'assets/build/gutenberg-modules.bundle.js',
            array('wp-data', 'wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor'),
            Plugin::VERSION,
            true
        );

        wp_register_style(
            'setka-editor-wp-admin-gutenberg-modules',
            $this->getBaseUrl() . 'assets/build/gutenberg-styles.css',
            array(),
            Plugin::VERSION
        );

        $this->editorGutenbergModule->setBlockType(register_block_type(
            SetkaEditorBlock::NAME,
            array(
                'editor_script' => 'setka-editor-wp-admin-gutenberg-modules',
                'editor_style' => 'setka-editor-wp-admin-gutenberg-modules',
                'render_callback' => array($this->editorGutenbergModule, 'render'),
            )
        ));

        return $this;
    }

    /**
     * Enqueue scripts and styles for Gutenberg edit post page.
     *
     * @return $this For chain calls.
     */
    public function enqueueForGutenberg()
    {
        wp_enqueue_script('setka-editor-editor');
        wp_enqueue_style('setka-editor-editor');
        wp_enqueue_style('setka-editor-theme-resources');

        return $this;
    }

    /**
     * Localise Gutenberg modules.
     *
     * @return $this For chain calls.
     */
    public function localizeGutenbergBlocks()
    {
        global $wp_version;
        wp_localize_script(
            'setka-editor-wp-admin-gutenberg-modules',
            'setkaEditorGutenbergModules',
            array(
                'name' => Plugin::NAME,
                'settings' => $this->editorAdapterJsSettings->getSettings(),
                'settingsUrl' => $this->pluginSettingsPage->getURL(),
                'notices' => $this->noticesStack->getNoticesAsArray(),
                'wpVersion' => $wp_version,
            )
        );

        wp_set_script_translations('setka-editor-wp-admin-gutenberg-modules', Plugin::NAME);

        return $this;
    }

    /**
     * Enqueue resources if they required for this page.
     *
     * Function fired on wp_enqueue_scripts action.
     *
     * @see \Setka\Editor\Plugin::run()
     */
    public function enqueue()
    {
        if ($this->ampAssets) {
            $this->enqueueAMPAssets()->enqueueFonts();
        } elseif ($this->standaloneAssets) {
            $this->enqueueStandaloneAssets();
        } elseif ($this->regularAssets && $this->setkaEditorAccount->isThemeResourcesAvailable()) {
            $this->enqueueRegularAssets();
        }
    }

    /**
     * Enqueue CSS for AMP pages with Setka posts.
     * @return $this For chain calls.
     */
    public function enqueueAMPAssets()
    {
        wp_enqueue_style(Plugin::NAME . '-amp');
        wp_add_inline_style(Plugin::NAME . '-amp', $this->ampCSS);

        return $this;
    }

    /**
     * Enqueue styles for posts created with Setka Editor on non admin site area.
     *
     * @return $this For chain calls.
     */
    public function enqueueRegularAssets()
    {
        $this->enqueueThemePlugins();
        wp_enqueue_style(Plugin::NAME . '-theme-resources');

        return $this;
    }

    private function enqueueThemePlugins()
    {
        wp_enqueue_script(Plugin::NAME . '-theme-plugins');
    }

    /**
     * @return $this
     */
    public function enqueueStandaloneAssets()
    {
        $this->enqueueThemePlugins();
        wp_enqueue_style(Plugin::NAME . '-standalone');

        if ($this->standaloneCriticalCSS) {
            wp_add_inline_style(Plugin::NAME . '-standalone', $this->standaloneCriticalCSS);
            foreach ($this->fonts as $name => $url) {
                wp_enqueue_style($name, $url, Plugin::NAME . '-standalone');
            }
        }

        foreach ($this->standalone as $name => $url) {
            wp_enqueue_style($name, $url, Plugin::NAME . '-standalone');
        }

        return $this;
    }

    public function headStyles()
    {
        if ($this->standaloneCriticalCSS) {
            return sprintf(
                '<style id="%s">%s</style>',
                Plugin::NAME . '-standalone-critical-css',
                $this->standaloneCriticalCSS
            );
        }
    }

    /**
     * @return $this
     */
    public function enableRegularAssets()
    {
        $this->regularAssets = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function enableStandaloneAssets()
    {
        $this->standaloneAssets = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function enableAMPAssets()
    {
        $this->ampAssets = true;
        return $this;
    }

    /**
     * @return $this
     */
    protected function enqueueFonts()
    {
        foreach ($this->fonts as $id => $url) {
            wp_enqueue_style($id, $url);
        }
        return $this;
    }

    /**
     * Modifies HTML script tag.
     *
     * @param string $tag    The `<script>` tag for the enqueued script.
     * @param string $handle The script's registered handle.
     *
     * @return string Modified $tag.
     */
    public function scriptLoaderTag($tag, $handle)
    {
        if (!in_array($handle, $this->asyncScripts, true)) {
            return $tag;
        }

        $tag = str_replace('<script ', '<script async ', $tag);

        return $tag;
    }

    /**
     * @param string $tag    The link tag for the enqueued style.
     * @param string $handle The style's registered handle.
     *
     * @return string
     */
    public function styleLoaderTag($tag, $handle)
    {
        if ($this->standaloneCriticalCSS && isset($this->standalone[$handle])) {
            $tag = sprintf(
                '<link rel="preload" id="%s" href="%s" media="all" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">',
                esc_attr($handle),
                esc_attr($this->standalone[$handle])
            );

            $tag .= PHP_EOL;
        }
        return $tag;
    }

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     *
     * @return $this For chain calls.
     */
    public function setSetkaEditorAccount(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        return $this;
    }

    /**
     * @param PageInterface $pluginSettingsPage
     */
    public function setPluginSettingsPage(PageInterface $pluginSettingsPage)
    {
        $this->pluginSettingsPage = $pluginSettingsPage;
    }

    /**
     * @param EditorGutenbergModule $editorGutenbergModule
     *
     * @return $this
     */
    public function setEditorGutenbergModule(EditorGutenbergModule $editorGutenbergModule)
    {
        $this->editorGutenbergModule = $editorGutenbergModule;
        return $this;
    }

    /**
     * @param EditorAdapterJsSettings $editorAdapterJsSettings
     *
     * @return $this
     */
    public function setEditorAdapterJsSettings(EditorAdapterJsSettings $editorAdapterJsSettings)
    {
        $this->editorAdapterJsSettings = $editorAdapterJsSettings;
        return $this;
    }

    /**
     * @param NoticesStack $noticesStack
     * @return $this
     */
    public function setNoticesStack(NoticesStack $noticesStack)
    {
        $this->noticesStack = $noticesStack;
        return $this;
    }

    /**
     * @param string $ampCSS
     * @return $this
     */
    public function setAmpCSS($ampCSS)
    {
        $this->ampCSS = $ampCSS;
        return $this;
    }

    /**
     * @param $fonts array
     * @return $this
     */
    public function setFonts($fonts)
    {
        $this->fonts = $fonts;
        return $this;
    }

    /**
     * @param array $assets
     * @return $this
     */
    public function setStandalone(array $assets)
    {
        $this->standalone = $assets;
        return $this;
    }

    /**
     * @param string $standaloneCriticalCSS
     */
    public function setStandaloneCriticalCSS($standaloneCriticalCSS)
    {
        $this->standaloneCriticalCSS = $standaloneCriticalCSS;
        return $this;
    }
}
