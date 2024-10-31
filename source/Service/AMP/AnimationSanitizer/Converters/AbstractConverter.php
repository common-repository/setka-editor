<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters;

use Setka\Editor\Service\AMP\AnimationSanitizer\AMPPositionObserverElementBuilder;
use Setka\Editor\Service\AMP\AnimationSanitizer\AnimatedDOMElement;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\AnimationConfig\AnimationConfigFactory;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\AnimationConfig\AnimationConfigInterface;
use Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes\KeyframesFactoryInterface;
use Setka\Editor\Service\AMP\AnimationSanitizer\InitialStyles;
use Setka\Editor\Service\AMP\SanitizerExceptions\NoModificationsException;

abstract class AbstractConverter implements ConverterInterface
{
    /**
     * @var \DOMDocument
     */
    protected $dom;

    /**
     * @var \DOMElement
     */
    protected $rootElement;

    /**
     * @var \DOMXPath
     */
    protected $xpath;

    /**
     * @var integer Current animated element index.
     */
    protected $index;

    /**
     * @var \DOMElement Current animated element (tag with data-anim=true).
     */
    protected $element;

    /**
     * @var AnimatedDOMElement Wrapper for $this->element.
     */
    protected $animatedElement;

    /**
     * @var InitialStyles
     */
    protected $initialStyles;

    /**
     * @var AnimationConfigInterface Current animation config.
     */
    protected $config;

    /**
     * @var array HTML attributes should be removed.
     */
    protected $attributesToRemove = array(
        'data-anim-delay',
        'data-anim-direction',
        'data-anim-duration',
        'data-anim-loop',
        'data-anim-m-delay',
        'data-anim-m-direction',
        'data-anim-m-duration',
        'data-anim-m-loop',
        'data-anim-m-opacity',
        'data-anim-m-rotation',
        'data-anim-m-scroll-finish',
        'data-anim-m-scroll-start',
        'data-anim-m-shift',
        'data-anim-m-sticky-direction',
        'data-anim-m-sticky-offset',
        'data-anim-m-trigger',
        'data-anim-m-zoom',
        'data-anim-opacity',
        'data-anim-rotation',
        'data-anim-scroll-finish',
        'data-anim-scroll-start',
        'data-anim-shift',
        'data-anim-sticky-direction',
        'data-anim-sticky-offset',
        'data-anim-trigger',
        'data-anim-zoom',
    );

    /**
     * AbstractConverter constructor.
     *
     * @param \DOMDocument $dom
     * @param \DOMElement $rootElement
     * @param \DOMXPath $xpath
     */
    public function __construct(\DOMDocument $dom, \DOMElement $rootElement, \DOMXPath $xpath)
    {
        $this->dom         = $dom;
        $this->rootElement = $rootElement;
        $this->xpath       = $xpath;
    }

    /**
     * @inheritdoc
     */
    public function convert()
    {
        $nodes = $this->createNodesList();

        if (count($nodes) === 0) {
            throw new NoModificationsException();
        }

        foreach ($nodes as $this->index => $this->element) {
            $this->stepInLoop();
        }

        $this->cleanupCommon();
    }

    /**
     * @return \DOMNodeList
     */
    protected function createNodeListWithAnimations()
    {
        return $this->xpath->query('//*[@data-anim="true"]');
    }

    /**
     * Return list contains \DOMElement-s with data-anim=true.
     * @return \DOMElement[]
     */
    abstract protected function createNodesList();

    /**
     * @throws \Exception
     */
    protected function stepInLoop()
    {
        $this->convertAnimation()->cleanup();
    }

    /**
     * @throws \Exception
     * @return $this
     */
    protected function convertAnimation()
    {
        $this->animatedElement = $this->createAnimatedDOMElement();
        $this->initialStyles   = $this->createInitialStyles()->generateStyles();
        $this->config          = $this->createAnimationConfig();

        $this->updateCSSClasses();

        $this->element->setAttribute('id', $this->createNodeId());

        try {
            $this->appendToRoot($this->createAdditionalElements());
        } catch (\Exception $exception) {
            // Silently skip this animation element.
        }

        return $this;
    }

    /**
     * @return AnimatedDOMElement
     */
    private function createAnimatedDOMElement()
    {
        return new AnimatedDOMElement($this->element);
    }

