<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractLastFailureNameOption;
use Setka\Editor\Plugin;

class SyncLastFailureNameOption extends AbstractLastFailureNameOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_sync_last_failure_name');
    }
}
