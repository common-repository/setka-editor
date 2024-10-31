<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractSyncOption;
use Setka\Editor\Plugin;

class SyncOption extends AbstractSyncOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_sync');
    }
}
