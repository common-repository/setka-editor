<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters;

use Setka\Editor\Service\AMP\AnimationSanitizer\AnimatedDOMElement;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\AnimationConfig\AnimationConfigFactory;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFromScriptFactory;
use Setka\Editor\Service\AMP\AnimationSanitizer\Traits\ScriptAndStylesNodeListFactoryTrait;

class WAAPIConverter extends AbstractConverterWithLibrary
{
    use ScriptAndStylesNodeListFactoryTrait;

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
                foreach ($animatedElement->getNames() as $name) {
                    if ($name) {
                        // <script> element should exists.
                        $this->getNodeScript($name);
                    }
                }
                $found[] = $element;
            } catch (\Exception $exception) {
                continue;
            }
        }

        return $found;
    }

    /**
     * @return \DOMNodeList
     */
    protected function createNodeListWithAnimations()
    {
        return $this->xpath->query('//*[@data-anim="true"]|//*[@data-anim-m="true"]');
    }

    /**
     * @return \DOMElement[]
     */
    protected function createAdditionalElements()
    {
        return array(
            $this->createAndBuildPositionObserver(),
            $this->createAnimationElement(),
        );
    }

    /**
     * @inheritDoc
     */
    protected function createAnimationConfig()
    {
        return AnimationConfigFactory::createKeyframesFromScript(
            $this->generateUniqueClassForAnimation(),
            $this->animatedElement,
            $this->createKeyframesFactory()
        );
    }

    /**
     * @inheritdoc
     */
    protected function createKeyframesFactory()
    {
        $names = $this->animatedElement->getNames();

        try {
            return $this->keyframesLibrary->get($names);
        } catch (\Exception $exception) {
            $scripts = array();
            foreach ($names as $name) {
                $scripts[] = ($name) ? $this->getNodeScript($name) : null;
            }
            $keyframes = new KeyframesFromScriptFactory($scripts[0], $scripts[1]);
            $this->keyframesLibrary->add($names, $keyframes);
            return $keyframes;
        }
    }

    /**
     * @inheritdoc
     */
    protected function cleanupCommon()
    {
        foreach ($this->keyframesLibrary->scripts() as $script) {
            if (is_a($script->parentNode, \DOMNode::class)) {
                $script->parentNode->removeChild($script);
            }
        }
        return parent::cleanupCommon();
    }

    /**
     * @param string $name
     * @throws \DomainException
     * @return \DOMElement
     */
    private function getNodeScript($name)
    {
        return $this->getFirstNodeFromList($this->createScriptNodeList($name));
    }

    /**
     * @inheritdoc
     */
    protected function getUniqueClassFragment()
    {
        return '3';
    }
}
