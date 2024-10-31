<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractSyncOption;
use Setka\Editor\Plugin;

class AMPSyncOption extends AbstractSyncOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_sync');
    }
}
