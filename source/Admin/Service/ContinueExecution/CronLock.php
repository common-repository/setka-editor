<?php
namespace Setka\Editor\Admin\Service\ContinueExecution;

class CronLock
{

    /**
     * Check if this cron process is still actual.
     *
     * Be sure to call this method only when wp-cron.php accessed. If cron task running
     * via terminal command the function _get_cron_lock not defined.
     *
     * See wp-cron.php:120 for more details.
     *
     * @throws OutOfTimeException If time is out.
     *
     * @return bool True if current php process (cron) is still actual.
     */
    public static function check()
    {
        global $doing_wp_cron;

        /**
         * If you are running cron task from WP CLI via
         * wp cron event run COMMAND_NAME
         * so the function _get_cron_lock not defined.
         */
        if (!function_exists('_get_cron_lock')) {
            return true;
        }

        if (_get_cron_lock() === $doing_wp_cron) {
            return true;
        }

        throw new OutOfTimeException();
    }
}
