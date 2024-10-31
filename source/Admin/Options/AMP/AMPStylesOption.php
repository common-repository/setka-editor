<?php
namespace Setka\Editor\Admin\Options\AMP;

use Setka\Editor\Admin\Options\Styles\AbstractStylesAggregateOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

class AMPStylesOption extends AbstractStylesAggregateOption
{
    public function __construct()
    {
        $this->setName(Plugin::_NAME_ . '_amp_styles')
             ->setAutoload(true)
             ->setDefaultValue(array(
                 self::COMMON => array(),
                 self::THEMES => array(),
                 self::LAYOUTS => array(),
             ));
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return new Constraints\Collection(array(
            'fields' => array(
                self::COMMON => $this->buildConstraintForSection('buildFieldsForFile'),
                self::THEMES => $this->buildConstraintForSection('buildFieldsForThemeFile'),
                self::LAYOUTS => $this->buildConstraintForSection('buildFieldsForFile'),
            ),
            'allowExtraFields' => true,
        ));
    }
}
