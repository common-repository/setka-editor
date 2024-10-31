<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\AbstractPostMeta;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class AttemptsToDownloadPostMeta
 */
class AttemptsToDownloadPostMeta extends AbstractPostMeta
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_attempts_to_download')
            ->setVisibility(false)
            ->setDefaultValue('0');
    }

    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type('numeric'),
        );
    }
}
