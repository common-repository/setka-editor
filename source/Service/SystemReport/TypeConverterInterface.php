<?php
namespace Setka\Editor\Service\SystemReport;

interface TypeConverterInterface
{
    /**
     * @param mixed $value
     *
     * @return string
     */
    public function convertToString($value): string;
}
