<?php
namespace Setka\Editor\Admin\Transients;

use Korobochkin\WPKit\Transients\AbstractTransient;

/**
 * @deprecated No longer used. This file is stored only for plugin uninstaller.
 */
class SettingsErrorsTransient extends AbstractTransient
{
    public function __construct()
    {
        $this
            ->setName('settings_errors')
            ->setExpiration(30);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
    }
}
