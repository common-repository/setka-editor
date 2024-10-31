<?php
namespace Setka\Editor\Service\Config;

class AMPConfig
{
    const MODE_NATIVE = 'native';

    const MODE_TRANSITIONAL = 'transitional';

    const MODE_READER = 'reader';

    /**
     * @var array
     */
    private static $modes = array(
        self::MODE_NATIVE,
        self::MODE_TRANSITIONAL,
        self::MODE_READER,
    );

    /**
     * Check for AMP plugin.
     *
     * @return bool True if AMP plugin enabled, false if disabled.
     */
    public static function isPluginActivate()
    {
        return function_exists('amp_init') &&
               function_exists('is_amp_endpoint') &&
               function_exists('amp_is_canonical');
    }

    /**
     * Return AMP plugin mode.
     *
     * @return ?string 'native', 'transitional' or 'reader' mode. null if AMP plugin not enabled.
     */
    public static function getMode(): ?string
    {
        if (!self::isPluginActivate()) {
            return null;
        }

        if (self::isCanonical()) {
            return self::MODE_NATIVE;
        }

        $mode = self::getModeFromSettings();

        if (!is_string($mode)) {
            return null;
        }

        if (in_array($mode, self::$modes, true)) {
            return $mode;
        }

        /**
         * 'disabled', 'paired' used before AMP 1.2.0.
         * 'disabled' also known as Classic mode in previous plugin versions.
         */

        if ('disabled' === $mode) {
            return self::MODE_READER;
        }

        if ('paired' === $mode) {
            return self::MODE_TRANSITIONAL;
        }

        return null;
    }

    /**
     * Check if AMP page requested.
     *
     * @return bool True if AMP page requested.
     */
    public static function isAMPEndpoint()
    {
        return self::isPluginActivate() && is_amp_endpoint();
    }

    /**
     * @return bool True if canonical.
     */
    private static function isCanonical()
    {
        return self::isPluginActivate() && amp_is_canonical();
    }

    /**
     * @return bool|string
     */
    private static function getModeFromSettings()
    {
        $options = self::getPluginOptions();

        if (isset($options['theme_support']) && is_string($options['theme_support'])) {
            return $options['theme_support'];
        }

        return false;
    }

    /**
     * @return mixed
     */
    private static function getPluginOptions()
    {
        return get_option('amp-options');
    }
}
