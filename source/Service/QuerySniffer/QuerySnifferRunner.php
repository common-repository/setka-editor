<?php
namespace Setka\Editor\Service\QuerySniffer;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuerySnifferRunner implements RunnerInterface
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
     * @param ?ContainerInterface $container Container with services.
     */
    public static function setContainer(ContainerInterface $container = null): void
    {
        self::$container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function run(): void
    {
        $querySniffer = self::$container->get(QuerySniffer::class);
        $querySniffer->scan();
    }
}
