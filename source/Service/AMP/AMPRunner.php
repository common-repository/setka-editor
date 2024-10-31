<?php
namespace Setka\Editor\Service\AMP;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\Admin\Options\SrcsetSizesOption;
use Setka\Editor\PostMetas\ImageAttachmentMetadataPostMeta;
use Setka\Editor\Service\DataFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AMPRunner implements RunnerInterface
{
    /**
     * @var ContainerInterface Container with services.
     */
    protected static $container;

    /**
     * @var string
     */
    protected $mode;

    /**
     * AMPRunner constructor.
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        $this->mode = $mode;
    }

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
     * Setup additional sanitizers.
     *
     * @param array $sanitizers
     *
     * @return array Modified array sanitizers array.
     */
    public static function addSanitizers(array $sanitizers)
    {
        $beforeDefault = array(
            EmbedSanitizer::class     => array(),
            ImageSanitizer::class     => array(
                SrcsetSizesOption::class => self::getContainer()->get(SrcsetSizesOption::class),
                ImageAttachmentMetadataPostMeta::class =>
                    self::getContainer()->get(DataFactory::class)->create(ImageAttachmentMetadataPostMeta::class),
            ),
            AnimationSanitizer::class => array('setka_amp_service' => self::getContainer()->get(AMP::class)),
            FootnoteSanitizer::class  => array(),
            BackgroundSanitizer::class => array(),
        );

        $afterDefault = array(
            GallerySanitizer::class => array(),
        );

        return array_merge($beforeDefault, $sanitizers, $afterDefault);
    }

    /**
     * Modify config for AMP template.
     *
     * Classic mode.
     *
     * @param $data array Config for AMP template.
     * @param $post \WP_Post WordPress post object.
     *
     * @return array Modified data.
     */
    public static function classicTemplateData($data, $post)
    {
        if (!is_array($data) || !is_a($post, \WP_Post::class)) {
            return $data;
        }

        /**
         * @var $amp AMP
         */
        $amp = self::getContainer()->get(AMP::class);
        return $amp->classicTemplateData($data, $post);
    }

    /**
     * Output additional CSS for AMP page.
     *
     * @param $ampTemplate \AMP_Post_Template
     */
    public static function classicTemplateCss($ampTemplate)
    {
        if (!is_a($ampTemplate, '\AMP_Post_Template') || ! is_a($ampTemplate->post, \WP_Post::class)) {
            return;
        }
        /**
         * @var $amp AMP
         */
        $amp = self::getContainer()->get(AMP::class);
        echo wp_strip_all_tags($amp->classicTemplateCss($ampTemplate->post));
    }
}
