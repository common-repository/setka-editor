<?php
namespace Setka\Editor\Compatibility;

class WPVersionNotice
{
    /**
     * @var string Plugin base url.
     */
    protected $baseUrl;

    /**
     * @var string Plugin version.
     */
    protected $pluginVersion;

    /**
     * @var string Min WordPress version.
     */
    protected $wpVersionMin;

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
        global $wp_version;
        ?>
        <div id="setka-editor-notice-wp-min-version" class="notice setka-editor-notice notice-error setka-editor-notice-error">
            <p class="notice-title setka-editor-notice-title"><?php esc_html_e('Setka Editor plugin error', 'setka-editor'); ?></p>
            <p><?php esc_html_e('Your WordPress version is obsolete. Please update your WordPress and then activate plugin again.', 'setka-editor'); ?></p>
            <p><?php
                echo wp_kses(sprintf(
                    /* translators: %1$s - current WordPress version in X.Y.Z format. */
                    __('Your current WordPress version: <b>%1$s</b>', 'setka-editor'),
                    esc_html($wp_version)
                ), array('b' => array()));
                echo '<br>';
                echo wp_kses(sprintf(
                    /* translators: %1$s - required WordPress version in X.Y.Z format. */
                    __('Minimal version for Setka Editor plugin: <b>%1$s</b>', 'setka-editor'),
                    esc_html($this->wpVersionMin)
                ), array('b' => array()));
                ?></p>
            <p><?php
                echo wp_kses(
                    __('Please contact Setka Editor team at <a href="mailto:support@tiny.cloud" target="_blank">support@tiny.cloud</a>.', 'setka-editor'),
                    array('a' => array('href' => array(), 'target' => array()))
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
    public function getWpVersionMin()
    {
        return $this->wpVersionMin;
    }

    /**
     * @param string $wpVersionMin
     *
     * @return $this For chain calls.
     */
    public function setWpVersionMin($wpVersionMin)
    {
        $this->wpVersionMin = $wpVersionMin;
        return $this;
    }
}
