<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Editor\Plugin;

class ForceUseSetkaCDNOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_force_use_setka_cdn')
             ->setDefaultValue(false);
    }
}
