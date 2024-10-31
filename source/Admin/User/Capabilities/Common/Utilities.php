<?php
namespace Setka\Editor\Admin\User\Capabilities\Common;

use Setka\Editor\Admin\User\Capabilities\UseEditorCapability;

class Utilities
{

    public static function getAllCapabilities()
    {
        $capabilities = array(
            UseEditorCapability::class,
        );

        return $capabilities;
    }

    /**
     * Try to remove our plugin specific capabilities from all User Roles.
     * Used in plugin Uninstaller class to freeing up the site from plugin data.
     */
    public static function removeAllCapabilities()
    {
        $roles        = get_editable_roles();
        $capabilities = self::getAllCapabilities();

        if (!empty($roles)) {
            foreach ($roles as $roleKey => $roleValue) {
                $role = get_role($roleKey);
                foreach ($capabilities as $cap) {
                    $role->remove_cap($cap::NAME);
                }
            }
        }
    }
}
