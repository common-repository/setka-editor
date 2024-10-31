<?php
namespace Setka\Editor\Service\Manager\FilesManager\Stages\Downloads;

use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\Service\Manager\FilesManager\File;

class FileHandler implements HandlerInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string This directory should exists (usually wp-content/uploads).
     */
    private $destinationRoot;

    /**
     * @var string Extra path for Setka Editor files (will automatically created).
     */
    private $destinationPath;

    /**
     * @var string
     */
    private $destination;

    /**
     * FileHandler constructor.
     *
     * @param FilesystemInterface $filesystem
     * @param string $destinationRoot
     * @param string $destinationPath
     */
    public function __construct(FilesystemInterface $filesystem, string $destinationRoot, string $destinationPath)
    {
        $this->filesystem      = $filesystem;
        $this->destinationRoot = $destinationRoot;
        $this->destinationPath = $destinationPath;
    }

    /**
     * @throws \Setka\Editor\Exceptions\RuntimeException
     */
    public function setUp(): void
    {
        $this->filesystem->createFoldersRecursive($this->destinationRoot, $this->destinationPath);
        $this->destination = path_join($this->destinationRoot, $this->destinationPath);
    }

    /**
     * @param File $file
     *
     * @throws \Setka\Editor\Admin\Service\Filesystem\Exceptions\MoveException
     * @throws \Setka\Editor\Exceptions\RuntimeException
     */
    public function handle(File $file): void
    {
        $this->filesystem->createFoldersRecursive($this->destination, dirname($file->getSubPath()));

        $newPath = path_join($this->destination, $file->getSubPath());

        $this->filesystem->move($file->getCurrentLocation(), $newPath, true);
        $file->setCurrentLocation($newPath);
    }
}
