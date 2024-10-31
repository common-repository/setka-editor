<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\Plugin;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminScriptStylesRunner
 */
class AdminScriptStylesRunner implements RunnerInterface
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
        self::getContainer()->get(AdminScriptStyles::class)->register();
    }

    /**
     * Enqueue for all pages.
     *
     * @see Plugin::runAdmin()
     */
    public static function enqueue()
    {
        self::getContainer()->get(AdminScriptStyles::class)->enqueue();
    }
}
