<?php
namespace Setka\Editor\Compatibility;

class PHPVersionNotice
{
    /**
     * @var \array[][]
     */
    private static $allowedLink = array('a' => array('href' => array(), 'target' => array()));

    /**
     * @var string Plugin base url.
     */
    protected $baseUrl;

    /**
     * @var string Plugin version.
     */
    protected $pluginVersion;

    /**
     * @var string Min PHP version.
     */
    protected $phpVersionMin;

    public function run()
    {
        add_action('admin_init', array($this, 'init'));
    }

    public function init()
    {
        if (current_user_can('update_core') ||
            current_user_can('install_plugins') ||
            current_user_can('activate_plugins')
        ) {
            $this->addActions();
        }
    }

    public function addActions()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueAssets'), 11);
        add_action('admin_notices', array($this, 'renderNotice'));
    }

    /**
     * Enqueue scripts and styles.
     */
    public function enqueueAssets()
    {
        $file = 'assets/css/admin/main.min.css';
        wp_register_style(
            'setka-editor-wp-admin-main',
            $this->baseUrl . $file,
            array(),
            $this->pluginVersion
        );

        wp_enqueue_style('setka-editor-wp-admin-main');
    }

    /**
     * Render notice.
     */
    public function renderNotice()
    {
        ?>
        <div id="setka-editor-notice-php-min-version" class="notice setka-editor-notice notice-error setka-editor-notice-error">
            <p class="notice-title setka-editor-notice-title"><?php esc_html_e('Setka Editor plugin error', 'setka-editor'); ?></p>
            <p><?php
                echo wp_kses(sprintf(
                    __('Oh, no! Seems you have an old PHP version that is not compatible with Setka Editor plugin. Please update your PHP plugin by following <a href="%1$s" target="_blank">these easy instructions</a> and then try activating the plugin again.', 'setka-editor'),
                    'https://editor-help.setka.io/hc/en-us/articles/115000600189/#phpversionupdate'
                ), self::$allowedLink);
                ?></p>
            <p><?php
                echo wp_kses(sprintf(
                    /* translators: %1$s - current PHP version in X.Y.Z format. */
                    __('Your current PHP version: <b>%1$s</b>.', 'setka-editor'),
                    esc_html(phpversion())
                ), array('b' => array()));
                echo '<br>';
                echo wp_kses(sprintf(
                    /* translators: %1$s - required PHP version in X.Y.Z format. */
                    __('Minimal PHP version required for Setka Editor plugin: <b>%1$s</b>.', 'setka-editor'),
                    esc_html($this->phpVersionMin)
                ), array('b' => array()));
                echo '<br>';
                echo wp_kses(sprintf(
                    /* translators: %1$s - link to WordPress.org requirements page in native language. For example, for russian: https://ru.wordpress.org/about/requirements/ (please note ru. before wordpress.org). */
                    __('<a href="%1$s" target="_blank">WordPress highly recommends</a> using PHP %2$s or greater version.', 'setka-editor'),
                    esc_url(__('https://wordpress.org/about/requirements/', 'setka-editor')),
                    '7.3'
                ), self::$allowedLink);
                ?></p>
            <p><?php
                echo wp_kses(
                    __('Please contact Setka Editor team at <a href="mailto:support@tiny.cloud" target="_blank">support@tiny.cloud</a>.', 'setka-editor'),
                    self::$allowedLink
                );
                ?></p>
        </div>
        <?php
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     *
     * @return $this For chain calls.
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->pluginVersion;
    }

    /**
     * @param string $pluginVersion
     *
     * @return $this For chain calls.
     */
    public function setPluginVersion($pluginVersion)
    {
        $this->pluginVersion = $pluginVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhpVersionMin()
    {
        return $this->phpVersionMin;
    }

    /**
     * @param string $phpVersionMin
     *
     * @return $this For chain calls.
     */
    public function setPhpVersionMin($phpVersionMin)
    {
        $this->phpVersionMin = $phpVersionMin;
        return $this;
    }
}
