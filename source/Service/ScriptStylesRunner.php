<?php
namespace Setka\Editor\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\Plugin;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ScriptStylesRunner implements RunnerInterface
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
    }

    /**
     * Register main resources.
     */
    public static function register()
    {
        self::getContainer()->get(ScriptStyles::class)->register();
    }

    /**
     * Register theme resources.
     *
     * @see Plugin::run()
     */
    public static function registerThemeResources()
    {
        self::getContainer()->get(ScriptStyles::class)->registerThemeResources();
    }

    /**
     * Enqueue CSS and JS.
     *
     * @see Plugin::run()
     */
    public static function enqueue()
    {
        /**
         * @var $scriptStyles ScriptStyles
         */
        $scriptStyles = self::getContainer()->get(ScriptStyles::class);
        $scriptStyles->enqueue();
    }

    /**
     * Modifies HTML script tag.
     *
     * @param string $tag    The `<script>` tag for the enqueued script.
     * @param string $handle The script's registered handle.
     *
     * @return string Modified $tag.
     */
    public static function scriptLoaderTag($tag, $handle)
    {
        return self::getContainer()->get(ScriptStyles::class)->scriptLoaderTag($tag, $handle);
    }

    /**
     * @param string $tag    The link tag for the enqueued style.
     * @param string $handle The style's registered handle.
     *
     * @return string
     */
    public static function styleLoaderTag($tag, $handle)
    {
        return self::getContainer()->get(ScriptStyles::class)->styleLoaderTag($tag, $handle);
    }
}
