<?php
namespace Setka\Editor\Admin\Pages\Tabs;

use Korobochkin\WPKit\Pages\Tabs\Tab;
use Setka\Editor\Plugin;

/**
 * Class AccountTab
 */
class AccountTab extends Tab
{
    /**
     * AccountTab constructor.
     */
    public function __construct()
    {
        $this->setName('account');
        $this->setTitle(__('Account', Plugin::NAME));

        $url = add_query_arg(
            'page',
            Plugin::NAME,
            admin_url('admin.php')
        );

        $this->setUrl($url);
    }
}
