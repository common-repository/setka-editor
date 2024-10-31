<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractSyncAttemptsLimitFailureOption;
use Setka\Editor\Plugin;

class AMPSyncAttemptsLimitFailureOption extends AbstractSyncAttemptsLimitFailureOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_sync_attempts_limit_failure');
    }
}
