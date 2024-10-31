<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class ThemeResourceJSLocalOption
 */
class ThemeResourceJSLocalOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_theme_resource_js_local')
            ->setDefaultValue('');
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type('string'),
        );
    }
}
