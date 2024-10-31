<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\AbstractPostMeta;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * @deprecated
 */
class TypeKitIDPostMeta extends AbstractPostMeta
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_type_kit_id')
            ->setVisibility(false)
            ->setDefaultValue('');
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type(array(
                'type' => 'string',
            )),
        );
    }
}
