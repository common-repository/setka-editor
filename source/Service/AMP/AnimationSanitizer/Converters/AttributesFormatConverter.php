<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters;

use Setka\Editor\Service\AMP\AnimationSanitizer\AnimatedDOMElement;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFromAttributesFactory;

class AttributesFormatConverter extends AbstractConverter implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    protected function createNodesList()
    {
        $found = array();
        $nodes = $this->createNodeListWithAnimations();

        foreach ($nodes as $element) {
            $animatedElement = new AnimatedDOMElement($element);
            try {
                $animatedElement->getName();
            } catch (\Exception $exception) {
                $found[] = $element;
            }
        }

        return $found;
    }

    /**
     * @inheritdoc
     */
    protected function createKeyframesFactory()
    {
        return new KeyframesFromAttributesFactory($this->initialStyles);
    }

    /**
     * @inheritdoc
     */
    protected function getUniqueClassFragment()
    {
        return '1';
    }
}
