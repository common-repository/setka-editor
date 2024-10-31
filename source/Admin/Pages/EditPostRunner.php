<?php
namespace Setka\Editor\Admin\Pages;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EditPostRunner implements RunnerInterface
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
        add_action('admin_enqueue_scripts', array(self::class, 'adminEnqueueScripts'), 1100);
    }

    public static function adminEnqueueScripts()
    {
        /**
         * @var EditPost
         */
        $editPost = self::$container->get(EditPost::class);
        $editPost->setupOriginalPostFromGlobal()->enqueueScripts();
    }
}
