<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureNoticeOption;
use Setka\Editor\Plugin;

class SyncFailureNoticeOption extends AbstractSyncFailureNoticeOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_sync_failure_notice');
    }
}
