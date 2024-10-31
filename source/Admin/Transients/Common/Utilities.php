<?php
namespace Setka\Editor\Admin\Transients\Common;

use Korobochkin\WPKit\Transients\TransientInterface;
use Setka\Editor\Admin\Transients;

class Utilities
{

    /**
     * @return TransientInterface[]
     */
    public static function getAllTransients()
    {
        $transients = array(
            Transients\AfterSignInNoticeTransient::class,
            Transients\SettingsErrorsTransient::class,
            Transients\SettingsTokenTransient::class,
        );

        return $transients;
    }

    /**
     * Removes all transients from DB. This is a helper method for plugin Uninstaller.
     * Technically transients can be stored not in DB if your site using object cache.
     *
     * @see \Setka\Editor\Service\Uninstall::run()
     */
    public static function removeAllTransientsFromDb()
    {
        $transients = self::getAllTransients();

        try {
            foreach ($transients as $transient) {
                $transient = new $transient();
                if (is_a($transient, TransientInterface::class)) {
                    /**
                     * @var $transient TransientInterface
                     */
                    $transient->delete();
                }
            }
        } catch (\Exception $exception) {
            // Do nothing.
        }
    }
}
