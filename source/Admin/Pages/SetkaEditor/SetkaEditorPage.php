<?php
namespace Setka\Editor\Admin\Pages\SetkaEditor;

use Korobochkin\WPKit\Pages\VirtualMenuPage;
use Setka\Editor\Plugin;
use Setka\Editor\Service\Config\PluginConfig;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class SetkaEditorPage extends VirtualMenuPage
{
    const SUBMENU_KEY_TITLE = 0;

    const SUBMENU_KEY_SLUG = 2;

    public function __construct($accountPage, $signUpPage, SetkaEditorAccount $setkaEditorAccount)
    {
        if ($setkaEditorAccount->isLoggedIn()) {
            $this->setVirtualPage($accountPage);
        } else {
            $this->setVirtualPage($signUpPage);
        }

        $this->setPageTitle($this->getVirtualPage()->getPageTitle());
        $title = _x('Setka Editor', 'Admin menu plugin section title.', Plugin::NAME);
        if (!$setkaEditorAccount->isLoggedIn()) {
            $title .= ' <span class="awaiting-mod count-1"><span class="plugin-count">1</span></span>';
        }
        $this->setMenuTitle($title);
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME);
        $this->setIcon('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjRweCIgaGVpZ2h0PSIyNXB4IiB2aWV3Qm94PSIwIDAgMjQgMjUiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CiAgICA8ZyBpZD0iR3JvdXAiIHN0cm9rZT0ibm9uZSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPgogICAgICAgIDxwYXRoIGQ9Ik00LjA5NjA0NDgyLDAgQzEuODk4NTY1NjIsMCAwLjExNzU3MiwxLjc5Mjc1NDcyIDAuMTE3NTcyLDQuMDA1NjMyMDggQzAuMTE3NTcyLDUuOTQzMDU3MTMgMS40ODQzODEwNSw3LjU1ODczNTM2IDMuMjk5ODkwMDUsNy45MzAyNDg3NyBMMy4yOTk4OTAwNSwyMC4wMjcwMDMgTDQuODkyMTk5NTksMjAuMDI3MDAzIEw0Ljg5MjE5OTU5LDcuOTMwMjQ4NzcgQzYuNzA3NzA4NTgsNy41NTg3MzUzNiA4LjA3NDUxNzY0LDUuOTQzMDU3MTMgOC4wNzQ1MTc2NCw0LjAwNTYzMjA4IEM4LjA3NDUxNzY0LDEuNzkyNzU0NzIgNi4yOTM1MjQwMiwwIDQuMDk2MDQ0ODIsMCIgaWQ9IkZpbGwtMTciIGZpbGw9IiM5QjlCOUIiPjwvcGF0aD4KICAgICAgICA8cGF0aCBkPSJNMjAuODA3NzAxNiwxNi4xMDE1NzYyIEwyMC44MDc3MDE2LDQuMDA0ODIxOTIgTDE5LjIxNjU0MjYsNC4wMDQ4MjE5MiBMMTkuMjE2NTQyNiwxNi4xMDE1NzYyIEMxNy4zOTk4ODMxLDE2LjQ3MzA4OTYgMTYuMDMzMDc0LDE4LjA4ODc2NzggMTYuMDMzMDc0LDIwLjAyNjE5MjkgQzE2LjAzMzA3NCwyMi4yMzkwNzAyIDE3LjgxNDA2NzYsMjQuMDMxODI0OSAyMC4wMTE1NDY4LDI0LjAzMTgyNDkgQzIyLjIwOTAyNiwyNC4wMzE4MjQ5IDIzLjk5MTE3MDEsMjIuMjM5MDcwMiAyMy45OTExNzAxLDIwLjAyNjE5MjkgQzIzLjk5MTE3MDEsMTguMDg4NzY3OCAyMi42MjQzNjExLDE2LjQ3MzA4OTYgMjAuODA3NzAxNiwxNi4xMDE1NzYyIiBpZD0iRmlsbC0xOCIgZmlsbD0iIzlCOUI5QiI+PC9wYXRoPgogICAgICAgIDxwYXRoIGQ9Ik0xMC43NzM3MzU0LDEzLjY1MjI0OTYgQzEwLjg3NDk4MDUsMTQuMjk2OTAwNiAxMS4xNjE0NTgyLDE0LjY1MjIxMDkgMTIuMDIwODkxMiwxNC42NTIyMTA5IEMxMi43NjI5NzE4LDE0LjY1MjIxMDkgMTMuMTAwMDcyLDE0LjM2NDAyNzcgMTMuMTAwMDcyLDEzLjkyMzA3MjUgQzEzLjEwMDA3MiwxMy40OTk0Nzc4IDEyLjY3ODk4NDQsMTMuMjk1NzgxOSAxMS40NjUxOTM1LDEzLjEyNjgwNyBDOS4xMDU0OTIwNCwxMi44MjEyNjMzIDguMTExNDQ5MSwxMi4xMjU2ODgzIDguMTExNDQ5MSwxMC4yNzg1Mzc2IEM4LjExMTQ0OTEsOC4zMTEwMjEwNyA5Ljg0NzU3MjcyLDcuMzYxOTgzNzEgMTEuODg2MjgxMiw3LjM2MTk4MzcxIEMxNC4wNjA3NTAxLDcuMzYxOTgzNzEgMTUuNTc3MTI1OCw4LjEwNzMyNTI1IDE1Ljc3OTYxNjEsMTAuMjI3NjEzNiBMMTMuMDQ5NDQ5NSwxMC4yMjc2MTM2IEMxMi45MzA5NDY3LDkuNjMzODg2NTcgMTIuNjExMTA0Miw5LjM0NTcwMzI3IDExLjkyMDc5NjYsOS4zNDU3MDMyNyBDMTEuMjYyNzAzMyw5LjM0NTcwMzI3IDEwLjkyNTYwMzEsOS42MzM4ODY1NyAxMC45MjU2MDMxLDEwLjA0MTI3ODIgQzEwLjkyNTYwMzEsMTAuNDY0ODcyOSAxMS4zMzA1ODM2LDEwLjYzMzg0NzkgMTIuNDA4NjEzOSwxMC43NzA0MTY3IEMxNC44NTM0NTM0LDExLjA3NDgwMyAxNi4wMzI3Mjg4LDExLjcxOTQ1NCAxNi4wMzI3Mjg4LDEzLjYxODY4NjEgQzE2LjAzMjcyODgsMTUuNzIwNDU2NyAxNC40ODE4Mzc4LDE2LjY3MDY1MTQgMTIuMDM4MTQ4OCwxNi42NzA2NTE0IEM5LjUyNjU3OTY4LDE2LjY3MDY1MTQgOC4wNjA4MjY1NCwxNS42MTg2MDg3IDcuOTkyOTQ2MjksMTMuNjUyMjQ5NiBMMTAuNzczNzM1NCwxMy42NTIyNDk2IFoiIGlkPSJGaWxsLTE5IiBmaWxsPSIjOUI5QjlCIj48L3BhdGg+CiAgICA8L2c+Cjwvc3ZnPg==');
        $this->setPosition(81);

        $this->setName(Plugin::NAME);

        $this->setView($this->getVirtualPage()->getView());

        add_action('admin_head', array($this, 'fixMenu'));
    }

    public function fixMenu(): void
    {
        global $submenu;
        $pageName = $this->getName();

        if (!isset($submenu[$pageName])) {
            return;
        }

        $setkaPages =& $submenu[$pageName];

        $remove = array(
            2 => $pageName . '-files',
            5 => $pageName . '-amp',
            7 => $pageName . '-wp-support-forum',
        );

        foreach ($remove as $index => $slug) {
            if (isset($setkaPages[$index][self::SUBMENU_KEY_SLUG]) &&
                $setkaPages[$index][self::SUBMENU_KEY_SLUG] === $slug) {
                unset($setkaPages[$index]);
            }
        }

        if (isset($setkaPages[0][self::SUBMENU_KEY_TITLE])) {
            $setkaPages[0][self::SUBMENU_KEY_TITLE] = $this->getVirtualPage()->getMenuTitle(); // WPCS: override ok.
        }

        if (isset($setkaPages[4][self::SUBMENU_KEY_SLUG]) && $setkaPages[4][self::SUBMENU_KEY_SLUG] === $pageName . '-upgrade') {
            $setkaPages[4][self::SUBMENU_KEY_SLUG] = PluginConfig::getUpgradeUrl(); // WPCS: override ok.
        }
    }

    public function enqueueScriptStyles(): void
    {
        wp_enqueue_script(Plugin::NAME . '-wp-admin-setting-pages-initializer');
        wp_enqueue_style(Plugin::NAME . '-wp-admin-main');
    }
}
