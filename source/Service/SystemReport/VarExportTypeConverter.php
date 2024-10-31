<?php
namespace Setka\Editor\Service\SystemReport;

class VarExportTypeConverter extends EmptyTypeConverter implements TypeConverterInterface
{
    /**
     * @var array
     */
    public static $methods = array('var_export');

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function convertToString($value): string
    {
        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
        return var_export($value, true);
        // phpcs:enable
    }
}
