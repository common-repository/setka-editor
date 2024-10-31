<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\AbstractPostMeta;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class FileSubPathPostMeta
 */
class FileSubPathPostMeta extends AbstractPostMeta
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_file_sub_path')
            ->setVisibility(false)
            ->setDefaultValue('');
    }

    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type('string'),
        );
    }
}
