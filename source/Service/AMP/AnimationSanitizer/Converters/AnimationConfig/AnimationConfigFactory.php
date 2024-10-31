<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters\AnimationConfig;

use Setka\Editor\Exceptions\LogicException;
use Setka\Editor\Service\AMP\AnimationSanitizer\AnimatedDOMElement;
use Setka\Editor\Service\AMP\AnimationSanitizer\AnimatedDOMElementInterface;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFactoryInterface;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFromAttributesFactory;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFromScriptFactory;

class AnimationConfigFactory
{
    const MODE_MOBILE = 1;

    const MODE_DESKTOP = 2;

    const MEDIA_VALUE_MOBILE = '(max-width: 767px)';

    const MEDIA_VALUE_DESKTOP = '(min-width: 768px)';

    /**
     * @param string $id
     * @param AnimatedDOMElementInterface $element
     * @param KeyframesFromAttributesFactory $keyframesFactory
     * @return AnimationConfig
     */
    public static function createKeyframesFromAttributes(
        $id,
        AnimatedDOMElementInterface $element,
        KeyframesFromAttributesFactory $keyframesFactory
    ) {
        return self::create($id, $element, $keyframesFactory);
    }

    /**
     * @param string $id
     * @param AnimatedDOMElementInterface $element
     * @param KeyframesFromScriptFactory $keyframesFactory
     * @return AnimationConfig
     */
    public static function createKeyframesFromScript(
        $id,
        AnimatedDOMElementInterface $element,
        KeyframesFromScriptFactory $keyframesFactory
    ) {
        return self::create($id, $element, $keyframesFactory);
    }

    /**
     * @param string $id
     * @param AnimatedDOMElementInterface $element
     * @param KeyframesFactoryInterface $keyframesFactory
     * @return AnimationConfig
     */
    private static function create(
        $id,
        AnimatedDOMElementInterface $element,
        KeyframesFactoryInterface $keyframesFactory
    ) {
        $keyframesFactory->build();
        $switch = array();

        if (!$element->hasMobileAnimationAttribute() || $element->isAnimationMobile()) {
            $switch[] = self::createSwitchState(
                self::MEDIA_VALUE_MOBILE,
                self::getModeForMobile($keyframesFactory),
                $element,
                $keyframesFactory
            );
        }

        if ($element->isAnimationDesktop()) {
            $switch[] = self::createSwitchState(
                self::MEDIA_VALUE_DESKTOP,
                self::MODE_DESKTOP,
                $element,
                $keyframesFactory
            );
        }

        return self::createAnimationConfig($id, $switch);
    }

    /**
     * @param string $mediaValue
     * @param integer $mode
     * @param AnimatedDOMElementInterface $element
     * @param KeyframesFactoryInterface $keyframesFactory
     * @return array
     */
    private static function createSwitchState(
        $mediaValue,
        $mode,
        AnimatedDOMElementInterface $element,
        KeyframesFactoryInterface $keyframesFactory
    ) {
        switch ($mode) {
            case self::MODE_MOBILE:
                $modeArgument     = AnimatedDOMElement::MOBILE_PREFIX;
                $keyframesFactory = array($keyframesFactory, 'getMobile');
                break;

            case self::MODE_DESKTOP:
            default:
                $modeArgument     = AnimatedDOMElement::DESKTOP_PREFIX;
                $keyframesFactory = array($keyframesFactory, 'getDesktop');
                break;
        }

        return array(
            'media' => $mediaValue,
            'duration' => call_user_func(array($element, 'getAnimationDuration'), $modeArgument),
            'delay' => call_user_func(array($element, 'getAnimationDelay'), $modeArgument),
            'keyframes' => call_user_func($keyframesFactory),
        );
    }

    /**
     * @param string $id
     * @param array $switch
     * @return AnimationConfig
     */
    private static function createAnimationConfig($id, array $switch)
    {
        return new AnimationConfig(array(
            'id' => $id,
            'selector' => '.' . $id,
            'switch' => $switch,
        ));
    }

    /**
     * @param KeyframesFactoryInterface $keyframesFactory
     * @return int
     * @throws LogicException
     */
    private static function getModeForMobile(KeyframesFactoryInterface $keyframesFactory)
    {
        switch (get_class($keyframesFactory)) {
            case KeyframesFromAttributesFactory::class:
                return self::MODE_DESKTOP;

            case KeyframesFromScriptFactory::class:
                if ($keyframesFactory->getMobileScriptElement()) {
                    return self::MODE_MOBILE;
                } else {
                    return self::MODE_DESKTOP;
                }

            default:
                throw new LogicException('Unknown class type of interface ' . KeyframesFactoryInterface::class);
        }
    }
}
