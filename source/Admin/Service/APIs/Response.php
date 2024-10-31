<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response
{
    /**
     * @var ParameterBag
     */
    private $content;

    /**
     * @var integer
     */
    private $statusCode;

    /**
     * @var ResponseHeaderBag
     */
    private $headers;

    public function __construct(ParameterBag $content, int $statusCode, ResponseHeaderBag $headers)
    {
        $this->content    = $content;
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
    }

    public function getContent(): ParameterBag
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function isOk(): bool
    {
        return \Symfony\Component\HttpFoundation\Response::HTTP_OK === $this->statusCode;
    }

    public function getHeaders(): ResponseHeaderBag
    {
        return $this->headers;
    }
}
