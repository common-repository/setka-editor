<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractSyncAttemptsLimitFailureOption;
use Setka\Editor\Plugin;

class SyncAttemptsLimitFailureOption extends AbstractSyncAttemptsLimitFailureOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_sync_attempts_limit_failure');
    }
}
