<?php
namespace Setka\Editor\Admin\Service\FilesSync;

use Setka\Editor\Admin\Service\FilesSync\Exceptions\FileDownloadException;

/**
 * Interface DownloaderInterface
 */
interface DownloaderInterface
{

    /**
     * Download a single file and
     *
     * @param $url string An url to file which need to be downloaded.
     *
     * @throws FileDownloadException If file was not downloaded.
     *
     * @return $this For chain calls.
     */
    public function download($url);

    /**
     * @return null|string|\WP_Error
     */
    public function getResult();

    /**
     * @param null|string|\WP_Error $result
     *
     * @return $this For chain calls.
     */
    public function setResult($result);
}
