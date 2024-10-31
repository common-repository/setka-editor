<?php
namespace Setka\Editor\Admin\Options\Common;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Options;

class Utilities
{

    /**
     * @return OptionInterface[]
     */
    public static function getAllOptions()
    {
        $options = array(
            Options\AMP\AMPCssOption::class,
            Options\AMP\AMPFontsOption::class,
            Options\AMP\AMPStylesIdOption::class,
            Options\AMP\AMPStylesOption::class,
            Options\AMP\AMPSyncAttemptsLimitFailureOption::class,
            Options\AMP\AMPSyncFailureNoticeOption::class,
            Options\AMP\AMPSyncFailureOption::class,
            Options\AMP\AMPSyncLastFailureNameOption::class,
            Options\AMP\AMPSyncOption::class,
            Options\AMP\AMPSyncStageOption::class,
            Options\AMP\ServiceSwitchOption::class,
            Options\AMP\UseAMPStylesOption::class,
            Options\DBVersionOption::class,
            Options\EditorAccessPostTypesOption::class,
            Options\EditorAccessRolesOption::class,
            Options\EditorCSSOption::class,
            Options\EditorJSOption::class,
            Options\EditorVersionOption::class,

            Options\Files\FileSyncFailureOption::class,
            Options\Files\FileSyncStageOption::class,
            Options\Files\FilesOption::class,
            Options\Files\ServiceSwitchOption::class,
            Options\Files\UseLocalFilesOption::class,
            Options\ForceUseSetkaCDNOption::class,

            Options\PlanFeatures\PlanFeaturesOption::class,
            Options\PublicTokenOption::class,
            Options\SetkaPostCreatedOption::class,
            Options\SrcsetSizesOption::class,

            Options\Standalone\ServiceSwitchOption::class,
            Options\Standalone\StylesIdOption::class,
            Options\Standalone\StylesOption::class,
            Options\Standalone\SyncAttemptsLimitFailureOption::class,
            Options\Standalone\SyncFailureNoticeOption::class,
            Options\Standalone\SyncFailureOption::class,
            Options\Standalone\SyncLastFailureNameOption::class,
            Options\Standalone\SyncOption::class,
            Options\Standalone\SyncStageOption::class,
            Options\Standalone\UseCriticalOption::class,
            Options\Standalone\UseStylesOption::class,

            Options\SubscriptionActiveUntilOption::class,
            Options\SubscriptionPaymentStatusOption::class,
            Options\SubscriptionStatusOption::class,
            Options\ThemePluginsJSOption::class,
            Options\ThemeResourceCSSLocalOption::class,
            Options\ThemeResourceCSSOption::class,
            Options\ThemeResourceJSLocalOption::class,
            Options\ThemeResourceJSOption::class,
            Options\TokenOption::class,
            Options\WebhooksEndpointOption::class,
            Options\WhiteLabelOption::class
        );

        return $options;
    }

    /**
     * Check if any of our options presented in DB.
     * This is a helper method for plugin Activation.
     *
     * @see \Setka\Editor\Service\Activation::isActivatedFirstTime()
     *
     * @return bool true if any of options founded in DB, false if no options saved in DB.
     */
    public static function isOptionsExistsInDb()
    {
        $options = self::getAllOptions();

        foreach ($options as $option) {
            /**
             * @var $option OptionInterface
             */
            if (class_exists($option)) {
                $option = new $option();
                $value  = $option->getValueFromWordPress();
                if (false !== $value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Removes all options from DB. This is a helper method for plugin Uninstaller.
     *
     * @see \Setka\Editor\Service\Uninstall::run()
     */
    public static function removeAllOptionsFromDb()
    {
        $options = self::getAllOptions();

        foreach ($options as $option) {
            try {
                $option = new $option();
                if (is_a($option, OptionInterface::class)) {
                    /**
                     * @var $option OptionInterface
                     */
                    $option->delete();
                }
            } catch (\Exception $exception) {
                // Do nothing.
            }
        }
    }
}
