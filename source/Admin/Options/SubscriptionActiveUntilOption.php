<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class SubscriptionActiveUntilOption
 */
class SubscriptionActiveUntilOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_subscription_active_until')
            ->setDefaultValue('');
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\DateTime(array(
                'format' => \DateTime::ISO8601
            ))
            // Example: '2016-08-25T18:05:35+03:00'
        );
    }
}
