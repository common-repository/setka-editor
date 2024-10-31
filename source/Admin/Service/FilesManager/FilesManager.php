<?php
namespace Setka\Editor\Admin\Service\FilesManager;

use Korobochkin\WPKit\Options\OptionInterface;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Cron\Files\SendFilesStatCronEvent;
use Setka\Editor\Admin\Options\Files\FilesOption;
use Setka\Editor\Admin\Options\Files\FileSyncFailureOption;
use Setka\Editor\Admin\Options\Files\ServiceSwitchOption;
use Setka\Editor\Admin\Options\Files\FileSyncStageOption;
use Setka\Editor\Admin\Options\Files\UseLocalFilesOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSOption;
use Setka\Editor\Admin\Options\ThemeResourceJSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceJSOption;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Admin\Service\EditorConfigGenerator\EditorConfigGeneratorFactory;
use Setka\Editor\Admin\Service\FilesCreator\FilesCreator;
use Setka\Editor\Admin\Service\FilesManager\Exceptions\DeletingAttemptsDownloadsMetaException;
use Setka\Editor\Admin\Service\FilesManager\Exceptions\FailureOptionException;
use Setka\Editor\Admin\Service\FilesManager\Exceptions\FlushingCacheException;
use Setka\Editor\Admin\Service\FilesManager\Exceptions\SyncDisabledByUserException;
use Setka\Editor\Admin\Service\FilesSync\DownloaderInterface;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\Admin\Service\WPQueryFactory;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\Service\Manager\Exceptions\AttemptsLimitException;
use Setka\Editor\Service\Manager\Exceptions\PendingFilesException;
use Setka\Editor\Service\Manager\FilesManager\FilesManagerInterface;
use Setka\Editor\Service\Manager\FilesManager\IsPendingExistsStage;
use Setka\Editor\Service\Manager\FilesManager\RemoveOldEntriesOnDiskStage;
use Setka\Editor\Service\Manager\FilesManager\SimplyFilesManager;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\DownloadStage;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\FileHandler;
use Setka\Editor\Service\Manager\Stacks\PendingFactory;
use Setka\Editor\Service\Manager\Stacks\AnyFactory;
use Setka\Editor\Service\Manager\Stacks\DraftFactory;
use Setka\Editor\Service\PathsAndUrls;
use Setka\Editor\Service\PostStatuses;
use Setka\Editor\Service\SetkaPostTypes;

class FilesManager extends SimplyFilesManager implements FilesManagerInterface
{
    /**
     * @var boolean True if sync enabled.
     */
    protected $sync;

    /**
     * @var FileSyncFailureOption
     */
    protected $fileSyncFailureOption;

    /**
     * @var ServiceSwitchOption
     */
    protected $fileSyncOption;

    /**
     * @var UseLocalFilesOption
     */
    protected $useLocalFilesOption;

    /**
     * @var DownloadListOfFiles
     */
    protected $downloadListOfFiles;

    /**
     * @var OriginUrlPostMeta
     */
    private $originUrlPostMeta;

    /**
     * @var FileSubPathPostMeta
     */
    private $fileSubPathPostMeta;

    /**
     * @var DownloaderInterface
     */
    private $downloader;

    /**
     * @var FilesystemInterface
     */
    private $fileSystem;

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
    private $destinationUrl;

    /**
     * @var FilesOption
     */
    private $filesOption;

    /**
     * @var ThemeResourceJSOption
     */
    private $themeResourceJSOption;

    /**
     * @var ThemeResourceCSSOption
     */
    private $themeResourceCSSOption;

    /**
     * @var ThemeResourceJSLocalOption
     */
    private $themeResourceJSLocalOption;

    /**
     * @var ThemeResourceCSSLocalOption
     */
    private $themeResourceCSSLocalOption;

