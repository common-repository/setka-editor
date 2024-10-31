<?php
namespace Setka\Editor\Admin\Pages;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginsRunner implements RunnerInterface
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
    }

    /**
     * Adds plugin action links (along with Deactivate | Edit | Delete).
     *
     * @param $links array Default links setted up by WordPress.
     *
     * @return array Default links + our custom links.
     */
    public static function addActionLinks(array $links)
    {
        return self::getContainer()->get(Plugins::class)->addActionLinks($links);
    }
}
