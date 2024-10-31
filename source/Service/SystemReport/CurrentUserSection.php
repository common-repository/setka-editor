<?php
namespace Setka\Editor\Service\SystemReport;

class CurrentUserSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Current WordPress user';

    /**
     * @var array
     */
    protected $buildMethods = array('wp_get_current_user');

    /**
     * @inheritDoc
     */
    public function build(): array
    {
        $user = wp_get_current_user();
        return array(
            'id' => $user->ID,
            'login' => $user->user_login,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
        );
    }
}
