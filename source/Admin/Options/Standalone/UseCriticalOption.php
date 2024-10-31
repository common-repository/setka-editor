<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Editor\Plugin;

class UseCriticalOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setDefaultValue(false)
            ->setName(Plugin::_NAME_ . '_standalone_use_critical');
    }
}
