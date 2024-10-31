<?php
namespace Setka\Editor\Service\Manager\FilesManager\Stages\Downloads;

use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\Service\Manager\Exceptions\ReadFileException;
use Setka\Editor\Service\Manager\FilesManager\File;

class DBHandler implements HandlerInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var ConverterInterface[]
     */
    private $converters;

    /**
     * @param FilesystemInterface $filesystem
     * @param ?ConverterInterface[] $converters
     */
    public function __construct(FilesystemInterface $filesystem, ?array $converters = null)
    {
        $this->filesystem = $filesystem;
        $this->converters = $converters;
    }

    public function setUp(): void
    {
    }

    /**
     * @param File $file
     * @throws ReadFileException
     * @throws \Exception
     */
    public function handle(File $file): void
    {
        try {
            $content = $this->filesystem->getContents($file->getCurrentLocation());
        } catch (\Exception $exception) {
            throw new ReadFileException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!empty($this->converters)) {
            foreach ($this->converters as $converter) {
                $content = $converter->convert($content, $file);
            }
        }

        $file->getPost()->post_content = $content;
    }
}
