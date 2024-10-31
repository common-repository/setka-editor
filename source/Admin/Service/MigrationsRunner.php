<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\Admin\Migrations\Configuration;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class MigrationsRunner
 */
class MigrationsRunner implements RunnerInterface
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
     * @inheritdoc
     */
    public static function run()
    {
        try {
            /**
             * @var $configuration Configuration
             */
            $configuration = self::getContainer()->get('wp.plugins.setka_editor.migrations');
            $configuration->migrateAsNecessary();
        } catch (\Exception $exception) {
            // Just catch
        }
    }
}
