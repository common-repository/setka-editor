<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes;

use Setka\Editor\Service\AMP\AnimationSanitizer\InitialStyles;

class KeyframesFromAttributesFactory implements KeyframesFactoryInterface
{
    const OPACITY = 'opacity';

    const TRANSFORM = 'transform';

    /**
     * @var InitialStyles
     */
    private $initialStyles;

    /**
     * @var array
     */
    private $keyframes;

    /**
     * @param InitialStyles $initialStyles
     */
    public function __construct(InitialStyles $initialStyles)
    {
        $this->initialStyles = $initialStyles;
    }

    /**
     * @inheritDoc
     */
    public function build()
    {
        $this->keyframes = array(
            array(
                self::OPACITY => $this->initialStyles->getOpacity(),
                self::TRANSFORM => $this->initialStyles->getTransform(),
            ),
            array(
                self::OPACITY => 1,
                self::TRANSFORM => 'none',
            ),
        );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDesktop()
    {
        return $this->keyframes;
    }

    /**
     * @inheritDoc
     */
    public function getMobile()
    {
        return $this->keyframes;
    }
}
