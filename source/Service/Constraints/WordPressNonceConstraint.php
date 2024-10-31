<?php
namespace Setka\Editor\Service\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class WordPressNonceConstraint extends Constraint
{

    /**
     * @var string
     */
    public $message = 'The nonce is not valid.';

    /**
     * @var string
     */
    public $name = '_wpnonce';

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'name';
    }
}
