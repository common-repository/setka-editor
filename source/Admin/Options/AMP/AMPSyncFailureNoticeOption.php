<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureNoticeOption;
use Setka\Editor\Plugin;

class AMPSyncFailureNoticeOption extends AbstractSyncFailureNoticeOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_sync_failure_notice');
    }
}
