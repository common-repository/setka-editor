<?php
namespace Setka\Editor\Admin\Service\EditorConfigGenerator;

use Setka\Editor\Admin\Service\EditorConfigGenerator\Exceptions\ParsingConfigPathException;

class FileInfo
{
    /**
     * @var string Path to root folder where files stored.
     */
    private $rootPath;

    /**
     * @var string Url to rootPath.
     */
    private $rootUrl;

    /**
     * @var string Sub path to file which we want use (for creating URL)
     */
    private $subPath;

    /**
     * @var string Sub path to file which we want make (for creating URL)
     */
    private $subPathLocal;

    /**
     * @param string $rootPath
     * @param string $rootUrl
     * @param string $subPath
     *
     * @throws ParsingConfigPathException
     */
    public function __construct(string $rootPath, string $rootUrl, string $subPath)
    {
        $this->rootPath = untrailingslashit($rootPath);
        $this->rootUrl  = untrailingslashit($rootUrl);
        $this->subPath  = ltrim($subPath, '/'); // Used only / because $subPath was extracted from URL

        $this->preparePaths();
    }

    private function preparePaths(): void
    {
        $info = pathinfo($this->subPath);

        if (!isset($info['dirname']) || !isset($info['extension']) || !isset($info['filename'])) {
            throw new ParsingConfigPathException();
        }

        $this->subPathLocal = $info['dirname'] . '/' . $info['filename'] . '-local.' . $info['extension'];
    }

    public function getPath(): string
    {
        return path_join($this->rootPath, $this->subPath);
    }

    public function getUrl(): string
    {
        return $this->rootUrl . '/' . $this->subPath;
    }

    public function getPathLocal(): string
    {
        return path_join($this->rootPath, $this->subPathLocal);
    }

    public function getUrlLocal(): string
    {
        return $this->rootUrl . '/' . $this->subPathLocal;
    }
}
