<?php
namespace Setka\Editor\Admin\Service\Filesystem;

/**
 * Class FilesystemFactory
 */
class FilesystemFactory
{
    /**
     * @return bool|FilesystemInterface false if Filesystem can't be created.
     */
    public static function create()
    {
        try {
            $filesystem = WordPressFilesystemFactory::create();
        } catch (\Exception $exception) {
            return new NullFilesystem(); // WordPress file system not available.
        }

        $fs = new Filesystem();
        $fs->setFilesystem($filesystem);

        return $fs;
    }
}
