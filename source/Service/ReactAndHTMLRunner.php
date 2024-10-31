<?php
namespace Setka\Editor\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ReactAndHTMLRunner implements RunnerInterface
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
     * @param \WP_REST_Response $response The response object.
     * @param \WP_Post          $post     Post object.
     * @param \WP_REST_Request  $request  Request object.
     * @return \WP_REST_Response
     */
    public static function normalizeHTML($response, $post, $request)
    {
        if (is_a($response, '\WP_REST_Response') &&
            is_a($post, '\WP_Post') &&
            is_a($request, '\WP_REST_Request')) {
            return self::$container->get(ReactAndHTML::class)->run($response, $post, $request);
        }
        return $response;
    }
}
