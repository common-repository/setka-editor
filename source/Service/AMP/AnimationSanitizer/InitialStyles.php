<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer;

class InitialStyles
{
    const TRANSFORM = '--stk-transform';

    const OPACITY = '--stk-opacity';

    const TRANSFORM_TRANSLATE = 'translate';

    const TRANSFORM_ROTATE = 'rotate';

    const TRANSFORM_SCALE = 'scale';

    const PROPERTY_TYPE_PARENT = 'parent';

    const PROPERTY_TYPE_PROP = 'prop';

    const PROPERTY_KEYS_TYPE = 0;

    const PROPERTY_KEYS_CALLBACK = 1;

    const PROPERTY_KEYS_DEFAULT = 2;

    /**
     * @var array
     */
    private $properties = array(
        self::TRANSFORM => array(
            self::PROPERTY_TYPE_PARENT,
            array(
                self::TRANSFORM_TRANSLATE => array(
                    self::PROPERTY_TYPE_PROP,
                    'getTranslate',
                    array('0px', '0px')
                ),
                self::TRANSFORM_ROTATE => array(
                    self::PROPERTY_TYPE_PROP,
                    'getRotate',
                    '0deg',
                ),
                self::TRANSFORM_SCALE => array(
                    self::PROPERTY_TYPE_PROP,
                    'getScale',
                    1.0,
                ),
            ),
            array(),
        ),
        self::OPACITY => array(
            self::PROPERTY_TYPE_PROP,
            'getOpacity',
            1.0,
        ),
    );

    /**
     * @var AnimatedDOMElementInterface
     */
    private $element;

    /**
     * @var string
     */
    private $uniqueClass;

    /**
     * @var array
     */
    private $styles = array();

    /**
     * @param AnimatedDOMElementInterface $element
     * @param string $uniqueClass
     */
    public function __construct(AnimatedDOMElementInterface $element, $uniqueClass)
    {
        $this->element     = $element;
        $this->uniqueClass = $uniqueClass;
    }

    /**
     * @return $this
     */
    public function generateStyles()
    {
        $this->generateStylesStep(
            $this->properties,
            $this->styles
        );

        return $this;
    }

    private function generateStylesStep(array &$properties, array &$values)
    {
        foreach ($properties as $index => &$node) {
            if (self::PROPERTY_TYPE_PARENT === $node[self::PROPERTY_KEYS_TYPE]) {
                $value = array();
                $this->generateStylesStep($node[self::PROPERTY_KEYS_CALLBACK], $value);
            } else {
                $value = call_user_func(array($this->element, $node[self::PROPERTY_KEYS_CALLBACK]));
            }

            if ($value !== $node[self::PROPERTY_KEYS_DEFAULT]) {
                $values[$index] = $value;
            }
        }
    }

    /**
     * @throws \LogicException
     * @return string
     */
    public function generateCSS()
    {
        if (!$this->uniqueClass) {
            throw new \LogicException();
        }

        return sprintf('.stk-anim.%s {%s}', $this->uniqueClass, $this->generateProperties());
    }

    /**
     * @return string
     */
    public function getTransform()
    {
        return isset($this->styles[self::TRANSFORM]) ? $this->generateProperty($this->styles[self::TRANSFORM]) : 'none';
    }

    /**
     * @return float
     */
    public function getOpacity()
    {
        return isset($this->styles[self::OPACITY]) ? $this->styles[self::OPACITY] : $this->properties[self::OPACITY][self::PROPERTY_KEYS_DEFAULT];
    }

    /**
     * @return string
     */
    private function generateProperties()
    {
        $css = '';

        foreach ($this->styles as $property => &$value) {
            $css .= $property . ':' . (is_array($value) ? $this->generateProperty($value) : $value) . ';';
        }

        return $css;
    }

    /**
     * @param $value array
     * @return string
     */
    private function generateProperty(array &$value)
    {
        $state = array();

        foreach ($value as $type => $modification) {
            if (is_array($modification)) {
                $modification = implode(',', $modification);
            }

            $state[] = $type . '(' . $modification . ')';
        }

        return implode(' ', $state);
    }
}
