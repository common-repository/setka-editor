<?php
namespace Setka\Editor\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Transients;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Uninstall implements RunnerInterface
{

    /**
     * @var ContainerInterface Container with services.
     */
    protected static $container;

    /**
     * Returns the ContainerBuilder with services.
     *
     * @return ContainerInterface Container with services.
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * Sets the ContainerBuilder with services.
     *
     * @param ContainerInterface $container Container with services.
     */
    public static function setContainer(ContainerInterface $container = null)
    {
        self::$container = $container;
    }

    /**
     * Main uninstaller function.
     *
     * @since 0.0.2
     */
    public static function run()
    {
        /**
         * Checklist:
         * 1. Remove plugin settings (many options in DB).
         * 2. Remove transients.
         * 3. Remove plugin specific capabilities from all User Roles.
         * 4. Remove scheduled cron tasks.
         */

        $uninstall = self::getContainer()->get('wp.plugins.setka_editor.uninstall');

        \Setka\Editor\Admin\Options\Common\Utilities::removeAllOptionsFromDb();

        \Setka\Editor\Admin\Transients\Common\Utilities::removeAllTransientsFromDb();

        \Setka\Editor\Admin\User\Capabilities\Common\Utilities::removeAllCapabilities();

        $uninstall->deleteCronEvents();
    }
}
