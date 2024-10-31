<?php
namespace Setka\Editor\Admin\Options\Styles;

use Korobochkin\WPKit\Options\Special\NumericOption;

abstract class AbstractStylesIdOption extends NumericOption implements ConfigIdOptionInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setAutoload(true)
             ->setDefaultValue(0);
    }
}
