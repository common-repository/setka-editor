<?php
namespace Setka\Editor\Admin\Pages\Settings;

use Korobochkin\WPKit\DataComponents\NodeInterface;
use Setka\Editor\Admin\Options\EditorAccessPostTypesOption;
use Setka\Editor\Admin\Options\ForceUseSetkaCDNOption;
use Setka\Editor\Admin\Options\SrcsetSizesOption;
use Setka\Editor\Admin\Options\WebhooksEndpointOption;
use Setka\Editor\Admin\Options\WhiteLabelOption;
use Setka\Editor\Admin\User\Capabilities\UseEditorCapability;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;

class SettingsBuilder
{
    /**
     * @var Settings
     */
    private $settings;

    /**
     * @var array
     */
    private $dataComponentsMap = array(
        WebhooksEndpointOption::class => 'setEndpoint',
        ForceUseSetkaCDNOption::class => 'setForceUseSetkaCDN',
        WhiteLabelOption::class => 'setWhiteLabel',
    );

    /**
     * @var NodeInterface[]
     */
    private $dataComponents;

    /**
     * @var StandaloneServiceManager
     */
    private $standaloneServiceManager;

    /**
     * SettingsBuilder constructor.
     *
     * @param SrcsetSizesOption $srcsetSizesOption
     * @param EditorAccessPostTypesOption $editorAccessPostTypesOption
     * @param WebhooksEndpointOption $webhooksEndpoint
     * @param ForceUseSetkaCDNOption $forceUseSetkaCDNOption
     * @param WhiteLabelOption $whiteLabelOption
     * @param StandaloneServiceManager $standaloneServiceManager
     */
    public function __construct(
        SrcsetSizesOption $srcsetSizesOption,
        EditorAccessPostTypesOption $editorAccessPostTypesOption,
        WebhooksEndpointOption $webhooksEndpoint,
        ForceUseSetkaCDNOption $forceUseSetkaCDNOption,
        WhiteLabelOption $whiteLabelOption,
        StandaloneServiceManager $standaloneServiceManager
    ) {
        $this->dataComponents = array(
            SrcsetSizesOption::class              => $srcsetSizesOption,
            EditorAccessPostTypesOption::class    => $editorAccessPostTypesOption,
            WebhooksEndpointOption::class         => $webhooksEndpoint,
            ForceUseSetkaCDNOption::class         => $forceUseSetkaCDNOption,
            WhiteLabelOption::class               => $whiteLabelOption,
        );

        $this->standaloneServiceManager = $standaloneServiceManager;
    }

    /**
     * @return Settings
     */
    public function build()
    {
        $this->settings = new Settings();
        $this->buildPostTypes();
        $this->buildRoles();
        $this->buildSrcsetSizes();
        $this->buildStandaloneMode();
        $this->buildDataComponents();
        return $this->settings;
    }

    /**
     * @return $this
     */
    private function buildPostTypes()
    {
        $postTypes = $this->dataComponents[EditorAccessPostTypesOption::class]->get();
        $value     = array();

        foreach ($postTypes as $postType) {
            $value[] = new PostType($postType);
        }

        $this->settings->setPostTypes($value);

        return $this;
    }

    /**
     * @return $this
     */
    private function buildRoles()
    {
        $roles = $this->getRoles();

        if (empty($roles)) {
            return $this;
        }

        $rolesSelected = array();

        foreach ($roles as $index => $role) {
            if (isset($role['capabilities'][UseEditorCapability::NAME])
                &&
                true === $role['capabilities'][UseEditorCapability::NAME]
            ) {
                $rolesSelected[] = $index;
            }
        }

        $this->settings->setRoles($rolesSelected);

        return $this;
    }

    /**
     * @return array[]
     */
    private function getRoles()
    {
        return get_editable_roles();
    }

    /**
     * @return $this
     */
    private function buildSrcsetSizes()
    {
        $sizes = $this->dataComponents[SrcsetSizesOption::class]->get();
        $value = array();

        foreach ($sizes as $size) {
            $value[] = new ImageSize($size);
        }

        $this->settings->setSrcsetSizes($value);

        return $this;
    }

    private function buildStandaloneMode(): void
    {
        if ($this->standaloneServiceManager->isOnCritical()) {
            $mode = Settings::STYLES_MODE_STANDALONE_CRITICAL;
        } elseif ($this->standaloneServiceManager->isOnByOption()) {
            $mode = Settings::STYLES_MODE_STANDALONE;
        } else {
            $mode = Settings::STYLES_MODE_COMBINED;
        }

        $this->settings->setStylesMode($mode);
    }

    /**
     * @return $this
     */
    private function buildDataComponents()
    {
        foreach ($this->dataComponentsMap as $name => $setter) {
            call_user_func(array($this->settings, $setter), $this->dataComponents[$name]->get());
        }
        return $this;
    }
}
