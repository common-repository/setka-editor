<?php
namespace Setka\Editor\Service\Config;

/**
 * Class FileSystemCache
 */
class FileSystemCache
{
    /**
     * This function return path to cache folder or false.
     *
     * You can override this value by defining your own value in wp-config.php.
     *
     * We are playing a little bit dirty here. Since cache folder exists - there is no guarantee
     * that cache/twig (for example) folder is writable but this is not very common scenario
     * (and usually if this happens the developer know that is happening).
     *
     * Also we don't care about requiring WordPress `file.php` file with WP_Filesystem func.
     * Because we only load this on wp-admin pages.
     *
     * Read more in docs/plugin-configuration/README.md
     *
     * @param $pluginDirPath string Path to plugin.
     */
    public static function getDirPath($pluginDirPath)
    {
        if (function_exists('WP_Filesystem')) {
            WP_Filesystem();

            global $wp_filesystem;

            if (is_a($wp_filesystem, \WP_Filesystem_Direct::class)) {
                /**
                 * @var $wp_filesystem \WP_Filesystem_Direct
                 */
                if ($wp_filesystem->is_writable($pluginDirPath)) {
                    return $pluginDirPath . 'cache/';
                }
            }
        }

        return false;
    }
}
