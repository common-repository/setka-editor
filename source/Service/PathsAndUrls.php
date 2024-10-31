<?php
namespace Setka\Editor\Service;

use Setka\Editor\Exceptions\RuntimeException;

class PathsAndUrls
{
    /**
     * @param $path string Path to file or page
     *
     * @throws RuntimeException
     *
     * @return array
     */
    public static function splitUrlPathIntoFragments($path)
    {
        $path = trim($path, '/');

        if (!is_string($path) || empty($path)) {
            throw new RuntimeException('Invalid type of argument. Expected not empty string.');
        }

        if (preg_match('/[^a-z\d\-\_\/]/i', $path)) {
            throw new RuntimeException('Invalid path: ' . $path);
        }

        return explode('/', $path);
    }

    public static function madeUrlProtocolRelative(string $url): string
    {
        $protocolEnds = strpos($url, '://');

        if (is_int($protocolEnds) && $protocolEnds > 0) {
            $protocolEnds++;
            $url = substr($url, $protocolEnds);
        }

        return $url;
    }
}
