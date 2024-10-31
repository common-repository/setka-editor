<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer;

use Setka\Editor\Exceptions\DomainException;

interface AnimatedDOMElementInterface
{
    const MOBILE_PREFIX = 'data-anim-m';

    const DESKTOP_PREFIX = 'data-anim';

    /**
     * @return array
     */
    public function getAllPrefixes();

    /**
     * @return bool
     */
    public function hasMobileAnimationAttribute();

    /**
     * @return bool
     */
    public function isAnimationMobile();

    /**
     * @return bool
     */
    public function isAnimationDesktop();

    /**
     * @param string $prefix
     * @return string
     * @throws DomainException
     */
    public function getName($prefix = self::DESKTOP_PREFIX);

    /**
     * @return array
     * @throws DomainException
     */
    public function getNames();

    /**
     * @return float|int
     */
    public function getOpacity();

    /**
     * @return string
     */
    public function getRotate();

    /**
     * @return array
     */
    public function getTranslate();

    /**
     * @return float|int
     */
    public function getScale();

    /**
     * @return string
     */
    public function getAnimationTrigger();

    /**
     * @return integer
     */
    public function getAnimationScrollStart();

    /**
     * @return integer
     */
    public function getAnimationScrollFinish();

    /**
     * @param string $prefix
     * @return float
     */
    public function getAnimationDuration($prefix = self::DESKTOP_PREFIX);

    /**
     * @param string $prefix
     * @return float
     */
    public function getAnimationDelay($prefix = self::DESKTOP_PREFIX);
}