    /**
     * @return InitialStyles
     */
    private function createInitialStyles()
    {
        return new InitialStyles($this->animatedElement, $this->generateUniqueClassForAnimation());
    }

    /**
     * @return \DOMElement[]
     */
    protected function createAdditionalElements()
    {
        return array(
            $this->createAndBuildPositionObserver(),
            $this->createAnimationElement(),
            $this->createAnimationStylesElement()
        );
    }

    /**
     * @param \DOMElement[] $elements
     */
    private function appendToRoot(array $elements)
    {
        foreach ($elements as $element) {
            $this->rootElement->appendChild($element);
        }
    }

    /**
     * @throws \Exception
     * @return AnimationConfigInterface Animation config.
     */
    protected function createAnimationConfig()
    {
        return AnimationConfigFactory::createKeyframesFromAttributes(
            $this->generateUniqueClassForAnimation(),
            $this->animatedElement,
            $this->createKeyframesFactory()
        );
    }

    /**
     * @throws \Exception
     * @return KeyframesFactoryInterface
     */
    abstract protected function createKeyframesFactory();

    /**
     * Update CSS classes for current node.
     * @return $this
     */
    protected function updateCSSClasses()
    {
        $classes  = $this->element->getAttribute('class');
        $classes .= ' stk-anim ' . $this->config->getID();
        $this->element->setAttribute('class', $classes);
        return $this;
    }

    /**
     * @return string Unique id value for HTML attribute.
     */
    protected function createNodeId()
    {
        if ($this->element->hasAttribute('id')) {
            return $this->element->getAttribute('id');
        } else {
            return 'target-' . $this->config->getID();
        }
    }

    /**
     * @throws \RuntimeException If element was not created.
     * @return \DOMElement
     */
    protected function createAndBuildPositionObserver()
    {
        if (!$this->config) {
            throw new \RuntimeException();
        }

        $builder = new AMPPositionObserverElementBuilder($this->createNodeId(), $this->config->getID(), $this->animatedElement);
        $element = $this->dom->createElement('amp-position-observer');

        $builder->build($element);

        return $element;
    }

    /**
     * @throws \RuntimeException If element was not created.
     * @return \DOMElement
     */
    protected function createAnimationElement()
    {
        if (!$this->config) {
            throw new \RuntimeException();
        }

        $config = array(
            'fill' => 'both',
            'easing' => 'ease',
            'iterations' => 1,
            'animations' => array($this->config->asArray()),
        );

        $json = wp_json_encode($config);

        if (!is_string($json)) {
            throw new \RuntimeException();
        }

        $node = $this->dom->createElement('amp-animation');
        $node->setAttribute('id', $this->config->getID());
        $node->setAttribute('layout', 'nodisplay');

        $script = $this->dom->createElement('script');
        $script->setAttribute('type', 'application/json');
        $script->textContent = $json;

        $node->appendChild($script);

        return $node;
    }

    /**
     * Generates <style> element for single animation.
     *
     * @throws \RuntimeException
     * @throws \LogicException
     * @return \DOMElement
     */
    protected function createAnimationStylesElement()
    {
        if (!$this->config) {
            throw new \RuntimeException();
        }

        $element = $this->createStyleElement();

        $element->textContent = $this->initialStyles->generateCSS();

        return $element;
    }

    /**
     * @return \DOMElement
     */
    private function createStyleElement()
    {
        $element = $this->dom->createElement('style');
        $element->setAttribute('type', 'text/css');
        return $element;
    }

    /**
     * @return $this
     */
    protected function cleanup()
    {
        $this->removeAttributes();
        return $this;
    }

    /**
     * @return $this
     */
    protected function removeAttributes()
    {
        foreach ($this->attributesToRemove as $attribute) {
            $this->element->removeAttribute($attribute);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function cleanupCommon()
    {
        return $this;
    }

    /**
     * Generates unique CSS class for element.
     *
     * @return string Unique CSS class for element.
     */
    protected function generateUniqueClassForAnimation()
    {
        return 'stk-anim-' . $this->getUniqueClassFragment() . absint($this->index);
    }

    /**
     * @return string
     */
    abstract protected function getUniqueClassFragment();
}
