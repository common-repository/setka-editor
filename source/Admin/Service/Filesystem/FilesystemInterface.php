<?php
namespace Setka\Editor\Admin\Service\Filesystem;

use Setka\Editor\Admin\Service\Filesystem\Exceptions\MoveException;
use Setka\Editor\Exceptions\RuntimeException;

interface FilesystemInterface
{
    /**
     * Get filesystem object.
     *
     * @return \WP_Filesystem_Base The result may be a class that extended from \WP_Filesystem_Base.
     */
    public function getFilesystem();

    /**
     * Set filesystem object.
     *
     * @param \WP_Filesystem_Base $filesystem \WP_Filesystem_Base instance or extended from it.
     *
     * @return $this For chain calls.
     */
    public function setFilesystem($filesystem);

    /**
     * Creates a folder in recursive manner.
     *
     * @param $basePath string Path which should exists.
     * @param $processingPath string Folders path which will be created.
     * @return $this For chain calls.
     *
     * @throws RuntimeException
     */
    public function createFoldersRecursive($basePath, $processingPath);

    /**
     * Read file content into string.
     *
     * @param $path string Path to file.
     *
     * @throws RuntimeException If read was failed.
     *
     * @return string If file successfully read.
     */
    public function getContents($path);

    /**
     * @param string $path
     * @param string $content
     * @throws RuntimeException
     */
    public function putContent($path, $content);

    /**
     * Delete file or folder.
     *
     * @param $path string Path to file which will be deleted.
     *
     * @throws RuntimeException If deleting was failed.
     *
     * @return $this For chain calls.
     */
    public function unlink($path);

    /**
     * Check if file or folder exist.
     *
     * @param $path string Path to file or folder.
     *
     * @throws RuntimeException If WordPress file system object return unexpected result.
     *
     * @return bool True or false in asking to question exists or not.
     */
    public function exists($path);

    /**
     * @param string $source Path to the source file.
     * @param string $destination Path to the destination file.
     * @param bool $overwrite Optional. Whether to overwrite the destination file if it exists.
     *
     * @return $this
     *
     * @throws MoveException
     * @throws RuntimeException
     */
    public function move($source, $destination, $overwrite = false);

    /**
     * @param string $path
     *
     * @return $this
     * @throws RuntimeException
     */
    public function mkdir($path);
}
