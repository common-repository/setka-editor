<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractStylesIdOption;
use Setka\Editor\Plugin;

class AMPStylesIdOption extends AbstractStylesIdOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_amp_styles_id');
    }
}
