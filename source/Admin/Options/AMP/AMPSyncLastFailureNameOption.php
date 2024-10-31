<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractLastFailureNameOption;
use Setka\Editor\Plugin;

class AMPSyncLastFailureNameOption extends AbstractLastFailureNameOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_sync_last_failure_name');
    }
}
