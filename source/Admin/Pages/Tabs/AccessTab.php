<?php
namespace Setka\Editor\Admin\Pages\Tabs;

use Korobochkin\WPKit\Pages\Tabs\Tab;
use Setka\Editor\Plugin;

/**
 * Class AccessTab
 */
class AccessTab extends Tab
{
    /**
     * AccessTab constructor.
     */
    public function __construct()
    {
        $this->setName('settings');
        $this->setTitle(_x('Settings', 'Page tab title', Plugin::NAME));

        $url = add_query_arg(
            'page',
            Plugin::NAME . '-settings',
            admin_url('admin.php')
        );

        $this->setUrl($url);
    }
}
