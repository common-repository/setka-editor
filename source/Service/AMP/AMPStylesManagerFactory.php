<?php
namespace Setka\Editor\Service\AMP;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\AMP\AMPStylesIdOption;
use Setka\Editor\Admin\Options\AMP\AMPStylesOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncAttemptsLimitFailureOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncFailureNoticeOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncFailureOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncLastFailureNameOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncStageOption;
use Setka\Editor\Admin\Options\AMP\UseAMPStylesOption;
use Setka\Editor\Admin\Service\FilesSync\WordPressDownloader;
use Setka\Editor\Admin\Service\Filesystem\FilesystemFactory;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\Service\DataFactory;

class AMPStylesManagerFactory
{
    /**
     * @param AMPSyncStageOption $currentStageOption
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param AMPStylesIdOption $configIdOption
     * @param AMPStylesOption $configOption
     * @param AMPSyncFailureOption $failureOption
     * @param AMPSyncLastFailureNameOption $failureNameOption
     * @param AMPSyncFailureNoticeOption $failureNoticeOption
     * @param AMPSyncAttemptsLimitFailureOption $attemptsLimitOption
     * @param UseAMPStylesOption $finishedOption
     * @param AMPSyncOption $syncOption
     * @param DataFactory $dataFactory
     * @param integer $downloadAttempts
     *
     * @return AMPStylesManager
     */
    public static function create(
        AMPSyncStageOption $currentStageOption,
        $continueExecution,
        LoggerInterface $logger,
        AMPStylesIdOption $configIdOption,
        AMPStylesOption $configOption,
        AMPSyncFailureOption $failureOption,
        AMPSyncLastFailureNameOption $failureNameOption,
        AMPSyncFailureNoticeOption $failureNoticeOption,
        AMPSyncAttemptsLimitFailureOption $attemptsLimitOption,
        UseAMPStylesOption $finishedOption,
        AMPSyncOption $syncOption,
        DataFactory $dataFactory,
        $downloadAttempts
    ) {
        /**
         * @var $originUrlPostMeta OriginUrlPostMeta
         * @var $setkaFileTypePostMeta SetkaFileTypePostMeta
         * @var $attemptsToDownloadPostMeta AttemptsToDownloadPostMeta
         */
        $downloader                 = new WordPressDownloader();
        $originUrlPostMeta          = $dataFactory->create(OriginUrlPostMeta::class);
        $setkaFileTypePostMeta      = $dataFactory->create(SetkaFileTypePostMeta::class);
        $attemptsToDownloadPostMeta = $dataFactory->create(AttemptsToDownloadPostMeta::class);

        $fileSystem = FilesystemFactory::create();

        return new AMPStylesManager(
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
            $fileSystem
        );
    }
}
