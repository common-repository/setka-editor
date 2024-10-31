<?php
namespace Setka\Editor\Admin\Pages\Upgrade;

use Korobochkin\WPKit\Pages\SubMenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Plugin;

class Upgrade extends SubMenuPage
{

    public function __construct()
    {
        $this->setParentSlug(Plugin::NAME);
        $this->setPageTitle(__('Upgrade plan', Plugin::NAME));
        $this->setMenuTitle($this->getPageTitle());
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME . '-upgrade');

        $this->setName('upgrade');

        $this->setView(new TwigPageView());
    }
}
