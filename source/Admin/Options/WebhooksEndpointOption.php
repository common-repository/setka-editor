<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

class WebhooksEndpointOption extends AbstractOption
{
    const TYPE_POST = 'post';

    const TYPE_AJAX = 'ajax';

    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_webhooks_endpoint')
            ->setDefaultValue(self::TYPE_POST);
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotNull(),
            new Constraints\Choice(array(
                'choices' => $this->getChoices(),
                'multiple' => false,
                'strict' => true,
            )),
        );
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return array(self::TYPE_POST, self::TYPE_AJAX);
    }
}
