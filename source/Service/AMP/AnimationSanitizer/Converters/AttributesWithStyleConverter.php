<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters;

use Setka\Editor\Service\AMP\AnimationSanitizer\AnimatedDOMElement;
use Setka\Editor\Service\AMP\AnimationSanitizer\Traits\ScriptAndStylesNodeListFactoryTrait;

class AttributesWithStyleConverter extends AttributesFormatConverter
{
    use ScriptAndStylesNodeListFactoryTrait;

    /**
     * Return list contains \DOMElement-s with data-anim=true.
     * @return \DOMElement[]
     */
    protected function createNodesList()
    {
        $found = array();
        $nodes = $this->createNodeListWithAnimations();

        foreach ($nodes as $element) {
            $animatedElement = new AnimatedDOMElement($element);
            try {
                // <style> element should exists.
                $this->getFirstNodeFromList($this->createStyleNodeList($animatedElement->getName()));
            } catch (\Exception $exception) {
                continue;
            }

            try {
                // <script> element should NOT exists.
                $this->getFirstNodeFromList($this->createScriptNodeList($animatedElement->getName()));
            } catch (\Exception $exception) {
                $found[] = $element;
            }
        }

        return $found;
    }

    /**
     * @inheritdoc
     */
    protected function cleanup()
    {
        $this->removeNodeStyle();
        return parent::cleanup();
    }

    private function removeNodeStyle()
    {
        $node = $this->getFirstNodeFromList($this->createStyleNodeList($this->animatedElement->getName()));
        $node->parentNode->removeChild($node);
    }

    /**
     * @inheritdoc
     */
    protected function getUniqueClassFragment()
    {
        return '2';
    }
}
