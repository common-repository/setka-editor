<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Plugin;

class SyncStageOption extends AbstractSyncStageOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_sync_stage');
    }
}
