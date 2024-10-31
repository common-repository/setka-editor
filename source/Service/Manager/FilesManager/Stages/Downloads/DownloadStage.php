<?php
namespace Setka\Editor\Service\Manager\FilesManager\Stages\Downloads;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Admin\Service\FilesSync\DownloaderInterface;
use Setka\Editor\Admin\Service\FilesSync\Exceptions\FileDownloadException;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\Service\Manager\AbstractStage;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\Exceptions\PostMetaException;
use Setka\Editor\Service\Manager\FilesManager\File;
use Setka\Editor\Service\Manager\PostOperationsTrait;
use Setka\Editor\Service\Manager\Stacks\DraftFactoryInterface;
use Setka\Editor\Service\PostStatuses;

class DownloadStage extends AbstractStage
{
    use PostOperationsTrait;

    /**
     * @var DraftFactoryInterface
     */
    private $draftFactory;

    /**
     * @var OriginUrlPostMeta
     */
    private $originUrlPostMeta;

    /**
     * @var AttemptsToDownloadPostMeta
     */
    private $attemptsToDownloadPostMeta;

    /**
     * @var DownloaderInterface
     */
    private $downloader;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @var File
     */
    private $file;

    /**
     * AbstractDownloadStage constructor.
     *
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param DraftFactoryInterface $draftFactory
     * @param OriginUrlPostMeta $originUrlPostMeta
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param DownloaderInterface $downloader
     * @param FilesystemInterface $filesystem
     * @param array $handlers
     */
    public function __construct(
        callable $continueExecution,
        LoggerInterface $logger,
        DraftFactoryInterface $draftFactory,
        OriginUrlPostMeta $originUrlPostMeta,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        DownloaderInterface $downloader,
        FilesystemInterface $filesystem,
        array $handlers
    ) {
        parent::__construct($continueExecution, $logger);
        $this->draftFactory               = $draftFactory;
        $this->originUrlPostMeta          = $originUrlPostMeta;
        $this->attemptsToDownloadPostMeta = $attemptsToDownloadPostMeta;
        $this->downloader                 = $downloader;
        $this->filesystem                 = $filesystem;
        $this->handlers                   = $handlers;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        try {
            kses_remove_filters();
            foreach ($this->handlers as $handler) {
                $handler->setUp();
            }
            $this->loop();
        } finally {
            kses_init();
        }
    }

    /**
     * @throws OutOfTimeException
     * @throws PostException
     * @throws PostMetaException
     * @throws \Setka\Editor\Exceptions\RuntimeException
     * @throws \Exception
     */
    private function loop()
    {
        do {
            $query = $this->draftFactory->createQuery();

            while ($query->have_posts()) {
                $post = $query->next_post();
                $this->loopStep($post);
            }

            $query->rewind_posts();
        } while ($query->have_posts());
    }

    /**
     * @param \WP_Post $post
     *
     * @throws OutOfTimeException
     * @throws PostException
     * @throws PostMetaException
     * @throws \Setka\Editor\Exceptions\RuntimeException
     * @throws \Exception
     */
    private function loopStep(\WP_Post $post): void
    {
        try {
            $this->continueExecution();

            $this->setupFile($post);
            $this->downloadFile();
            $this->handleFile();
            $this->markFileDownloadedAndSaved();
        } catch (FileDownloadException $exception) {
            $this->markPostPending($exception);
        } finally {
            $this->deleteTemporaryFile();
        }
    }

    private function setupFile(\WP_Post $post): void
    {
        if (!$this->originUrlPostMeta->setPostId($post->ID)->isValid()) {
            throw new PostMetaException($post, $this->originUrlPostMeta);
        }

        $this->attemptsToDownloadPostMeta->setPostId($post->ID)->deleteLocal();

        $this->file = new File($post, $this->originUrlPostMeta->get());
    }

    /**
     * @throws FileDownloadException
     */
    private function downloadFile(): void
    {
        $originUrl = $this->file->getOriginUrl();

        $this->logger->debug(
            'Start downloading file.',
            array('id' => $this->file->getID(), 'url' => $originUrl)
        );

        $this->file->setCurrentLocation(
            $this->downloader->download($originUrl)->getResult()
        );

        $this->logger->debug(
            'File successful downloaded.',
            array('path' => $this->downloader->getResult())
        );
    }

    /**
     * @throws \Exception
     */
    private function handleFile(): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($this->file);
        }
    }

    /**
     * @throws PostException
     */
    private function markFileDownloadedAndSaved(): void
    {
        $this->attemptsToDownloadPostMeta->delete();

        $post = $this->file->getPost();

        $post->post_status = PostStatuses::PUBLISH;

        $this->updatePost($post);

        $this->logger->debug('File successful updated.');
    }

    /**
     * Mark post pending (means what post can not be downloaded).
     *
     * @param $exception \Exception Reason because of which post cannot be downloaded.
     *
     * @throws PostException If post was not updated.
     */
    private function markPostPending($exception): void
    {
        $this->logger->warning('Error while file downloading.', array($exception));

        $counter = (int) $this->attemptsToDownloadPostMeta->get();
        $counter++;
        $this->attemptsToDownloadPostMeta->updateValue($counter);

        $post = $this->file->getPost();

        $post->post_status = PostStatuses::PENDING;

        $this->updatePost($post);
    }

    /**
     * @throws \Setka\Editor\Exceptions\RuntimeException
     */
    private function deleteTemporaryFile(): void
    {
        $path = $this->downloader->getResult();

        if (isset($path) && is_string($path) && $this->filesystem->exists($path)) {
            $this->filesystem->unlink($path);
        }
    }
}
