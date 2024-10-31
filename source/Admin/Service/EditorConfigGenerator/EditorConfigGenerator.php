<?php
namespace Setka\Editor\Admin\Service\EditorConfigGenerator;

use Setka\Editor\Admin\Options\ThemeResourceCSSLocalOption;
use Setka\Editor\Admin\Options\ThemeResourceJSLocalOption;
use Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\DecodingJSONException;
use Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\EncodingJSONException;
use Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\ReadingConfigFileException;
use Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\WritingConfigFileException;
use Setka\Editor\Admin\Service\Filesystem\FilesystemInterface;

class EditorConfigGenerator
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string Path to folder where all setka-editor assets files located.
     */
    private $rootPath;

    /**
     * @var string URL to folder where all setka-editor assets files located.
     */
    private $rootUrl;

    /**
     * @var FileInfo
     */
    private $jsonFileInfo;

    /**
     * @var FileInfo
     */
    private $cssFileInfo;

    /**
     * @var ThemeResourceJSLocalOption
     */
    private $jsLocalOption;

    /**
     * @var ThemeResourceCSSLocalOption
     */
    private $cssLocalOption;

    /**
     * @var array
     */
    private $config;

    /**
     * @param FilesystemInterface $filesystem
     * @param string $rootPath
     * @param string $rootUrl
     * @param FileInfo $jsonFileInfo
     * @param FileInfo $cssFileInfo
     * @param ThemeResourceJSLocalOption $jsLocalOption
     * @param ThemeResourceCSSLocalOption $cssLocalOption
     */
    public function __construct(
        FilesystemInterface $filesystem,
        string $rootPath,
        string $rootUrl,
        FileInfo $jsonFileInfo,
        FileInfo $cssFileInfo,
        ThemeResourceJSLocalOption $jsLocalOption,
        ThemeResourceCSSLocalOption $cssLocalOption
    ) {
        $this->filesystem     = $filesystem;
        $this->rootPath       = untrailingslashit($rootPath);
        $this->rootUrl        = untrailingslashit($rootUrl);
        $this->jsonFileInfo   = $jsonFileInfo;
        $this->cssFileInfo    = $cssFileInfo;
        $this->jsLocalOption  = $jsLocalOption;
        $this->cssLocalOption = $cssLocalOption;
    }

    /**
     * Generates the JSON config for Setka Editor and setups the links to local files.
     *
     * @throws \Exception Different exceptions, see methods called from this method.
     */
    public function generate(): void
    {
        $this->loadJSON();
        $this->replaceUrls();
        $this->saveJSON();
        $this->saveLocalUrls();
    }

    /**
     * Loads JSON from file into local variable as array.
     * @throws DecodingJSONException If JSON file is broken.
     * @throws ReadingConfigFileException If filesystem can't read the file content.
     */
    private function loadJSON(): void
    {
        try {
            $fileContent = $this->filesystem->getContents($this->jsonFileInfo->getPath());
        } catch (\Exception $exception) {
            throw new ReadingConfigFileException();
        }

        $json = json_decode($fileContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DecodingJSONException();
        }

        $this->config = $json;
    }

    /**
     * Replaces all urls in config to new.
     * @see replaceUrlsHandler
     */
    private function replaceUrls(): void
    {
        array_walk_recursive($this->config, array($this, 'replaceUrlsHandler'));
    }

    /**
     * Actually makes replacements in $this->config.
     *
     * @param $item string|int The name of array cell.
     * @param $key mixed The value of array cell.
     */
    public function replaceUrlsHandler(&$item, $key): void
    {
        // A hack for single file.
        if ('public_js_url' === $key) {
            return;
        }

        // Urls have only string type

        if (!is_string($item) || empty($item)) {
            return;
        }

        $startsWith = substr($key, 0, 1);

        if (!$startsWith) {
            return;
        }

        if ('_' === $startsWith) {
            return;
        }

        unset($startsWith);


        // Search for _url at the end of key.
        $endsWith = substr($key, -3);
        if (!$endsWith) {
            return;
        }
        if ('url' !== $endsWith) {
            return;
        }

        // Since we supporting only PHP > 5.5.9 parse_url() works pretty similar
        // on all versions above and there is no need to use wp_parse_url()
        // which is created for support older PHP versions.
        $scheme = parse_url($item, PHP_URL_SCHEME); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
        $host   = parse_url($item, PHP_URL_HOST); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
        $path   = parse_url($item, PHP_URL_PATH); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

        if (!$scheme || !$host || !$path) {
            return;
        }

        $item = $this->rootUrl . '/' . ltrim($path, '/');
    }

    /**
     * Saves $this->config as JSON on the disk.
     * @throws EncodingJSONException If cant encode config into JSON string.
     * @throws WritingConfigFileException If filesystem can't write string into file.
     */
    private function saveJSON(): void
    {
        $json = wp_json_encode($this->config);

        if (!$json) {
            throw new EncodingJSONException();
        }

        try {
            $this->filesystem->putContent($this->jsonFileInfo->getPathLocal(), $json);
        } catch (\Exception $exception) {
            throw new WritingConfigFileException();
        }
    }

    private function saveLocalUrls(): void
    {
        $this->jsLocalOption->updateValue($this->jsonFileInfo->getUrlLocal());
        $this->cssLocalOption->updateValue($this->cssFileInfo->getUrl());
    }
}
