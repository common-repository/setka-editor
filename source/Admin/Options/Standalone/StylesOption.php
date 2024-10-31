<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Styles\AbstractStylesAggregateOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

class StylesOption extends AbstractStylesAggregateOption
{
    public function __construct()
    {
        $this->setName(Plugin::_NAME_ . '_standalone_styles')
             ->setAutoload(true)
             ->setDefaultValue(array(
                 self::COMMON => array(),
                 self::COMMON_CRITICAL => array(),
                 self::COMMON_DEFERRED => array(),
                 self::THEMES => array(),
                 self::THEMES_CRITICAL => array(),
                 self::THEMES_DEFERRED => array(),
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
                self::COMMON_CRITICAL => $this->buildConstraintForSection('buildFieldsForFile'),
                self::COMMON_DEFERRED => $this->buildConstraintForSection('buildFieldsForFile'),
                self::THEMES => $this->buildConstraintForSection('buildFieldsForThemeFile'),
                self::THEMES_CRITICAL => $this->buildConstraintForSection('buildFieldsForThemeFile'),
                self::THEMES_DEFERRED=> $this->buildConstraintForSection('buildFieldsForThemeFile'),
                self::LAYOUTS => $this->buildConstraintForSection('buildFieldsForFile'),
            ),
            'allowExtraFields' => true,
        ));
    }
}
