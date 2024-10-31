<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\AbstractPostMeta;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class PostLayoutPostMeta
 */
class PostLayoutPostMeta extends AbstractPostMeta
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_post_layout')
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
            new Constraints\Length(array(
                'min' => 2,
                'allowEmptyString' => false,
            )),
        );
    }
}
