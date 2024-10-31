<?php
namespace Setka\Editor\Admin\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class UpgraderRunner
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
     * @param mixed $upgrader
     * @param mixed $options
     */
    public static function afterUpgrade($upgrader, $options)
    {
    }
}
