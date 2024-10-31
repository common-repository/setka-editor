<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureOption;
use Setka\Editor\Plugin;

class SyncFailureOption extends AbstractSyncFailureOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_sync_failure');
    }
}
