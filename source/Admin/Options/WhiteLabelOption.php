<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Editor\Plugin;

/**
 * Class WhiteLabelOption shows should or not we show credits after posts.
 *
 * @package Setka\Editor\Admin\Options\WhiteLabel
 */
class WhiteLabelOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_white_label')
            ->setDefaultValue(false);
    }
}
