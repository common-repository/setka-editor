<?php
namespace Setka\Editor\Admin\Service\Filesystem;

use Setka\Editor\Exceptions\RuntimeException;

class NullFilesystem implements FilesystemInterface
{
    /**
     * @inheritdoc
     *
     * @throws RuntimeException This method is not available in this FS.
     */
    public function getFilesystem()
    {
        throw new RuntimeException();
    }

    /**
     * @inheritdoc
     */
    public function setFilesystem($filesystem)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createFoldersRecursive($basePath, $processingPath)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }

    /**
     * @inheritdoc
     */
    public function getContents($path)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }

    /**
     * @inheritdoc
     */
    public function putContent($path, $content)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }

    /**
     * @inheritdoc
     */
    public function unlink($path)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }

    /**
     * @inheritdoc
     */
    public function exists($path)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }

    /**
     * @inheritDoc
     */
    public function move($source, $destination, $overwrite = false)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }

    /**
     * @inheritDoc
     */
    public function mkdir($path)
    {
        throw new RuntimeException(sprintf('Current file system doesn\'t support this method. File system name: %1$s', get_class($this)));
    }
}
