<?php
namespace Setka\Editor\Admin\Cron;

use Setka\Editor\Plugin;

class StandaloneStylesCronEvent extends AbstractStylesCronEvent
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_cron_standalone_styles');
    }
}
