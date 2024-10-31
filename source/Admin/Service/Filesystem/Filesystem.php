<?php
namespace Setka\Editor\Admin\Service\Filesystem;

use Setka\Editor\Admin\Service\Filesystem\Exceptions\MoveException;
use Setka\Editor\Exceptions\RuntimeException;
use Setka\Editor\Service\PathsAndUrls;

class Filesystem implements FilesystemInterface
{

    /**
     * @var $filesystem \WP_Filesystem_Base
     */
    protected $filesystem;

    /**
     * @inheritdoc
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @inheritdoc
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function createFoldersRecursive($basePath, $processingPath)
    {
        if (!$this->filesystem->exists($basePath)) {
            throw new RuntimeException('Root folder not exist: ' . $basePath);
        }

        if ($this->isPathAbsolute($processingPath)) {
            throw new RuntimeException('Variable has invalid format');
        }

        $fragments = PathsAndUrls::splitUrlPathIntoFragments($processingPath);
        $path      = $basePath;

        foreach ($fragments as $fragment) {
            $path = path_join($path, $fragment);
            if (!$this->exists($path)) {
                $this->mkdir($path);
            }
        }
        return $this;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isPathAbsolute($path)
    {
        return path_is_absolute($path);
    }

    /**
     * @inheritdoc
     */
    public function getContents($path)
    {
        $result = $this->filesystem->get_contents($path);
        if (is_string($result)) {
            return $result;
        }
        throw new \RuntimeException(sprintf('File reading error. File path: %1$s', $path));
    }

    /**
     * @inheritDoc
     */
    public function putContent($path, $content)
    {
        $result = $this->filesystem->put_contents($path, $content);
        if (true === $result) {
            return $this;
        }
        throw new RuntimeException(sprintf('File writing error. File path: %1$s', $path));
    }

    /**
     * @inheritdoc
     */
    public function unlink($path)
    {
        $result = $this->filesystem->delete($path, true);
        if (true === $result) {
            return $this;
        }
        throw new \RuntimeException(sprintf('File or folder deleting error. Path: %1$s', $path));
    }

    /**
     * @inheritdoc
     */
    public function exists($path)
    {
        $result = $this->filesystem->exists($path);
        if (is_bool($result)) {
            return $result;
        }
        throw new \RuntimeException(sprintf('WordPress file system exists() method return unexpected result. Path: %1$s.', $path));
    }

    /**
     * @inheritDoc
     */
    public function move($source, $destination, $overwrite = false)
    {
        $result = $this->filesystem->move($source, $destination, $overwrite);
        if ($result) {
            return $this;
        }
        throw new MoveException($source, $destination);
    }

    /**
     * @inheritDoc
     */
    public function mkdir($path)
    {
        $result = $this->filesystem->mkdir($path);
        if ($result) {
            return $this;
        }
        throw new RuntimeException('Directory ' . $path . ' not created.');
    }
}
