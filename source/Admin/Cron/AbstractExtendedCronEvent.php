<?php
namespace Setka\Editor\Admin\Cron;

use Korobochkin\WPKit\Cron\AbstractCronEvent;

class AbstractExtendedCronEvent extends AbstractCronEvent
{
    /**
     * Re-add event.
     *
     * @return $this For chain calls.
     */
    public function restart()
    {
        $this->unscheduleAll()->schedule();
        return $this;
    }
}
