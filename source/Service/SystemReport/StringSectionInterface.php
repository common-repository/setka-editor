<?php
namespace Setka\Editor\Service\SystemReport;

interface StringSectionInterface extends SectionInterface
{
    public function build(): string;
}