    /**
     * FilesManager constructor.
     *
     * @param FileSyncStageOption $fileSyncStageOption
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param int $downloadAttempts
     * @param array $postTypes
     * @param bool $sync
     * @param FileSyncFailureOption $fileSyncFailureOption
     * @param ServiceSwitchOption $fileSyncOption
     * @param UseLocalFilesOption $useLocalFilesOption
     * @param DownloadListOfFiles $downloadListOfFiles
     * @param OriginUrlPostMeta $originUrlPostMeta
     * @param FileSubPathPostMeta $fileSubPathPostMeta
     * @param FilesOption $filesOption
     * @param DownloaderInterface $downloader
     * @param FilesystemInterface $fileSystem
     * @param string $destinationRoot
     * @param string $destinationPath
     * @param string $destinationUrl
     * @param ThemeResourceJSOption $themeResourceJSOption
     * @param ThemeResourceCSSOption $themeResourceCSSOption
     * @param ThemeResourceJSLocalOption $themeResourceJSLocalOption
     * @param ThemeResourceCSSLocalOption $themeResourceCSSLocalOption
     */
    public function __construct(
        FileSyncStageOption $fileSyncStageOption,
        $continueExecution,
        LoggerInterface $logger,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts,
        array $postTypes,
        $sync,
        FileSyncFailureOption $fileSyncFailureOption,
        ServiceSwitchOption $fileSyncOption,
        UseLocalFilesOption $useLocalFilesOption,
        DownloadListOfFiles $downloadListOfFiles,
        OriginUrlPostMeta $originUrlPostMeta,
        FileSubPathPostMeta $fileSubPathPostMeta,
        FilesOption $filesOption,
        DownloaderInterface $downloader,
        FilesystemInterface $fileSystem,
        $destinationRoot,
        $destinationPath,
        $destinationUrl,
        ThemeResourceJSOption $themeResourceJSOption,
        ThemeResourceCSSOption $themeResourceCSSOption,
        ThemeResourceJSLocalOption $themeResourceJSLocalOption,
        ThemeResourceCSSLocalOption $themeResourceCSSLocalOption
    ) {
        parent::__construct(
            $fileSyncStageOption,
            array(
                FileSyncStageOption::DOWNLOAD_FILES_LIST => array($this, 'stageDownloadFilesList'),
                FileSyncStageOption::CLEANUP => array($this, 'stageCleanup'),
                FileSyncStageOption::CREATE_ENTRIES => array($this, 'stageCreateEntries'),
                FileSyncStageOption::DOWNLOAD_FILES => array($this, 'stageDownloadFiles'),
                FileSyncStageOption::GENERATE_EDITOR_CONFIG => array($this, 'stageGenerateEditorConfig'),
                FileSyncStageOption::OK => '__return_true',
            ),
            $continueExecution,
            $logger,
            $attemptsToDownloadPostMeta,
            $downloadAttempts,
            SetkaPostTypes::GROUP_FILES,
            $postTypes
        );

        $this->sync                  = $sync;
        $this->fileSyncFailureOption = $fileSyncFailureOption;
        $this->fileSyncOption        = $fileSyncOption;
        $this->useLocalFilesOption   = $useLocalFilesOption;
        $this->downloadListOfFiles   = $downloadListOfFiles;
        $this->originUrlPostMeta     = $originUrlPostMeta;
        $this->fileSubPathPostMeta   = $fileSubPathPostMeta;
        $this->filesOption           = $filesOption;
        $this->downloader            = $downloader;
        $this->fileSystem            = $fileSystem;
        $this->destinationRoot       = $destinationRoot;
        $this->destinationPath       = $destinationPath;
        $this->destinationUrl        = PathsAndUrls::madeUrlProtocolRelative($destinationUrl);

        $this->themeResourceJSOption       = $themeResourceJSOption;
        $this->themeResourceCSSOption      = $themeResourceCSSOption;
        $this->themeResourceJSLocalOption  = $themeResourceJSLocalOption;
        $this->themeResourceCSSLocalOption = $themeResourceCSSLocalOption;
    }

    /**
     * @throws FailureOptionException
     * @throws SyncDisabledByUserException
     *
     * @return $this
     */
    public function run()
    {
        if (!$this->fileSyncOption->get() || !$this->sync) {
            throw new SyncDisabledByUserException();
        }

        if ($this->fileSyncFailureOption->get()) {
            throw new FailureOptionException();
        }

        try {
            return parent::run();
        } catch (PendingFilesException $exception) {
            $this->logger->info('Pending files exists in queue. Manager should be run again.');
        } catch (\Exception $exception) {
            $this->logger->error('Exception caught.', array($exception));
        }
    }

