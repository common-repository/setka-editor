<?php
namespace Setka\Editor\Admin\Service\Filesystem;

class WordPressFilesystemFactory
{

    /**
     * Returns WordPress filesystem object.
     *
     * @throws \Exception If WordPress can't create Filesystem instance.
     *
     * @return \WP_Filesystem_Base
     */
    public static function create()
    {
        require_once(ABSPATH . '/wp-admin/includes/file.php');

        $method = self::getMethod();

        $args = ('VIP' === $method && self::hasFilterForCredentials()) ? self::getArgsForVIP($method) : false;

        if (WP_Filesystem($args)) {
            global $wp_filesystem;
            return $wp_filesystem;
        }

        throw new \Exception();
    }

    /**
     * @return string
     */
    private static function getMethod()
    {
        return get_filesystem_method();
    }

    /**
     * @return bool
     */
    private static function hasFilterForCredentials()
    {
        return (bool) has_filter('request_filesystem_credentials');
    }

    /**
     * @param string $type
     *
     * @return array
     */
    private static function getArgsForVIP($type)
    {
        $args = apply_filters(
            'request_filesystem_credentials',
            '',
            '',
            $type,
            false,
            '',
            array(),
            false
        );

        return is_array($args) ? $args : array();
    }
}
