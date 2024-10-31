<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters;

use Setka\Editor\Exceptions\OutOfBoundsException;
use Setka\Editor\Exceptions\OutOfRangeException;
use Setka\Editor\Exceptions\RuntimeException;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFromScriptFactory;

class KeyframesFactoryLibrary
{
    /**
     * @var KeyframesFromScriptFactory[]
     */
    private $keyframes = array();

    /**
     * @param array $names
     * @param KeyframesFromScriptFactory $keyframes
     * @return $this
     * @throws OutOfBoundsException
     * @throws RuntimeException
     */
    public function add(array $names, KeyframesFromScriptFactory $keyframes)
    {
        $key = $this->createKey($names);

        if (isset($this->keyframes[$key])) {
            throw new RuntimeException('The key already exist: ' . $key);
        }

        $this->keyframes[$key] = $keyframes;
        return $this;
    }

    /**
     * @param array $names
     * @return KeyframesFromScriptFactory
     * @throws OutOfBoundsException
     * @throws OutOfRangeException
     */
    public function get(array $names)
    {
        $key = $this->createKey($names);
        if (isset($this->keyframes[$key])) {
            return $this->keyframes[$key];
        }
        throw new OutOfRangeException();
    }

    /**
     * @return \Generator
     */
    public function scripts()
    {
        foreach ($this->keyframes as $keyframe) {
            $scripts = array($keyframe->getMobileScriptElement(), $keyframe->getDesktopScriptElement());
            foreach ($scripts as $script) {
                if ($script) {
                    yield $script;
                }
            }
        }
    }

    /**
     * @param array $names
     * @return string
     * @throws OutOfBoundsException
     */
    private function createKey(array $names)
    {
        $valid = false;

        foreach ($names as $name) {
            if (is_string($name)) {
                $valid = true;
            }
        }

        if ($valid) {
            return implode('-', $names);
        }

        throw new OutOfBoundsException();
    }
}
