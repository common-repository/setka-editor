<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\Service\Manager\Stacks\StackItemFactoryInterface;

class RemoveOldEntriesOnDiskStage extends RemoveOldEntriesStage
{
    /**
     * @var FilesystemInterface
     */
    private $fileSystem;

    /**
     * @var FileSubPathPostMeta
     */
    private $fileSubPathPostMeta;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var boolean
     */
    private $deleteFiles = false;

    /**
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param StackItemFactoryInterface $queryFactory
     * @param FilesystemInterface $fileSystem
     * @param FileSubPathPostMeta $fileSubPathPostMeta
     * @param string $destination
     */
    public function __construct(
        $continueExecution,
        LoggerInterface $logger,
        StackItemFactoryInterface $queryFactory,
        FilesystemInterface $fileSystem,
        FileSubPathPostMeta $fileSubPathPostMeta,
        $destination
    ) {
        parent::__construct($continueExecution, $logger, $queryFactory);
        $this->fileSystem          = $fileSystem;
        $this->fileSubPathPostMeta = $fileSubPathPostMeta;
        $this->destination         = $destination;
    }

    /**
     * @inheritDoc
     * @throws \Setka\Editor\Exceptions\RuntimeException
     */
    protected function delete()
    {
        if ($this->deleteFiles) {
            $this->deleteFile();
        }
        parent::delete();
    }

    /**
     * @throws \Setka\Editor\Exceptions\RuntimeException
     */
    private function deleteFile()
    {
        $file = $this->fileSubPathPostMeta->setPostId($this->post->ID)->get();

        if ($file) {
            $file = path_join($this->destination, $file);

            if ($this->fileSystem->exists($file)) {
                $this->fileSystem->unlink($file);
            } else {
                $this->logger->error(
                    'File which attempted to delete not exists. This may occurs if file was deleted manually. Please consider that files should be deleted programmatically because they linked to DB records. DB record of this file will be deleted.',
                    array(
                        'file' => $file,
                        'post_type' => $this->post->post_type,
                        'post_name' => $this->post->post_name,
                    )
                );
            }
        }
    }

    /**
     * @param bool $deleteFiles
     * @return $this
     */
    public function setDeleteFiles($deleteFiles)
    {
        $this->deleteFiles = $deleteFiles;
        return $this;
    }
}
