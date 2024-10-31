<?php
namespace Setka\Editor\Admin\Pages\Tabs;

use Korobochkin\WPKit\Pages\Tabs\Tab;
use Setka\Editor\Plugin;

/**
 * Class UninstallTab
 */
class UninstallTab extends Tab
{
    /**
     * StartTab constructor.
     */
    public function __construct()
    {
        $this->setName('uninstall');
        $this->setTitle(__('Uninstall', Plugin::NAME));

        $url = add_query_arg(
            'page',
            Plugin::NAME . '-uninstall',
            admin_url('admin.php')
        );

        $this->setUrl($url);
    }
}
