<?php
namespace Setka\Editor\Admin\Service;

class Freemius
{
    /**
     * @param $pluginPath string
     * @throws \Freemius_Exception
     */
    public static function run(string $pluginPath): void
    {
        global $setka_editor_freemius;

        if (!isset($setka_editor_freemius)) {
            require_once $pluginPath . 'source/libraries/freemius/wordpress-sdk/start.php';

            $setka_editor_freemius = fs_dynamic_init(array(
                'id'                  => '1245',
                'slug'                => 'setka-editor',
                'type'                => 'plugin',
                'public_key'          => 'pk_15103d9fe899fc27028a14a3d656f',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'slug'           => 'setka-editor',
                    'account'        => false,
                    'contact'        => false,
                    'support'        => false,
                ),
            ));
        }

        // Signal that SDK was initiated.
        do_action('setka_editor_freemius_loaded');
    }
}
