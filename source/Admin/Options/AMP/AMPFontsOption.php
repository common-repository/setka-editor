<?php
namespace Setka\Editor\Admin\Options\AMP;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

class AMPFontsOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_amp_fonts')
            ->setDefaultValue(array());
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
