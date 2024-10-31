<?php
namespace Setka\Editor\Service\Manager\FilesManager\Stages\Downloads;

use Setka\Editor\Service\Manager\FilesManager\File;

interface ConverterInterface
{
    /**
     * @param string $content
     * @param File $file
     *
     * @throws \Exception
     *
     * @return string
     */
    public function convert(string $content, File $file): string;
}
