<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ResponseFactory
{
    /**
     * @param mixed $response
     *
     * @return Response
     */
    public static function create($response): Response
    {
        $headers = self::createHeaders($response);
        return new Response(
            self::createContent($response, $headers),
            self::getStatusCode($response),
            $headers
        );
    }

    /**
     * @param mixed $response
     * @param ResponseHeaderBag $headers
     *
     * @return ParameterBag
     *
     * @throws \InvalidArgumentException
     */
    private static function createContent($response, ResponseHeaderBag $headers): ParameterBag
    {
        if ($content = $response['body'] ?? null) {
            return self::parseContent($content, $headers);
        }
        throw new \InvalidArgumentException();
    }

    /**
     * @param mixed $response
     *
     * @return integer
     */
    private static function getStatusCode($response): int
    {
        if (isset($response['response']['code']) && is_int($code = $response['response']['code'])) {
            return $code;
        }
        throw new \InvalidArgumentException('Cant find the HTTP status code in response.');
    }

    /**
     * @param mixed $response
     *
     * @return ResponseHeaderBag
     */
    private static function createHeaders($response): ResponseHeaderBag
    {
        // WP >= 4.6 -> \Requests_Utility_CaseInsensitiveDictionary
        // WP >= 6.2 -> \WpOrg\Requests\Utility\CaseInsensitiveDictionary
        if (isset($response['headers']) && is_object($response["headers"]) && method_exists($response["headers"], "getAll")) {
            /**
             * @var array $response {
             * @var $headers \Requests_Utility_CaseInsensitiveDictionary|\WpOrg\Requests\Utility\CaseInsensitiveDictionary
             * }
             */
            $headers = $response['headers']->getAll();
        } elseif (isset($response['headers']) && is_array($response['headers'])) { // WP < 4.6
            $headers = $response['headers'];
        } else {
            $headers = array();
        }

        return new ResponseHeaderBag($headers);
    }

    /**
     * Transforms JSON string into ParameterBag instance.
     *
     * @param ?string $content
     * @param ResponseHeaderBag $headers
     *
     * @return ParameterBag
     *
     * @throws \InvalidArgumentException If content not a JSON string.
     */
    private static function parseContent(?string $content, ResponseHeaderBag $headers): ParameterBag
    {
        $contentType = $headers->get('Content-Type');
        if (is_string($contentType) && 0 === strpos($contentType, 'application/json')) {
            $json = json_decode($content, true);
            $error = json_last_error();

            if (JSON_ERROR_NONE === $error && is_array($json)) {
                return new ParameterBag($json);
            } else {
                throw new \InvalidArgumentException('The response body contain invalid JSON data');
            }
        }

        throw new \InvalidArgumentException('The response body format not supported');
    }
}
