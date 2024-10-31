<?php
namespace Setka\Editor\Service;

use Setka\Editor\Plugin;

/**
 * Class CronSchedules
 */
class CronSchedules
{
    /**
     * @param $schedules array List of cron schedules.
     * @return array Updated list of cron schedules.
     */
    public function addSchedules($schedules)
    {
        $schedules[Plugin::_NAME_ . '_every_minute'] = array(
            'interval'  => MINUTE_IN_SECONDS,
            'display'   => __('Every minute (60 seconds)', Plugin::NAME)
        );

        return $schedules;
    }
}
