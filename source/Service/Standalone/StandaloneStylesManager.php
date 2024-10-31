<?php
namespace Setka\Editor\Service\Standalone;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Standalone\StylesIdOption;
use Setka\Editor\Admin\Options\Standalone\StylesOption;
use Setka\Editor\Admin\Options\Standalone\SyncAttemptsLimitFailureOption;
use Setka\Editor\Admin\Options\Standalone\SyncFailureNoticeOption;
use Setka\Editor\Admin\Options\Standalone\SyncFailureOption;
use Setka\Editor\Admin\Options\Standalone\SyncLastFailureNameOption;
use Setka\Editor\Admin\Options\Standalone\SyncOption;
use Setka\Editor\Admin\Options\Standalone\SyncStageOption;
use Setka\Editor\Admin\Options\Standalone\UseStylesOption;
use Setka\Editor\Admin\Service\FilesSync\DownloaderInterface;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\Service\Manager\Exceptions\EarlyFinishException;
use Setka\Editor\Service\Manager\FilesManager\IsPendingExistsStage;
use Setka\Editor\Service\Manager\FilesManager\RemoveOldEntriesOnDiskStage;
use Setka\Editor\Service\Manager\FilesManager\RemoveOldEntriesStage;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\DBHandler;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\DownloadStage;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\FileHandler;
use Setka\Editor\Service\Manager\FilesManager\Stages\Downloads\RelativeToAbsoluteURLConverter;
use Setka\Editor\Service\Manager\FilesManager\StylesManager;
use Setka\Editor\Service\Manager\Stacks\AnyFactory;
use Setka\Editor\Service\Manager\Stacks\ArchiveFactory;
use Setka\Editor\Service\Manager\Stacks\DraftFactory;
use Setka\Editor\Service\Manager\Stacks\PendingFactory;
use Setka\Editor\Service\SetkaPostTypes;

class StandaloneStylesManager extends StylesManager
{
    /**
     * @var array
     */
    private $dbPostTypes = array(
        SetkaPostTypes::STANDALONE_COMMON_CRITICAL,
        SetkaPostTypes::STANDALONE_THEME_CRITICAL,
    );

    /**
     * @var array
     */
    private $filePostTypes = array(
        SetkaPostTypes::STANDALONE_COMMON,
        SetkaPostTypes::STANDALONE_COMMON_DEFERRED,
        SetkaPostTypes::STANDALONE_THEME,
        SetkaPostTypes::STANDALONE_THEME_DEFERRED,
    );

    /**
     * @var array
     */
    private $fileAndDBPostTypes = array(
        SetkaPostTypes::STANDALONE_LAYOUT,
    );

    /**
     * @var boolean
     */
    private $selfHostedFiles;

    /**
     * @var FileSubPathPostMeta
     */
    private $fileSubPathPostMeta;

    /**
     * @var string This directory should exists (usually wp-content/uploads).
     */
    private $destinationRoot;

    /**
     * @var string
     */
    private $destinationRootURL;

    /**
     * @var string Extra path for Setka Editor files (will automatically created).
     */
    private $destinationPath;

    /**
     * @param SyncStageOption $currentStageOption
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param StylesIdOption $configIdOption
     * @param StylesOption $configOption
     * @param SyncFailureOption $failureOption
     * @param SyncLastFailureNameOption $failureNameOption
     * @param SyncFailureNoticeOption $failureNoticeOption
     * @param SyncAttemptsLimitFailureOption $attemptsLimitOption
     * @param UseStylesOption $finishedOption
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param integer $downloadAttempts
     * @param SyncOption $syncOption
     * @param OriginUrlPostMeta $originUrlPostMeta
     * @param SetkaFileTypePostMeta $setkaFileTypePostMeta
     * @param DownloaderInterface $downloader
     * @param FilesystemInterface $fileSystem
     * @param boolean $selfHostedFiles
     * @param FileSubPathPostMeta $fileSubPathPostMeta
     * @param string $destinationRoot
     * @param string $destinationRootURL
     * @param string $destinationPath
     */
    public function __construct(
        SyncStageOption $currentStageOption,
        $continueExecution,
        LoggerInterface $logger,
        StylesIdOption $configIdOption,
        StylesOption $configOption,
        SyncFailureOption $failureOption,
        SyncLastFailureNameOption $failureNameOption,
        SyncFailureNoticeOption $failureNoticeOption,
        SyncAttemptsLimitFailureOption $attemptsLimitOption,
        UseStylesOption $finishedOption,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts,
        SyncOption $syncOption,
        OriginUrlPostMeta $originUrlPostMeta,
        SetkaFileTypePostMeta $setkaFileTypePostMeta,
        DownloaderInterface $downloader,
        FilesystemInterface $fileSystem,
        $selfHostedFiles,
        $fileSubPathPostMeta,
        $destinationRoot,
        $destinationRootURL,
        $destinationPath
    ) {
        $configPostType = SetkaPostTypes::STANDALONE_CONFIG;
        $postTypesGroup = SetkaPostTypes::GROUP_STANDALONE;
        $postTypes      = SetkaPostTypes::getPostTypes($postTypesGroup);

        parent::__construct(
            $currentStageOption,
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
            $postTypes,
            $syncOption,
            $originUrlPostMeta,
            $setkaFileTypePostMeta,
            $downloader,
            $fileSystem
        );

        $this->selfHostedFiles     = $selfHostedFiles;
        $this->fileSubPathPostMeta = $fileSubPathPostMeta;
        $this->destinationRoot     = $destinationRoot;
        $this->destinationRootURL  = $destinationRootURL;
        $this->destinationPath     = $destinationPath;
    }

