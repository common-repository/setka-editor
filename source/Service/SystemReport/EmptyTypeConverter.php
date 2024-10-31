<?php
namespace Setka\Editor\Service\SystemReport;

class EmptyTypeConverter implements TypeConverterInterface
{
    /**
     * @var array
     */
    public static $methods = array();

    public function convertToString($value): string
    {
        return (string) $value;
    }
}
