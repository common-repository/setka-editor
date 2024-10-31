<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters\AnimationConfig;

interface AnimationConfigInterface
{
    /**
     * @return string
     */
    public function getID();

    /**
     * @return array
     */
    public function asArray();

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function get($key);
}
