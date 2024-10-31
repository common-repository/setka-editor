<?php
namespace Setka\Editor\Admin\Prototypes\Pages;

use Korobochkin\WPKit\Pages\PageInterface;
use Korobochkin\WPKit\Pages\Tabs\TabsInterface;

trait PrepareTabsTrait
{
    /**
     * This method find current tab and mark it as active.
     */
    public function prepareTabs()
    {
        /**
         * @var $this PageInterface
         * @var $tabs TabsInterface
         */
        $tabs = $this->getTabs();
        if ($tabs) {
            $tab = $tabs->getTab($this->getName());
            if ($tab) {
                $tab->markActive();
            }
        }
    }
}
