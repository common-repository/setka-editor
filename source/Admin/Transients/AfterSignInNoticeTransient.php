<?php
namespace Setka\Editor\Admin\Transients;

use Korobochkin\WPKit\Transients\Special\BoolTransient;
use Setka\Editor\Plugin;

/**
 * When this transient set to '1' — After Sign In notice shows up.
 *
 * @since 0.2.0
 */
class AfterSignInNoticeTransient extends BoolTransient
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_after_sign_in_notice')
            ->setExpiration(30)
            ->setDefaultValue(false);
    }
}
