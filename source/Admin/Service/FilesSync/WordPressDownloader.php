<?php
namespace Setka\Editor\Admin\Service\FilesSync;

use Setka\Editor\Admin\Service\FilesSync\Exceptions\FileDownloadException;

/**
 * Class WordPressDownloader
 */
class WordPressDownloader implements DownloaderInterface
{
    /**
     * Null by default.
     *
     * \WP_Error if error during file downloading.
     *
     * string if file was successful downloaded. String represent path to file.
     *
     * @var null|\WP_Error|string Result of download_url method.
     */
    protected $result;

    /**
     * @inheritdoc
     */
    public function download($url)
    {
        $filename      = basename(parse_url($url, PHP_URL_PATH));  // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
        $temporaryFile = wp_tempnam($filename);

        if (!$temporaryFile) {
            throw new FileDownloadException();
        }

        $response = wp_safe_remote_get(
            $url,
            array(
                'stream' => true,
                'filename' => $temporaryFile,
                'headers' => array(
                    // Disable GZIP and any other compressing since
                    'Accept-Encoding' => 'identity;q=0',
                ),
            )
        );

        $this->result = $response;

        if (is_wp_error($response)) {
            unlink($temporaryFile); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
            throw new FileDownloadException();
        }

        if (200 !== wp_remote_retrieve_response_code($response)) {
            unlink($temporaryFile); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
            throw new FileDownloadException();
        }

        $md5 = wp_remote_retrieve_header($response, 'content-md5');
        if ($md5) {
            $md5Check = verify_file_md5($temporaryFile, $md5);
            if (is_wp_error($md5Check)) {
                unlink($temporaryFile); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_unlink
                $this->result = $md5Check;
                throw new FileDownloadException();
            }
        }

        $this->result = $temporaryFile;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @inheritdoc
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }
}
