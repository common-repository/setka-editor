<?php
namespace Setka\Editor\Service\Config;

use Setka\Editor\Admin\Service\ContinueExecution\CronLock;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Endpoints as SetkaEditorAPIEndpoints;

class PluginConfig
{

    public static function getUpgradeUrl()
    {
        return apply_filters('setka_editor_upgrade_url', 'https://editor.setka.io/app/billing_plans');
    }

    /**
     * Check for wp.com env.
     *
     * @return bool True if WordPress.com env, false otherwise.
     */
    public static function isVIP()
    {
        return defined('WPCOM_IS_VIP_ENV') && true === WPCOM_IS_VIP_ENV;
    }

    /**
     * @return bool
     */
    public static function isVIPGo()
    {
        return self::isVIP() &&
               (defined('VIP_GO_APP_ID') ||
                defined('VIP_GO_APP_NAME') ||
                defined('VIP_GO_APP_ENVIRONMENT') ||
                defined('VIP_GO_ENV'));
    }

    /**
     * @return bool True if sync files enabled.
     */
    public static function isSyncFiles()
    {
        return defined('SETKA_EDITOR_SYNC_FILES') ? SETKA_EDITOR_SYNC_FILES : true;
    }

    /**
     * @return bool True if AMP files sync enabled.
     */
    public static function isSyncAMPFiles()
    {
        return defined('SETKA_EDITOR_SYNC_AMP_FILES') ? SETKA_EDITOR_SYNC_AMP_FILES : true;
    }

    public static function isSyncStandaloneFiles()
    {
        return defined('SETKA_EDITOR_SYNC_STANDALONE_FILES') ? SETKA_EDITOR_SYNC_STANDALONE_FILES : true;
    }

    /**
     * @return bool
     */
    public static function serviceSwitchRegularFiles()
    {
        return self::isSyncFiles() && (self::isVIPGo() || !self::isVIP());
    }

    /**
     * @return bool
     */
    public static function serviceSwitchAMPFiles()
    {
        return self::isSyncAMPFiles();
    }

    /**
     * @return bool
     */
    public static function serviceSwitchStandaloneFiles()
    {
        return self::isSyncStandaloneFiles() && (self::isVIPGo() || !self::isVIP());
    }

    /**
     * @return bool
     */
    public static function isStandaloneSelfHostedFiles()
    {
        return self::serviceSwitchRegularFiles();
    }

    /**
     * @return bool True if cron process.
     */
    public static function isCron()
    {
        return defined('DOING_CRON') && true === DOING_CRON;
    }

    /**
     * @return bool True if WP_DEBUG mode enabled.
     */
    public static function isDebug()
    {
        return defined('WP_DEBUG') && true === WP_DEBUG;
    }

    /**
     * @return bool True if run from WP_CLI.
     */
    public static function isCli()
    {
        return defined('WP_CLI') && true === WP_CLI;
    }

    /**
     * @return bool
     */
    public static function isDoingAutosave()
    {
        return defined('DOING_AUTOSAVE') && true === DOING_AUTOSAVE;
    }

    /**
     * @return bool
     */
    public static function isDoingAJAX()
    {
        return defined('DOING_AJAX') && true === DOING_AJAX;
    }

    /**
     * Call this method directly in the code. It can't be stored in container as argument
     * since defined too late.
     * @return bool
     */
    public static function isRestRequest()
    {
        return defined('REST_REQUEST') && true === REST_REQUEST;
    }

    /**
     * @return bool True if logging should be enabled.
     */
    public static function isLog()
    {
        return !(defined('SETKA_EDITOR_PHP_UNIT') && true === SETKA_EDITOR_PHP_UNIT);
    }

    /**
     * Check for Gutenberg env.
     *
     * @return bool True if Gutenberg activated.
     */
    public static function isGutenberg()
    {
        if (function_exists('register_block_type')
            &&
            function_exists('do_blocks')
            &&
            function_exists('parse_blocks')
            &&
            function_exists('wp_set_script_translations')
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return array All post types which can used with Setka Editor.
     */
    public static function getAvailablePostTypes()
    {
        $types = get_post_types();

        $unusedTypes = array(
            'attachment',
            'revision',
            'nav_menu_item',
            'custom_css',
            'customize_changeset',
            'oembed_cache',
            'user_request',
            'amp_validated_url',
        );

        foreach ($unusedTypes as $type) {
            unset($types[$type]);
        }

        return $types;
    }

    /**
     * @return callable Could we continue run the code?
     */
    public static function getContinueExecution()
    {
        if (self::isCron()) {
            return array(CronLock::class, 'check');
        }
        return '__return_true';
    }

    /**
     * @return string|false
     */
    public static function getStoragePath()
    {
        try {
            $config = wp_upload_dir();
            if (isset($config['basedir'])) {
                return $config['basedir'];
            }
            throw new \RuntimeException('WordPress method wp_upload_dir return array without "basedir" parameter');
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return string|false
     */
    public static function getStorageUrl()
    {
        try {
            $config = wp_upload_dir();
            return isset($config['baseurl']) ? $config['baseurl'] : false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return int
     */
    public static function getFileDownloadAttempts()
    {
        if (defined('SETKA_EDITOR_FILES_DOWNLOADING_ATTEMPTS') && is_int(SETKA_EDITOR_FILES_DOWNLOADING_ATTEMPTS)) {
            return SETKA_EDITOR_FILES_DOWNLOADING_ATTEMPTS;
        }
        return 3;
    }

    /**
     * @return int
     */
    public static function getAMPMaxCSSSize()
    {
        return 50000;
    }

    /**
     * @return string
     */
    public static function getSetkaAPIEndpoint()
    {
        return (defined('SETKA_EDITOR_DEBUG') && true === SETKA_EDITOR_DEBUG) ? SetkaEditorAPIEndpoints::API_DEV : SetkaEditorAPIEndpoints::API;
    }

    /**
     * @return bool|string
     */
    public static function getSetkaAPIBasicAuthLogin()
    {
        return (defined('SETKA_EDITOR_API_BASIC_AUTH_USERNAME')) ? SETKA_EDITOR_API_BASIC_AUTH_USERNAME : false;
    }

    /**
     * @return bool|string
     */
    public static function getSetkaAPIBasicAuthPassword()
    {
        return (defined('SETKA_EDITOR_API_BASIC_AUTH_PASSWORD')) ? SETKA_EDITOR_API_BASIC_AUTH_PASSWORD : false;
    }

    /**
     * @return string
     */
    public static function getWPVersion()
    {
        return get_bloginfo('version');
    }
}
