<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractUseStylesOption;
use Setka\Editor\Plugin;

class UseAMPStylesOption extends AbstractUseStylesOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_use_amp_styles');
    }
}
