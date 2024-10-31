<?php
namespace Setka\Editor\Admin\Pages\Tabs;

use Korobochkin\WPKit\Pages\Tabs\Tab;
use Setka\Editor\Plugin;

class SupportTab extends Tab
{
    public function __construct()
    {
        $this->setName('support')
             ->setTitle(__('Support', Plugin::NAME))
             ->setUrl(add_query_arg(
                 'page',
                 Plugin::NAME . '-support',
                 admin_url('admin.php')
             ));
    }
}
