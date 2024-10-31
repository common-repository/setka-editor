<?php
namespace Setka\Editor\Admin\Pages\Uninstall;

use Korobochkin\WPKit\Pages\SubMenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Admin\Prototypes\Pages\PrepareTabsTrait;
use Setka\Editor\Plugin;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class UninstallPage extends SubMenuPage
{
    use PrepareTabsTrait;

    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var UseEditorPostMeta
     */
    private $useEditorPostMeta;

    public function __construct()
    {
        $this->setParentSlug(Plugin::NAME);
        $this->setPageTitle(_x('Uninstall', 'Uninstall page title', Plugin::NAME));
        $this->setMenuTitle($this->getPageTitle());
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME . '-uninstall');

        $this->setName('uninstall');

        $view = new TwigPageView();
        $view->setTemplate('admin/settings/uninstall/page.html.twig');
        $this->setView($view);
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct()
    {
        $this->prepareTabs();

        try {
            $uninstallCode = new UninstallCode($this->setkaEditorAccount);

            $code = $uninstallCode->build()->getCode();
        } catch (\Exception $exception) {
            $code = '';
        }

        $attributes = array(
            'page' => $this,
            'translations' => array(
                'keep_styles_title' => __('Keep post styles', Plugin::NAME),
                'keep_styles_text' => __('Keep Setka Editor post styles even after you delete Setka Editor plugin. Just add the following code into your WordPress theme <code>functions.php</code>.', Plugin::NAME),
                'keep_styles_caption' => __('Attention: Please follow these instructions only if you are going to delete the plugin.', Plugin::NAME),
            ),
            'meta_name' => $this->useEditorPostMeta->getName(),
            'code' => $code,
        );

        $this->enqueueCodeEditor();

        $this->getView()->setContext($attributes);

        return $this;
    }

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     */
    public function setSetkaEditorAccount(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
    }

    /**
     * @param UseEditorPostMeta $useEditorPostMeta
     */
    public function setUseEditorPostMeta(UseEditorPostMeta $useEditorPostMeta)
    {
        $this->useEditorPostMeta = $useEditorPostMeta;
    }

    /**
     * @return $this
     */
    public function enqueueCodeEditor()
    {
        if (function_exists('wp_enqueue_code_editor')) {
            wp_enqueue_code_editor(array(
                'type' => 'php',
            ));
        }
        return $this;
    }
}
