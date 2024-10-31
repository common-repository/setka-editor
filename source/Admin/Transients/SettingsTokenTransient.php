<?php
namespace Setka\Editor\Admin\Transients;

use Korobochkin\WPKit\Transients\AbstractTransient;
use Setka\Editor\Plugin;

/**
 * @deprecated No longer used. This file is stored only for plugin uninstaller.
 */
class SettingsTokenTransient extends AbstractTransient
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_settings_token')
            ->setExpiration(30);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
    }
}
