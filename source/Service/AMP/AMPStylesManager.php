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
use Setka\Editor\Admin\Service\FilesSync\DownloaderInterface;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\Service\Manager\FilesManager\StylesManager;
use Setka\Editor\Service\SetkaPostTypes;

class AMPStylesManager extends StylesManager
{
    /**
     * @param AMPSyncStageOption $currentStageOption
     * @param callable$continueExecution
     * @param LoggerInterface $logger
     * @param AMPStylesIdOption $configIdOption
     * @param AMPStylesOption $configOption
     * @param AMPSyncFailureOption $failureOption
     * @param AMPSyncLastFailureNameOption $failureNameOption
     * @param AMPSyncFailureNoticeOption $failureNoticeOption
     * @param AMPSyncAttemptsLimitFailureOption $attemptsLimitOption
     * @param UseAMPStylesOption $finishedOption
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param integer $downloadAttempts
     * @param AMPSyncOption $syncOption
     * @param OriginUrlPostMeta $originUrlPostMeta
     * @param SetkaFileTypePostMeta $setkaFileTypePostMeta
     * @param DownloaderInterface $downloader
     * @param FilesystemInterface $fileSystem
     */
    public function __construct(
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
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts,
        AMPSyncOption $syncOption,
        OriginUrlPostMeta $originUrlPostMeta,
        SetkaFileTypePostMeta $setkaFileTypePostMeta,
        DownloaderInterface $downloader,
        FilesystemInterface $fileSystem
    ) {
        $configPostType = SetkaPostTypes::AMP_CONFIG;
        $postTypesGroup = SetkaPostTypes::GROUP_AMP;
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
    }
}
