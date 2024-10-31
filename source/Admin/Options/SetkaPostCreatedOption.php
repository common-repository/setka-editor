<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Editor\Plugin;

/**
 * Class SetkaPostCreatedOption
 */
class SetkaPostCreatedOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_setka_post_created')
            ->setDefaultValue(false)
            ->setAutoload(false);
    }
}
