<?php
namespace Setka\Editor\Service\SystemReport;

interface ArraySectionInterface extends SectionInterface
{
    public function build(): array;
}
