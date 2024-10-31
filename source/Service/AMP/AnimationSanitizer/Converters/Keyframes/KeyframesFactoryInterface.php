<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes;

interface KeyframesFactoryInterface
{
    /**
     * @return $this
     */
    public function build();

    /**
     * @return array
     */
    public function getDesktop();

    /**
     * @return array
     */
    public function getMobile();
}
