<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureOption;
use Setka\Editor\Plugin;

class AMPSyncFailureOption extends AbstractSyncFailureOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_sync_failure');
    }
}
