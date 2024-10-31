<?php
namespace Setka\Editor\Service\Manager;

use Setka\Editor\Service\Manager\Exceptions\InvalidConfigException;
use Setka\Editor\Service\Manager\Exceptions\JsonDecodeException;
use Setka\Editor\Service\WPPostFactory;

class PostConfig extends Config
{
    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * Config constructor.
     *
     * @param \WP_Post $post
     * @throws InvalidConfigException
     */
    public function __construct(\WP_Post $post)
    {
        if (!WPPostFactory::isValidPost($post)) {
            throw new InvalidConfigException();
        }

        $this->post = $post;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->post->ID;
    }

    /**
     * @return string
     */
    public function getDateGMT()
    {
        return $this->post->post_date_gmt;
    }

    /**
     * Transform post_content into array with config.
     *
     * @throws JsonDecodeException If JSON decoding was failed.
     *
     * @return $this
     */
    public function decode()
    {
        $config = json_decode($this->post->post_content, true);

        if (is_array($config)) {
            $this->config = $config;
            return $this;
        }

        throw new JsonDecodeException(json_last_error_msg(), json_last_error());
    }
}
