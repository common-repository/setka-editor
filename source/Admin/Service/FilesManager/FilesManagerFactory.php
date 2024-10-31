<?php
namespace Setka\Editor\Admin\Service\FilesManager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Files\FilesOption;
use Setka\Editor\Admin\Options\Files\FileSyncFailureOption;
use Setka\Editor\Admin\Options\Files\ServiceSwitchOption;
use Setka\Editor\Admin\Options\Files\FileSyncStageOption;
use Setka\Editor\Admin\Options\Files\UseLocalFilesOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceCSSOption;
use Setka\Editor\Admin\Options\ThemeResourceJSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceJSOption;
use Setka\Editor\Admin\Service\FilesSync\WordPressDownloader;
use Setka\Editor\Admin\Service\Filesystem\FilesystemFactory;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\Service\DataFactory;
use Setka\Editor\Service\SetkaPostTypes;

class FilesManagerFactory
{
    /**
     * @param FileSyncStageOption $fileSyncStageOption
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param int $downloadAttempts
     * @param bool $syncFiles
     * @param FileSyncFailureOption $fileSyncFailureOption
     * @param ServiceSwitchOption $fileSyncOption
     * @param UseLocalFilesOption $useLocalFilesOption
     * @param DownloadListOfFiles $downloadListOfFiles
     * @param FilesOption $filesOption
     * @param callable $destinationRoot
     * @param string $destinationPath
     * @param callable $destinationUrl
     * @param ThemeResourceJSOption $themeResourceJSOption
     * @param ThemeResourceCSSOption $themeResourceCSSOption
     * @param ThemeResourceJSLocalOption $themeResourceJSLocalOption
     * @param ThemeResourceCSSLocalOption $themeResourceCSSLocalOption
     * @param DataFactory $dataFactory
     *
     * @return FilesManager
     */
    public static function create(
        FileSyncStageOption $fileSyncStageOption,
        $continueExecution,
        LoggerInterface $logger,
        $downloadAttempts,
        $syncFiles,
        FileSyncFailureOption $fileSyncFailureOption,
        ServiceSwitchOption $fileSyncOption,
        UseLocalFilesOption $useLocalFilesOption,
        DownloadListOfFiles $downloadListOfFiles,
        FilesOption $filesOption,
        $destinationRoot,
        $destinationPath,
        $destinationUrl,
        ThemeResourceJSOption $themeResourceJSOption,
        ThemeResourceCSSOption $themeResourceCSSOption,
        ThemeResourceJSLocalOption $themeResourceJSLocalOption,
        ThemeResourceCSSLocalOption $themeResourceCSSLocalOption,
        DataFactory $dataFactory
    ) {
        $attemptsToDownloadPostMeta = $dataFactory->create(AttemptsToDownloadPostMeta::class);
        $originUrlPostMeta          = $dataFactory->create(OriginUrlPostMeta::class);
        $fileSubPathPostMeta        = $dataFactory->create(FileSubPathPostMeta::class);
        $downloader                 = new WordPressDownloader();
        $fileSystem                 = FilesystemFactory::create();

        return new FilesManager(
            $fileSyncStageOption,
            $continueExecution,
            $logger,
            $attemptsToDownloadPostMeta,
            $downloadAttempts,
            SetkaPostTypes::getPostTypes(SetkaPostTypes::GROUP_FILES),
            $syncFiles,
            $fileSyncFailureOption,
            $fileSyncOption,
            $useLocalFilesOption,
            $downloadListOfFiles,
            $originUrlPostMeta,
            $fileSubPathPostMeta,
            $filesOption,
            $downloader,
            $fileSystem,
            call_user_func($destinationRoot),
            $destinationPath,
            call_user_func($destinationUrl),
            $themeResourceJSOption,
            $themeResourceCSSOption,
            $themeResourceJSLocalOption,
            $themeResourceCSSLocalOption
        );
    }
}
