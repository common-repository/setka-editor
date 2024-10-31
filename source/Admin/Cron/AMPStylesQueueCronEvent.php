<?php
namespace Setka\Editor\Admin\Cron;

use Setka\Editor\Plugin;

class AMPStylesQueueCronEvent extends AbstractStylesQueueCronEvent
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_cron_amp_styles_queue');
    }
}
