<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractServiceSwitchOption;
use Setka\Editor\Plugin;

class ServiceSwitchOption extends AbstractServiceSwitchOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_service_switch');
        $this->setDefaultValue(true);
    }
}
