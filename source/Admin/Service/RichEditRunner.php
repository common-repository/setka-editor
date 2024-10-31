<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\DataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RichEditRunner implements RunnerInterface
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
     * @param boolean $richEdit
     * @return boolean
     */
    public static function userCanRichEdit($richEdit)
    {
        return self::$container->get(RichEdit::class)->userCanRichEdit($richEdit);
    }

    /**
     * @param DataFactory $dataFactory
     * @param Screen $screen
     *
     * @return RichEdit
     */
    public static function create(DataFactory $dataFactory, Screen $screen)
    {
        $postMeta = $dataFactory->create(UseEditorPostMeta::class);
        $post     = get_post();

        if (is_a($post, \WP_Post::class)) {
            $postMeta->setPostId($post->ID);
        }

        return new RichEdit($postMeta, $screen);
    }
}
