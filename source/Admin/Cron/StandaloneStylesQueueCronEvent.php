<?php
namespace Setka\Editor\Admin\Cron;

use Setka\Editor\Plugin;

class StandaloneStylesQueueCronEvent extends AbstractStylesQueueCronEvent
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_cron_standalone_styles_queue');
    }
}
