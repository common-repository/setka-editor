<?php
namespace Setka\Editor\Admin\Options\PlanFeatures;

use Korobochkin\WPKit\Options\Special\AbstractAggregateOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class PlanFeaturesOption
 */
class PlanFeaturesOption extends AbstractAggregateOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_plan_features')
            ->setDefaultValue(array(
                'white_label' => false,
                'white_label_html' => '',
            ));
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return new Constraints\Collection(array(
            'fields' => array(
                'white_label' => array(
                    new Constraints\Type(array(
                        'type' => 'bool',
                    )),
                    new Constraints\NotNull(),
                ),
                'white_label_html' => array(
                    new Constraints\Type(array(
                        'type' => 'string',
                    )),
                    new Constraints\NotNull(),
                ),
            ),
        ));
    }
}
