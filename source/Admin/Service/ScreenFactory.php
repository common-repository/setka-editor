<?php
namespace Setka\Editor\Admin\Service;

class ScreenFactory
{
    /**
     * @return ?\WP_Screen
     */
    public static function createWPScreen(): ?\WP_Screen
    {
        if (self::isHookSuffix()) {
            require_once(ABSPATH . 'wp-admin/includes/class-wp-screen.php');

            if (function_exists('get_current_screen')) {
                $screen = get_current_screen();

                if (is_a($screen, \WP_Screen::class)) {
                    return $screen;
                }
            }

            return \WP_Screen::get();
        }

        return null;
    }

    /**
     * @return bool
     */
    private static function isHookSuffix(): bool
    {
        return isset($GLOBALS['hook_suffix']);
    }
}
