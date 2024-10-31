<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer;

use Setka\Editor\Exceptions\DomainException;

class AnimatedDOMElement implements AnimatedDOMElementInterface
{
    /**
     * @var \DOMElement
     */
    private $element;

    /**
     * AnimatedDOMElement constructor.
     *
     * @param \DOMElement $element
     */
    public function __construct(\DOMElement $element)
    {
        $this->element = $element;
    }

    /**
     * @inheritDoc
     */
    public function getAllPrefixes()
    {
        return array(self::MOBILE_PREFIX, self::DESKTOP_PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function hasMobileAnimationAttribute()
    {
        return $this->element->hasAttribute(self::MOBILE_PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function isAnimationMobile()
    {
        return 'true' === $this->element->getAttribute(self::MOBILE_PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function isAnimationDesktop()
    {
        return 'true' === $this->element->getAttribute(self::DESKTOP_PREFIX);
    }

    /**
     * @inheritDoc
     */
    public function getName($prefix = self::DESKTOP_PREFIX)
    {
        $value = $this->element->getAttribute($prefix . '-name');

        if (!$value) {
            throw new DomainException();
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function getNames()
    {
        $names = array();
        $valid = false;

        foreach ($this->getAllPrefixes() as $prefix) {
            try {
                $names[] = $this->getName($prefix);
                $valid   = true;
            } catch (\Exception $exception) {
                $names[] = null;
            }
        }

        if ($valid) {
            return $names;
        }

        throw new DomainException();
    }

    /**
     * @inheritdoc
     */
    public function getOpacity()
    {
        return (float) $this->element->getAttribute('data-anim-opacity') / 100;
    }

    /**
     * @inheritdoc
     */
    public function getRotate()
    {
        return (float) $this->element->getAttribute('data-anim-rotation') . 'deg';
    }

    /**
     * @inheritdoc
     */
    public function getTranslate()
    {
        $direction = $this->element->getAttribute('data-anim-direction');
        $shift     = (int) $this->element->getAttribute('data-anim-shift');

        if ('bottom' === $direction || 'right' === $direction) {
            $shift = $shift * -1;
        }

        $shift = (string) $shift . 'px';
        $zero  = '0px';

        if ('top' === $direction || 'bottom' === $direction) {
            $translate = array($zero, $shift);
        } else {
            $translate = array($shift, $zero);
        }

        return $translate;
    }

    /**
     * @inheritdoc
     */
    public function getScale()
    {
        return (float) $this->element->getAttribute('data-anim-zoom') / 100;
    }

    /**
     * @inheritdoc
     */
    public function getAnimationTrigger()
    {
        return $this->element->getAttribute('data-anim-trigger');
    }

    /**
     * @return bool
     */
    public function isTriggersByScroll()
    {
        return 'whenScrolling' === $this->getAnimationTrigger();
    }

    /**
     * @inheritdoc
     */
    public function getAnimationScrollStart()
    {
        return (int) $this->element->getAttribute('data-anim-scroll-start');
    }

    /**
     * @inheritdoc
     */
    public function getAnimationScrollFinish()
    {
        return (int) $this->element->getAttribute('data-anim-scroll-finish');
    }

    /**
     * @inheritdoc
     */
    public function getAnimationDuration($prefix = self::DESKTOP_PREFIX)
    {
        if ($this->isTriggersByScroll()) {
            return 1000.0;
        }
        return (float) $this->element->getAttribute($prefix . '-duration') * 1000;
    }

    /**
     * @inheritdoc
     */
    public function getAnimationDelay($prefix = self::DESKTOP_PREFIX)
    {
        if ($this->isTriggersByScroll()) {
            return 0.0;
        }
        return (float) $this->element->getAttribute($prefix . '-delay') * 1000;
    }
}
