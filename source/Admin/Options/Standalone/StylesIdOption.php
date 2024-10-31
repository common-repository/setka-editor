<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractStylesIdOption;
use Setka\Editor\Plugin;

class StylesIdOption extends AbstractStylesIdOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_standalone_styles_id');
    }
}
