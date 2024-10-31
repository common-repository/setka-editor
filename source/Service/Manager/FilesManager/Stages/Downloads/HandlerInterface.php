<?php
namespace Setka\Editor\Service\Manager\FilesManager\Stages\Downloads;

use Setka\Editor\Service\Manager\FilesManager\File;

interface HandlerInterface
{
    public function setUp(): void;

    public function handle(File $file): void;
}
