<?php
namespace Setka\Editor\Service\Manager\FilesManager\Stages\Downloads;

use Setka\Editor\Exceptions\RuntimeException;
use Setka\Editor\Service\Manager\FilesManager\File;

class RelativeToAbsoluteURLConverter implements ConverterInterface
{
    /**
     * @var array
     */
    private $pattern = '/src\s*:\s*url\s*\(\s*(?:\"|\'){0,1}([\pL\pN\-._\~!$&\'()*+,;=:@\/]+|%%[0-9A-Fa-f]{2})(?:\"|\'){0,1}\s*\)/';

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var File
     */
    private $file;

    /**
     * RelativeToAbsoluteURLConverter constructor.
     *
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = trailingslashit($baseUrl);
    }

    /**
     * @inheritDoc
     */
    public function convert(string $content, File $file): string
    {
        $this->file = $file;

        $result = preg_replace_callback($this->pattern, array($this, 'regexCallback'), $content);

        if (is_string($result)) {
            return $result;
        }

        throw new RuntimeException();
    }

    public function regexCallback(array $matches): string
    {
        if (empty($matches[1])) {
            throw new RuntimeException();
        }

        $host = parse_url($matches[1], PHP_URL_HOST); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
        $path = parse_url($matches[1], PHP_URL_PATH); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

        if (is_string($host) || path_is_absolute($path)) {
            return $matches[1];
        }

        return sprintf('src:url(\'%s\')', $this->baseUrl . dirname($this->file->getSubPath()) . '/' . $matches[1]);
    }
}
