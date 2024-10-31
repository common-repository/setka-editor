<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractServiceSwitchOption;
use Setka\Editor\Plugin;

class ServiceSwitchOption extends AbstractServiceSwitchOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_service_switch');
        $this->setDefaultValue(true);
    }
}
