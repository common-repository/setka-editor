<?php
namespace Setka\Editor\Service\SystemReport;

interface SectionInterface
{
    /**
     * Builds value of section and store as class property.
     * @return mixed Value
     *
     * @throws \Exception
     */
    public function build();

    public function getTitle(): string;

    public function isBuildable(): bool;

    /**
     * @return \Exception[]
     */
    public function getErrors(): array;

    public function addError(\Exception $exception): void;
}
