<?php
namespace Setka\Editor\Admin\Pages;

use Korobochkin\WPKit\Pages\PageInterface;
use Korobochkin\WPKit\Pages\Tabs\TabsInterface;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginPagesFactory
{
    /**
     * Create page with tabs.
     *
     * @param $className string class name implemented PageInterface.
     * @param $container ContainerInterface
     *
     * @return PageInterface Page with tabs.
     */
    public static function create($className, $container)
    {
        /**
         * @var $page PageInterface
         * @var $tabs TabsInterface
         */
        $page = new $className();

        if ($container->get(SetkaEditorAccount::class)->isLoggedIn()) {
            $tabs = $container->get('wp.plugins.setka_editor.admin.account_tabs');
        } else {
            $tabs = $container->get('wp.plugins.setka_editor.admin.sign_up_tabs');
        }

        return $page->setTabs($tabs);
    }
}
