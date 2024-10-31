<?php
namespace Setka\Editor\Admin\Utils;

class CronUtils
{
    /**
     * Unschedules all events attached to the hook.
     *
     * Can be useful for plugins when deactivating to clean up the cron queue.
     *
     * Uses wp_unschedule_hook if it exists.
     *
     * @param string $hook Action hook, the execution of which will be unscheduled.
     */
    public static function wpUnscheduleHook($hook)
    {

        if (function_exists('wp_unschedule_hook')) {
            wp_unschedule_hook($hook);
            return;
        }

        $crons = _get_cron_array();

        foreach ($crons as $timestamp => $args) {
            unset($crons[$timestamp][$hook]);

            if (empty($crons[$timestamp])) {
                unset($crons[$timestamp]);
            }
        }

        _set_cron_array($crons);
    }
}
