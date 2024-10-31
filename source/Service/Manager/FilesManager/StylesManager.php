<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractLastFailureNameOption;
use Setka\Editor\Admin\Options\Styles\AbstractStylesAggregateOption;
use Setka\Editor\Admin\Options\Styles\AbstractStylesIdOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncAttemptsLimitFailureOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureNoticeOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Admin\Options\Styles\AbstractUseStylesOption;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Admin\Service\FilesSync\DownloaderInterface;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\Service\Manager\Exceptions\PendingFilesException;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\Exceptions\ReadFileException;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\DBHandler;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\DownloadStage;
use Setka\Editor\Service\Manager\Stacks\ArchiveFactory;
use Setka\Editor\Service\Manager\Stacks\DraftFactory;
use Setka\Editor\Service\Manager\Stacks\PendingFactory;

class StylesManager extends BaseFilesManager
{
    /**
     * @var AbstractSyncOption
     */
    private $syncOption;

    /**
     * @var OriginUrlPostMeta
     */
    protected $originUrlPostMeta;

    /**
     * @var SetkaFileTypePostMeta
     */
    private $setkaFileTypePostMeta;

    /**
     * @var DownloaderInterface
     */
    protected $downloader;

    /**
     * @var FilesystemInterface
     */
    protected $fileSystem;

    /**
     * StylesManager constructor.
     *
     * @param AbstractSyncStageOption $currentStageOption
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param AbstractStylesIdOption $configIdOption
     * @param AbstractStylesAggregateOption $configOption
     * @param string $configPostType
     * @param AbstractSyncFailureOption $failureOption
     * @param AbstractLastFailureNameOption $failureNameOption
     * @param AbstractSyncFailureNoticeOption $failureNoticeOption
     * @param AbstractSyncAttemptsLimitFailureOption $attemptsLimitOption
     * @param AbstractUseStylesOption $finishedOption
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param integer $downloadAttempts
     * @param string $postTypesGroup
     * @param array $postTypes
     * @param AbstractSyncOption $syncOption
     * @param OriginUrlPostMeta $originUrlPostMeta
     * @param SetkaFileTypePostMeta $setkaFileTypePostMeta
     * @param DownloaderInterface $downloader
     * @param FilesystemInterface $fileSystem
     */
    public function __construct(
        AbstractSyncStageOption $currentStageOption,
        $continueExecution,
        LoggerInterface $logger,
        AbstractStylesIdOption $configIdOption,
        AbstractStylesAggregateOption $configOption,
        $configPostType,
        AbstractSyncFailureOption $failureOption,
        AbstractLastFailureNameOption $failureNameOption,
        AbstractSyncFailureNoticeOption $failureNoticeOption,
        AbstractSyncAttemptsLimitFailureOption $attemptsLimitOption,
        AbstractUseStylesOption $finishedOption,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts,
        $postTypesGroup,
        array $postTypes,
        AbstractSyncOption $syncOption,
        OriginUrlPostMeta $originUrlPostMeta,
        SetkaFileTypePostMeta $setkaFileTypePostMeta,
        DownloaderInterface $downloader,
        FilesystemInterface $fileSystem
    ) {
        $stagesMap = array(
            AbstractSyncStageOption::RESET_PREVIOUS_STATE => array($this, 'stageResetPreviousState'),
            AbstractSyncStageOption::CREATE_ENTRIES => array($this, 'stageCreateEntries'),
            AbstractSyncStageOption::REMOVE_OLD_ENTRIES => array($this, 'stageRemoveOldEntries'),
            AbstractSyncStageOption::DOWNLOAD_FILES => array($this, 'stageDownloadFiles'),
            AbstractSyncStageOption::OK => '__return_true',
        );

        parent::__construct(
            $currentStageOption,
            $stagesMap,
            $continueExecution,
            $logger,
            $configIdOption,
            $configOption,
            $configPostType,
            $failureOption,
            $failureNameOption,
            $failureNoticeOption,
            $attemptsLimitOption,
            $finishedOption,
            $attemptsToDownloadPostMeta,
            $downloadAttempts,
            $postTypesGroup,
            $postTypes
        );

        $this->syncOption            = $syncOption;
        $this->originUrlPostMeta     = $originUrlPostMeta;
        $this->setkaFileTypePostMeta = $setkaFileTypePostMeta;

        $this->downloader = $downloader;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->logger->info('Start running styles manager.');

        if (!$this->syncOption->get()) {
            $this->logger->info('Sync disabled by option. Stop executing.');
            return $this;
        }

        return parent::run();
    }

    /**
     * Reset download attempts counters on all posts.
     *
     * @throws OutOfTimeException
     * @throws PostException
     */
    protected function stageResetPreviousState()
    {
        $stage = new ArchiveAndResetErrorsStage(
            $this->continueExecution,
            $this->logger,
            $this->postTypes,
            $this->attemptsToDownloadPostMeta
        );
        $stage->run();

        $this->removePreviousConfigs();
    }

    /**
     * Create WordPress post entries and mark them as drafts.
     *
     * @throws PostException If post was not created.
     * @throws OutOfTimeException If current cron process obsolete and we need to break execution.
     */
    protected function stageCreateEntries()
    {
        $stage = new CreateEntriesStage(
            $this->continueExecution,
            $this->logger,
            $this->configOption,
            $this->postTypes,
            $this->originUrlPostMeta,
            $this->setkaFileTypePostMeta,
            $this->attemptsToDownloadPostMeta
        );
        $stage->run();
    }

    /**
     * @throws OutOfTimeException
     * @throws PostException
     * @throws \Exception
     */
    protected function stageRemoveOldEntries()
    {
        $stage = new RemoveOldEntriesStage(
            $this->continueExecution,
            $this->logger,
            new ArchiveFactory($this->postTypes)
        );
        $stage->run();
    }

    /**
     * @throws OutOfTimeException
     * @throws PendingFilesException
     * @throws PostException
     * @throws ReadFileException
     * @throws \Setka\Editor\Service\Manager\Exceptions\PostMetaException
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
            array(new DBHandler($this->fileSystem))
        );
        $stage->run();

        $stage = new IsPendingExistsStage(new PendingFactory($this->postTypes));
        $stage->run();
    }
}
