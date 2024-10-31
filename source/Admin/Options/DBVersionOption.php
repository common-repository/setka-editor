<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\Special\NumericOption;
use Setka\Editor\Plugin;

/**
 * Class DBVersionOption
 */
class DBVersionOption extends NumericOption
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_db_version')
            ->setDefaultValue(0);
    }
}
