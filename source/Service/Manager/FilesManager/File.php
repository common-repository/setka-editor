<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Setka\Editor\Exceptions\DomainException;

class File
{
    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * @var string
     */
    private $originUrl;

    /**
     * @var string
     */
    private $subPath;

    /**
     * @var string|null
     */
    private $currentLocation;

    /**
     * @param \WP_Post $post
     * @param string $originUrl
     *
     * @throws DomainException
     */
    public function __construct(\WP_Post $post, string $originUrl)
    {
        $this->post      = $post;
        $this->originUrl = $originUrl;
        $this->subPath   = $this->createSubPath();
    }

    /**
     * @return string
     * @throws DomainException
     */
    private function createSubPath(): string
    {
        $path = parse_url( // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
            $this->originUrl,
            PHP_URL_PATH
        );
        if (is_string($path)) {
            return ltrim($path, '/');
        }
        throw new DomainException();
    }

    /**
     * @return string
     */
    public function getSubPath(): string
    {
        return $this->subPath;
    }

    /**
     * @return \WP_Post
     */
    public function getPost(): \WP_Post
    {
        return $this->post;
    }

    /**
     * @return int
     */
    public function getID(): int
    {
        return $this->post->ID;
    }

    /**
     * @return string
     */
    public function getOriginUrl(): string
    {
        return $this->originUrl;
    }

    /**
     * @return ?string
     */
    public function getCurrentLocation(): ?string
    {
        return $this->currentLocation;
    }

    /**
     * @param ?string $currentLocation
     */
    public function setCurrentLocation(?string $currentLocation): void
    {
        $this->currentLocation = $currentLocation;
    }
}