    /**
     * @inheritDoc
     */
    protected function afterStage($finishedStageName, $nextStageName)
    {
        parent::afterStage($finishedStageName, $nextStageName);
        if (!$this->selfHostedFiles && SyncStageOption::RESET_PREVIOUS_STATE === $finishedStageName) {
            $this->markFinished();
            throw new EarlyFinishException();
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function stageRemoveOldEntries()
    {
        $stage = new RemoveOldEntriesOnDiskStage(
            $this->continueExecution,
            $this->logger,
            new ArchiveFactory(array_merge($this->filePostTypes, $this->fileAndDBPostTypes)),
            $this->fileSystem,
            $this->fileSubPathPostMeta,
            path_join($this->destinationRoot, $this->destinationPath)
        );
        $stage->setDeleteFiles(true);
        $stage->run();

        $stage = new RemoveOldEntriesStage(
            $this->continueExecution,
            $this->logger,
            new ArchiveFactory($this->dbPostTypes)
        );
        $stage->run();
    }

    /**
     * @inheritDoc
     */
    protected function stageDownloadFiles()
    {
        // Download STANDALONE_THEME_CRITICAL separately because
        // this files need the RelativeToAbsoluteURLConverter.
        $stage = new DownloadStage(
            $this->continueExecution,
            $this->logger,
            new DraftFactory(array(SetkaPostTypes::STANDALONE_THEME_CRITICAL)),
            $this->originUrlPostMeta,
            $this->attemptsToDownloadPostMeta,
            $this->downloader,
            $this->fileSystem,
            array(new DBHandler($this->fileSystem, array(new RelativeToAbsoluteURLConverter(path_join($this->destinationRootURL, $this->destinationPath)))))
        );
        $stage->run();

        // Other DB files (except STANDALONE_THEME_CRITICAL)
        // will be downloaded without converter.
        $stage = new DownloadStage(
            $this->continueExecution,
            $this->logger,
            new DraftFactory($this->dbPostTypes),
            $this->originUrlPostMeta,
            $this->attemptsToDownloadPostMeta,
            $this->downloader,
            $this->fileSystem,
            array(new DBHandler($this->fileSystem))
        );
        $stage->run();

        $stage = new DownloadStage(
            $this->continueExecution,
            $this->logger,
            new DraftFactory($this->filePostTypes),
            $this->originUrlPostMeta,
            $this->attemptsToDownloadPostMeta,
            $this->downloader,
            $this->fileSystem,
            array(new FileHandler($this->fileSystem, $this->destinationRoot, $this->destinationPath))
        );
        $stage->run();

        $stage = new DownloadStage(
            $this->continueExecution,
            $this->logger,
            new DraftFactory($this->fileAndDBPostTypes),
            $this->originUrlPostMeta,
            $this->attemptsToDownloadPostMeta,
            $this->downloader,
            $this->fileSystem,
            array(
                new DBHandler($this->fileSystem),
                new FileHandler($this->fileSystem, $this->destinationRoot, $this->destinationPath)
            )
        );
        $stage->run();

        $stage = new IsPendingExistsStage(new PendingFactory($this->postTypes));
        $stage->run();
    }

    /**
     * @inheritDoc
     */
    public function deleteAllFiles()
    {
        $stage = new RemoveOldEntriesOnDiskStage(
            $this->continueExecution,
            $this->logger,
            new AnyFactory(array_merge($this->filePostTypes, $this->fileAndDBPostTypes)),
            $this->fileSystem,
            $this->fileSubPathPostMeta,
            path_join($this->destinationRoot, $this->destinationPath)
        );
        $stage->setDeleteFiles(true);
        $stage->run();

        $stage = new RemoveOldEntriesStage(
            $this->continueExecution,
            $this->logger,
            new AnyFactory($this->dbPostTypes)
        );
        $stage->run();

        return $this;
    }
}
