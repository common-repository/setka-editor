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
use Setka\Editor\Admin\Service\FilesSync\WordPressDownloader;
use Setka\Editor\Admin\Service\Filesystem\FilesystemFactory;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\Service\DataFactory;

class StandaloneStylesManagerFactory
{
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
     * @param SyncOption $syncOption
     * @param DataFactory $dataFactory
     * @param integer $downloadAttempts
     * @param boolean $selfHostedFiles
     * @param callable $storagePath
     * @param callable $storageURL
     * @param string $storageBasename
     *
     * @return StandaloneStylesManager
     * @throws \RuntimeException
     */
    public static function create(
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
        SyncOption $syncOption,
        DataFactory $dataFactory,
        $downloadAttempts,
        $selfHostedFiles,
        $storagePath,
        $storageURL,
        $storageBasename
    ) {
        if (!$storagePath) {
            throw new \RuntimeException('Storage path not defined. Please Check your WordPress configuration.');
        }

        /**
         * @var $originUrlPostMeta OriginUrlPostMeta
         * @var $setkaFileTypePostMeta SetkaFileTypePostMeta
         * @var $attemptsToDownloadPostMeta AttemptsToDownloadPostMeta
         * @var $fileSubPathPostMeta FileSubPathPostMeta
         */
        $downloader                 = new WordPressDownloader();
        $originUrlPostMeta          = $dataFactory->create(OriginUrlPostMeta::class);
        $setkaFileTypePostMeta      = $dataFactory->create(SetkaFileTypePostMeta::class);
        $attemptsToDownloadPostMeta = $dataFactory->create(AttemptsToDownloadPostMeta::class);
        $fileSubPathPostMeta        = $dataFactory->create(FileSubPathPostMeta::class);

        $fileSystem = FilesystemFactory::create();

        return new StandaloneStylesManager(
            $currentStageOption,
            $continueExecution,
            $logger,
            $configIdOption,
            $configOption,
            $failureOption,
            $failureNameOption,
            $failureNoticeOption,
            $attemptsLimitOption,
            $finishedOption,
            $attemptsToDownloadPostMeta,
            $downloadAttempts,
            $syncOption,
            $originUrlPostMeta,
            $setkaFileTypePostMeta,
            $downloader,
            $fileSystem,
            $selfHostedFiles,
            $fileSubPathPostMeta,
            call_user_func($storagePath),
            call_user_func($storageURL),
            $storageBasename
        );
    }
}
