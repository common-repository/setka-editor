<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\Special\AbstractAggregatePostMeta;
use Symfony\Component\Validator\Constraints;

class ImageAttachmentMetadataPostMeta extends AbstractAggregatePostMeta
{
    const SIZES = 'sizes';

    const WIDTH = 'width';

    const HEIGHT = 'height';

    const FILE = 'file';

    public function __construct()
    {
        $this->setName('wp_attachment_metadata')
             ->setVisibility(false)
             ->setDefaultValue(array(
                 self::WIDTH => 0,
                 self::HEIGHT => 0,
                 self::FILE => null,
                 self::SIZES => array(),
                 'image_meta' => array(),
             ));
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return new Constraints\Collection(array(
            'fields' => array(
                self::WIDTH => array(
                    new Constraints\NotBlank(),
                    new Constraints\Type(array(
                        'type' => 'integer',
                    )),
                ),
                self::HEIGHT => array(
                    new Constraints\NotBlank(),
                    new Constraints\Type(array(
                        'type' => 'integer',
                    )),
                ),
                self::FILE => array(
                    new Constraints\NotBlank(),
                    new Constraints\Type(array(
                        'type' => 'string',
                    )),
                ),
                'sizes' => array(
                    new Constraints\All(array(
                        'constraints' => new Constraints\Collection(array(
                            'fields' => array(
                                self::WIDTH => array(
                                    new Constraints\NotBlank(),
                                    new Constraints\Type(array(
                                        'type' => 'integer',
                                    )),
                                ),
                                self::HEIGHT => array(
                                    new Constraints\NotBlank(),
                                    new Constraints\Type(array(
                                        'type' => 'integer',
                                    )),
                                ),
                            ),
                            'allowExtraFields' => true,
                        )),
                    )),
                ),
            ),
            'allowExtraFields' => true,
        ));
    }

    /**
     * @return array
     */
    public function getSizes()
    {
        $value = $this->get();
        return isset($value[self::SIZES]) ? $value[self::SIZES] : $this->defaultValue[self::SIZES];
    }

    /**
     * @return string|null
     */
    public function getFile()
    {
        $value = $this->get();
        return $value[self::FILE];
    }
}
