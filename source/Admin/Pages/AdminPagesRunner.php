<?php
namespace Setka\Editor\Admin\Pages;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminPagesRunner implements RunnerInterface
{
    /**
     * @var ContainerInterface Container with services.
     */
    protected static $container;

    /**
     * @inheritdoc
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * @inheritdoc
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
        $pages = self::getContainer()->get(AdminPages::class);
        $pages->register();
    }
}
