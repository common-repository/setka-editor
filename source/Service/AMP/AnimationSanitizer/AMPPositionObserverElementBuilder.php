<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer;

class AMPPositionObserverElementBuilder
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $class;

    /**
     * @var AnimatedDOMElement
     */
    private $animatedElement;

    /**
     * @var array
     */
    private $attributeCallbacks = array(
        'on' => 'buildAttributeOn',
        'intersection-ratios' => 'buildAttributeIntersectionRatios',
        'viewport-margins' => 'buildAttributeViewportMargins',
        'layout' => 'buildAttributeLayout',
        'target' => 'buildAttributeTarget',
    );

    /**
     * AMPPositionObserverElementBuilder constructor.
     *
     * @param string $id
     * @param string $class
     * @param AnimatedDOMElement $animatedElement
     */
    public function __construct($id, $class, AnimatedDOMElement $animatedElement)
    {
        $this->id              = $id;
        $this->class           = $class;
        $this->animatedElement = $animatedElement;
    }

    public function build(\DOMElement $ampPositionObserverElement)
    {
        $attributes = $this->buildAttributes();

        foreach ($attributes as $name => $value) {
            $ampPositionObserverElement->setAttribute($name, $value);
        }
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function buildAttributes(array $attributes = array())
    {
        foreach ($this->attributeCallbacks as $name => $callback) {
            $value = call_user_func(array($this, $callback));

            if (is_null($value)) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            if (is_scalar($value)) {
                $attributes[$name] = $value;
                continue;
            }

            throw new \LogicException();
        }

        return $attributes;
    }

    /**
     * @return string
     */
    private function buildAttributeOn()
    {
        if ($this->animatedElement->isTriggersByScroll()) {
            return 'scroll:' . $this->class . '.seekTo(percent=event.percent);';
        } else {
            return 'enter:' . $this->class . '.start;';
        }
    }

    /**
     * @return array
     */
    private function buildAttributeIntersectionRatios()
    {
        if ($this->animatedElement->isTriggersByScroll()) {
            return array(
                $this->animatedElement->getAnimationScrollFinish() / 100,
                0
            );
        } else {
            return array(0, .1);
        }
    }

    /**
     * @return string
     */
    private function buildAttributeLayout()
    {
        return 'nodisplay';
    }

    /**
     * @return string
     */
    private function buildAttributeTarget()
    {
        return $this->id;
    }

    /**
     * @return array|null
     */
    private function buildAttributeViewportMargins()
    {
        if (!$this->animatedElement->isTriggersByScroll()) {
            return null;
        }

        $units = 'vh';

        return array(
            (100 - $this->animatedElement->getAnimationScrollFinish()) . $units,
            $this->animatedElement->getAnimationScrollStart() . $units,
        );
    }
}
