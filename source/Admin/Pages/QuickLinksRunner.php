<?php
namespace Setka\Editor\Admin\Pages;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\Service\Config\PluginConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuickLinksRunner implements RunnerInterface
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
        if (PluginConfig::isDoingAutosave()) {
            return;
        }

        if (self::$container->getParameter('wp.plugins.setka_editor.doing_ajax')) {
            return;
        }

        /**
         * @var $quickLinks QuickLinks
         */
        $quickLinks = self::getContainer()->get(QuickLinks::class);

        if ($quickLinks->isAllowed()) {
            $quickLinks->addFilters();
        }
    }
}
