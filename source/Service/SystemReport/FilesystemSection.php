<?php
namespace Setka\Editor\Service\SystemReport;

use Setka\Editor\Admin\Service\Filesystem\WordPressFilesystemFactory;

class FilesystemSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Filesystem';

    /**
     * @var array
     */
    protected $buildMethods = array('get_filesystem_method');

    public function build(): array
    {
        $data = array('get_filesystem_method' => get_filesystem_method());

        $create = 'WordPressFilesystemFactory::create()';

        try {
            $filesystem = WordPressFilesystemFactory::create();

            $data[$create] = array(
                'get_class' => get_class($filesystem),
                'method' => $filesystem->method,
                'errors' => is_wp_error($filesystem->errors) ? $filesystem->errors->get_error_message() : false,
                'options' => $filesystem->options,
            );
        } catch (\Exception $exception) {
            $data[$create] = 'WordPressFilesystemFactory::create() thrown exception';
        }

        return $data;
    }
}
