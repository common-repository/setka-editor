<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class SubscriptionPaymentStatusOption
 */
class SubscriptionPaymentStatusOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_subscription_payment_status')
            ->setDefaultValue('');
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Choice(array(
                'choices' => array('active', 'trialing', 'unpaid', 'canceled', 'past_due'),
                'strict' => true,
            )),
        );
    }
}