    /**
     * @inheritDoc
     */
    protected function afterStage($finishedStageName, $nextStageName)
    {
        if (FileSyncStageOption::OK === $nextStageName) {
            $this->markFinished();
        }
        return parent::afterStage($finishedStageName, $nextStageName);
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        parent::reset();

        foreach (array(
            $this->useLocalFilesOption,
            $this->fileSyncFailureOption,
            $this->themeResourceJSLocalOption,
            $this->themeResourceCSSLocalOption,
        ) as $option) {
            /**
             * @var $option OptionInterface
             */
            $option->delete();
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function stageDownloadFilesList()
    {
        $this->downloadListOfFiles->execute();
        $this->resetAllDownloadsCounters();
    }

    /**
     * @throws OutOfTimeException
     * @throws \Setka\Editor\Service\Manager\Exceptions\PostException
     */
    protected function stageCleanup()
    {
        $stage = new RemoveOldEntriesOnDiskStage(
            $this->continueExecution,
            $this->logger,
            new PendingFactory($this->postTypes),
            $this->fileSystem,
            $this->fileSubPathPostMeta,
            path_join($this->destinationRoot, $this->destinationPath)
        );
        $stage->setDeleteFiles(false);
        $stage->run();
    }

    /**
     * @inheritDoc
     * @throws OutOfTimeException
     * @throws \Setka\Editor\Service\Manager\Exceptions\PostException
     */
    public function deleteAllFiles()
    {
        $stage = new RemoveOldEntriesOnDiskStage(
            $this->continueExecution,
            $this->logger,
            new AnyFactory($this->postTypes),
            $this->fileSystem,
            $this->fileSubPathPostMeta,
            path_join($this->destinationRoot, $this->destinationPath)
        );
        $stage->setDeleteFiles(true);
        $stage->run();
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function stageCreateEntries()
    {
        $stage = new FilesCreator($this->filesOption, $this->continueExecution);
        $stage->createPosts();
    }

    /**
     * @throws \Exception
     */
    protected function stageDownloadFiles()
    {
        $stage = new DownloadStage(
            $this->continueExecution,
            $this->logger,
            new DraftFactory($this->postTypes),
            $this->originUrlPostMeta,
            $this->attemptsToDownloadPostMeta,
            $this->downloader,
            $this->fileSystem,
            array(new FileHandler(
                $this->fileSystem,
                $this->destinationRoot,
                $this->destinationPath
            ))
        );
        $stage->run();

        $stage = new IsPendingExistsStage(new PendingFactory($this->postTypes));
        $stage->run();
    }

    /**
     * @throws \Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\ConfigFileEntryException
     * @throws \Exception
     */
    protected function stageGenerateEditorConfig()
    {
        $stage = EditorConfigGeneratorFactory::create(
            $this->fileSystem,
            path_join($this->destinationRoot, $this->destinationPath),
            path_join($this->destinationUrl, $this->destinationPath),
            WPQueryFactory::createThemeJSON($this->themeResourceJSOption->get()),
            WPQueryFactory::createThemeCSS($this->themeResourceCSSOption->get()),
            $this->fileSubPathPostMeta,
            $this->themeResourceJSLocalOption,
            $this->themeResourceCSSLocalOption
        );
        $stage->generate();

        $this->markFinished();

        try {
            $sendFilesStatTask = new SendFilesStatCronEvent();
            if (!$sendFilesStatTask->isScheduled()) {
                $sendFilesStatTask->schedule();
            }
        } catch (\Exception $exception) {
        }
    }

    private function failureOnSyncing()
    {
        $this->fileSyncFailureOption->updateValue(true);
        return $this;
    }

    private function markFinished()
    {
        $this->logger->info('Manager successfully finished its work.');
        $this->useLocalFilesOption->updateValue(true);
        $this->fileSyncFailureOption->delete();
    }

    /**
     * @inheritDoc
     */
    public function checkPendingFiles()
    {
        try {
            parent::checkPendingFiles();
        } catch (AttemptsLimitException $exception) {
            $this->failureOnSyncing();
        } catch (\Exception $exception) {
        }
        return $this;
    }

    /**
     * Mark all files in DB as archived.
     *
     * After this operation this files will no longer affects downloading queue.
     *
     * @return mixed Result of SQL request with $wpdb->query().
     *
     * @throws FlushingCacheException If cache flushing was failed.
     */
    public function markAllFilesAsArchived()
    {
        global $wpdb;

        $queryResult = $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            "UPDATE {$wpdb->posts}
            SET
            post_status = %s
            WHERE
            post_type = %s",
            PostStatuses::ARCHIVE,
            SetkaPostTypes::FILE_POST_NAME
        ));

        $result = wp_cache_flush();

        // Different flushing mechanisms working different.
        // For example Memcached returns null as successful result.
        if (false === $result) {
            throw new FlushingCacheException();
        }

        return $queryResult;
    }

    /**
     * Completely remove downloads counters from post meta for all posts.
     *
     * And also resetting object cache.
     *
     * @return $this For chain calls.
     *
     * @throws FlushingCacheException If can't reset the object cache.
     * @throws DeletingAttemptsDownloadsMetaException If can't delete post metas from DB.
     */
    public function resetAllDownloadsCounters()
    {
        $result = wp_cache_flush();

        // Different flushing mechanisms working different.
        // For example Memcached returns null as successful result.
        if (false === $result) {
            throw new FlushingCacheException();
        }

        unset($result);
        global $wpdb;

        $result = $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s",
            $this->attemptsToDownloadPostMeta->getName()
        ));

        if (!is_numeric($result)) {
            throw new DeletingAttemptsDownloadsMetaException();
        }

        return $this;
    }
}
