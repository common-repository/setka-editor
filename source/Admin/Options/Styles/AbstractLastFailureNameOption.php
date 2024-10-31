<?php
namespace Setka\Editor\Admin\Options\Styles;

use Korobochkin\WPKit\Options\AbstractOption;
use Symfony\Component\Validator\Constraints;

abstract class AbstractLastFailureNameOption extends AbstractOption
{
    public function __construct()
    {
        $this->setDefaultValue('');
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
