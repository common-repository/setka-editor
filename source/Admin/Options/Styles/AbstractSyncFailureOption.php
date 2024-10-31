<?php
namespace Setka\Editor\Admin\Options\Styles;

use Korobochkin\WPKit\Options\Special\BoolOption;

abstract class AbstractSyncFailureOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultValue(false);
    }
}
