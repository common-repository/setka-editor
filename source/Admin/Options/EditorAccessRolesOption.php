<?php
namespace Setka\Editor\Admin\Options;

use Korobochkin\WPKit\Options\AbstractOption;
use Setka\Editor\Plugin;
use Symfony\Component\Validator\Constraints;

/**
 * Class EditorAccessRolesOption
 */
class EditorAccessRolesOption extends AbstractOption
{
    public function __construct()
    {
        $this
            ->setName(Plugin::_NAME_ . '_editor_access_roles')
            ->setDefaultValue(array());
    }

    /**
     * @inheritdoc
     */
    public function buildConstraint()
    {
        $roles = get_editable_roles();

        $roles = array_keys($roles);

        return array(
            new Constraints\NotNull(),
            new Constraints\Choice(array(
                'choices' => $roles,
                'multiple' => true,
                'strict' => true
            )),
        );
    }
}
