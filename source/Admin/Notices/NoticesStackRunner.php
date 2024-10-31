<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NoticesStackRunner implements RunnerInterface
{
    /**
     * @var ContainerInterface Container with services.
     */
    private static $container;

    /**
     * Returns the ContainerBuilder with services.
     *
     * @return ?ContainerInterface Container with services.
     */
    public static function getContainer(): ?ContainerInterface
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
        /**
         * @var $stack \Korobochkin\WPKit\Notices\NoticesStack
         */
        $stack = self::$container->get('wp.plugins.setka_editor.notices_stack');
        $stack->run();
    }
}
