<?php
namespace Setka\Editor\Admin\Pages\Settings;

use Korobochkin\WPKit\Notices\NoticesStack;
use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\Pages\SubMenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Admin\Notices\SettingsSavedSuccessfullyNotice;
use Setka\Editor\Admin\Prototypes\Pages\PrepareTabsTrait;
use Setka\Editor\Plugin;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\User\Capabilities;
use Setka\Editor\Service\Config\ImageSizesConfig;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;

class SettingsPage extends SubMenuPage
{
    use PrepareTabsTrait;

    /**
     * @var NoticesStack
     */
    private $noticesStack;

    /**
     * @var OptionInterface[]
     */
    private $dataComponents = array();

    /**
     * @var StandaloneServiceManager
     */
    private $standaloneServiceManager;

    /**
     * @var boolean
     */
    private $vip = false;

    public function __construct()
    {
        $this->setParentSlug(Plugin::NAME);
        $this->setPageTitle(_x('Settings', 'Settings page title', Plugin::NAME));
        $this->setMenuTitle($this->getPageTitle());
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME . '-settings');

        $this->setName('settings');

        $view = new TwigPageView();
        $view->setTemplate('admin/settings/settings/page.html.twig');
        $this->setView($view);
    }

    public function lateConstruct()
    {
        $this->prepareTabs();

        $settingsBuilder = new SettingsBuilder(
            $this->dataComponents[Options\SrcsetSizesOption::class],
            $this->dataComponents[Options\EditorAccessPostTypesOption::class],
            $this->dataComponents[Options\WebhooksEndpointOption::class],
            $this->dataComponents[Options\ForceUseSetkaCDNOption::class],
            $this->dataComponents[Options\WhiteLabelOption::class],
            $this->standaloneServiceManager
        );

        $this->setForm($this->getFormFactory()->create(
            SettingsType::class,
            $settingsBuilder->build(),
            array(
                'srcset_sizes' => ImageSizesConfig::filter(ImageSizesConfig::getAllImageSizes()),
                'vip' => $this->vip
            )
        ));

        $this->handleRequest();

        $attributes = array(
            'page' => $this,
            'form' => $this->getForm()->createView(),
            'translations' => array(
                'post_types_description' => __('Enable Setka Editor for the following post types. You can also disable Setka Editor for all post types by unchecking all checkboxes below. In this case all posts created with Setka Editor continue working and displaying correctly but you will not be able to create a new post with Setka Editor.', Plugin::NAME),
                'roles_description' => __('Enable Setka Editor for the selected User Roles. You can also add or remove this permission by simply adding or removing %1$s capability to any User Role with <a href="https://wordpress.org/plugins/members/">Members</a> plugin.', Plugin::NAME),
                'roles_capability' => Capabilities\UseEditorCapability::NAME,
                'endpoint_description' => __('Please choose webhooks endpoint to receive and process updates of the styles created in the Style Manager on editor.setka.io. Note that the default endpoint suits in the majority of cases.', Plugin::NAME),
                'styles_mode' => array(
                    /**
                     * Sync this array with same value in SettingsType::buildForm
                     */
                    Settings::STYLES_MODE_STANDALONE => array(
                        __('Default. Only load styles needed for a specific Setka Editor post.', Plugin::NAME),
                        __('All Setka Editor styles will be loaded on the page as separate files. This provides a balance between user experience and speed.', Plugin::NAME),
                    ),
                    Settings::STYLES_MODE_STANDALONE_CRITICAL => array(
                        __('Inline critical post styles into a page and load the rest of the styles asynchronously.', Plugin::NAME),
                        __('This will reduce the delay to display post content, but might result in post content redraws after the full page load. Read more about <a href="https://editor-help.setka.io/hc/en-us/articles/360053978293" target="_blank">critical styles and how they affect performance</a>.', Plugin::NAME),
                    ),
                    Settings::STYLES_MODE_COMBINED => array(
                        __('Legacy. Load a combined CSS file with all your Setka Editor styles.', Plugin::NAME),
                        __('This will increase the assets\' size and might affect load speeds. Use only if you plan on displaying Setka Editor posts in different styles (configured on <a href="https://editor.setka.io" target="_blank">editor.setka.io</a>) on a single page.', Plugin::NAME),
                    ),
                ),
                'force_use_setka_cdn' => __('Always use files provided by Setka Editor’s CDN even if any local files are available.', Plugin::NAME),
                'white_label' => __('Show “Created with Setka Editor” credits below the content', Plugin::NAME),
                'srcset_sizes_description' => __('With the <code>srcset</code> attribute, Setka Editor allows for automatic image size adjustments that adhere to the size of a user’s device. When viewing content on a smaller screen, this feature can dramatically improve page load speeds. Select from the following theme- and plugin-generated image sizes to pass to  the <code>srcset</code> attribute.', Plugin::NAME),
                'srcset_sizes_description_2' => __('Once selected, this setting will be applied to all new posts.', Plugin::NAME),
                'sizes' => array(
                    'width' => _x('Width: %s', 'Settings page sizes', Plugin::NAME),
                    'height' => _x('Height: %s', 'Settings page sizes', Plugin::NAME),
                    'units_px' => _x('%spx', 'Settings page sizes', Plugin::NAME),
                    'units_unlimited' => _x('Unlimited', 'Settings page sizes', Plugin::NAME),
                ),
            ),
        );

        $this->getView()->setContext($attributes);
    }

    /**
     * @return $this
     */
    public function handleRequest()
    {
        $this->form->handleRequest($this->getRequest());

        if (!$this->form->isSubmitted() || !$this->form->isValid()) {
            return $this;
        }

        /**
         * @var $settings Settings
         */
        $settings = $this->form->getData();

        $this->dataComponents[Options\EditorAccessPostTypesOption::class]->updateValue($settings->getPostTypesAsArray());
        $this->dataComponents[Options\WebhooksEndpointOption::class]->updateValue($settings->getEndpoint());
        $this->dataComponents[Options\ForceUseSetkaCDNOption::class]->updateValue($settings->isForceUseSetkaCDN());
        $this->dataComponents[Options\WhiteLabelOption::class]->updateValue($settings->isWhiteLabel());

        if (!$this->vip) {
            $this->dataComponents[Options\SrcsetSizesOption::class]->updateValue($settings->getSrcsetSizesAsArray());
        }

        switch ($settings->getStylesMode()) {
            default:
            case Settings::STYLES_MODE_STANDALONE:
                $this->standaloneServiceManager->enableWithFlags(false);
                break;

            case Settings::STYLES_MODE_STANDALONE_CRITICAL:
                $this->standaloneServiceManager->enableWithFlags(true);
                break;

            case Settings::STYLES_MODE_COMBINED:
                $this->standaloneServiceManager->disable();
                $this->standaloneServiceManager->discardCurrentState();
                break;
        }

        $roles         = get_editable_roles();
        $selectedRoles = $settings->getRoles();

        if (is_array($roles)) {
            foreach ($roles as $roleKey => $roleValue) {
                $role = get_role($roleKey);
                if (array_search($roleKey, $selectedRoles, true) === false) {
                    $role->remove_cap(Capabilities\UseEditorCapability::NAME);
                } elseif (!$role->has_cap(Capabilities\UseEditorCapability::NAME)) {
                    $role->add_cap(Capabilities\UseEditorCapability::NAME);
                }
            }
        }
        unset($roles, $role, $roleKey, $roleValue);

        $this->noticesStack->addNotice(new SettingsSavedSuccessfullyNotice());

        return $this;
    }

    /**
     * @param NoticesStack $noticesStack
     *
     * @return $this
     */
    public function setNoticesStack(NoticesStack $noticesStack)
    {
        $this->noticesStack = $noticesStack;
        return $this;
    }

    /**
     * @param Options\SrcsetSizesOption $srcsetSizesOption
     * @param Options\EditorAccessPostTypesOption $editorAccessPostTypesOption
     * @param Options\WebhooksEndpointOption $webhooksEndpoint
     * @param Options\ForceUseSetkaCDNOption $filesGenericSyncOption
     * @param Options\WhiteLabelOption $whiteLabelOption
     */
    public function setDataComponents(
        Options\SrcsetSizesOption $srcsetSizesOption,
        Options\EditorAccessPostTypesOption $editorAccessPostTypesOption,
        Options\WebhooksEndpointOption $webhooksEndpoint,
        Options\ForceUseSetkaCDNOption $filesGenericSyncOption,
        Options\WhiteLabelOption $whiteLabelOption
    ) {
        $this->dataComponents = array(
            Options\SrcsetSizesOption::class              => $srcsetSizesOption,
            Options\EditorAccessPostTypesOption::class    => $editorAccessPostTypesOption,
            Options\WebhooksEndpointOption::class         => $webhooksEndpoint,
            Options\ForceUseSetkaCDNOption::class         => $filesGenericSyncOption,
            Options\WhiteLabelOption::class               => $whiteLabelOption,
        );
    }

    /**
     * @return bool
     */
    public function isVip()
    {
        return $this->vip;
    }

    /**
     * @param bool $vip
     * @return $this
     */
    public function setVip($vip)
    {
        $this->vip = $vip;
        return $this;
    }

    /**
     * @param StandaloneServiceManager $standaloneServiceManager
     */
    public function setStandaloneServiceManager(StandaloneServiceManager $standaloneServiceManager)
    {
        $this->standaloneServiceManager = $standaloneServiceManager;
    }
}
