<?php
namespace Setka\Editor\Service\Manager;

use Setka\Editor\Service\Manager\Exceptions\JsonEncodeException;

class Config implements ConfigInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array|null
     */
    public function get()
    {
        return $this->config;
    }

    /**
     * @return string
     * @throws JsonEncodeException
     */
    public function encode()
    {
        $json = wp_json_encode($this->config);

        if (is_string($json)) {
            return $json;
        }
        throw new JsonEncodeException();
    }
}
