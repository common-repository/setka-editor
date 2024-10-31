<?php
namespace Setka\Editor\Admin\Options\Files;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * List of files as array which need to be crated in DB.
 */
class FilesOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_files')
            ->setDefaultValue(array())
            ->setAutoload(false);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\Type(array(
                'type' => 'array',
            )),
        );
    }
}
