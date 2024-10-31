<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Setka\Editor\Service\Config\PluginConfig;
use Symfony\Component\Validator\Constraints;

/**
 * Class EditorAccessPostTypesOption
 */
class EditorAccessPostTypesOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_editor_access_post_types')
            ->setDefaultValue(array('post', 'page'));
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        return array(
            new Constraints\NotNull(),
            new Constraints\Choice(array(
                'choices' => array_values(PluginConfig::getAvailablePostTypes()),
                'multiple' => true,
                'strict' => true,
            )),
        );
    }
}
