<?php
namespace Setka\Editor\Admin\Pages\Tabs;

use Korobochkin\WPKit\Pages\Tabs\Tab;
use Setka\Editor\Plugin;

/**
 * Class StartTab
 */
class StartTab extends Tab
{
    /**
     * StartTab constructor.
     */
    public function __construct()
    {
        $this->setName('sign-up');
        $this->setTitle(__('Log in', Plugin::NAME));

        $url = add_query_arg(
            'page',
            Plugin::NAME,
            admin_url('admin.php')
        );

        $this->setUrl($url);
    }
}
