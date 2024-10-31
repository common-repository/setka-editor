<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractUseStylesOption;
use Setka\Editor\Plugin;

class UseStylesOption extends AbstractUseStylesOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_use_standalone_styles');
    }
}
