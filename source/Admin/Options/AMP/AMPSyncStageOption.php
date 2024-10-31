<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Plugin;

class AMPSyncStageOption extends AbstractSyncStageOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_sync_stage');
    }
}
