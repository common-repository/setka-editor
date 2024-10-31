<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\AbstractPostMeta;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class SetkaFileIDPostMeta
 */
class SetkaFileIDPostMeta extends AbstractPostMeta
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_setka_file_id')
            ->setVisibility(false)
            ->setDefaultValue(null);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type('numeric'),
        );
    }
}
