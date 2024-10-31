<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SavePostRunner
 */
class SavePostRunner implements RunnerInterface
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
     * Handle POST request.
     *
     * @param $postId int Post ID.
     * @param $post object WordPress Post object
     * @param $update bool Update or create new post.
     */
    public static function postAction($postId, $post, $update)
    {
        /**
         * @var $savePost SavePost
         */
        $savePost = self::getContainer()->get(SavePost::class);
        return $savePost->postAction($postId, $post, $update);
    }

    /**
     * Handles default post auto saves (triggering by WordPress Heartbeat API).
     *
     * @see SavePost::heartbeatReceived()
     *
     * @param $response array Which will be sent back to the client in browser.
     * @param $data array The data comes from JavaScript (Browser).
     *
     * @return array Just pass $response for the next filters as is.
     */
    public static function heartbeatReceived($response, $data)
    {
        /**
         * @var $savePost SavePost
         */
        $savePost = self::getContainer()->get(SavePost::class);
        return $savePost->heartbeatReceived($response, $data);
    }
}
