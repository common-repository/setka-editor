<?php
namespace Setka\Editor\Service\SystemReport;

class PrintTypeConverter extends EmptyTypeConverter implements TypeConverterInterface
{
    /**
     * @var array
     */
    public static $methods = array('print_r');

    public function convertToString($value): string
    {
        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
        return print_r($value, true);
        // phpcs:enable
    }
}
