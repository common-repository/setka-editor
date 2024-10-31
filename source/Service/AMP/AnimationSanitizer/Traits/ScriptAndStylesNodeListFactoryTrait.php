<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Traits;

use Setka\Editor\Service\AMP\AnimationSanitizer\AbstractConverter;
use Setka\Editor\Service\AMP\AnimationSanitizer\Exceptions\DomainException;

trait ScriptAndStylesNodeListFactoryTrait
{
    /**
     * @param $name string
     * @return \DOMNodeList
     */
    private function createScriptNodeList($name)
    {
        /**
         * @var $this AbstractConverter
         */
        return $this->xpath->query('//script[@data-anim-name="' . $name . '"]');
    }

    /**
     * @param $name string
     * @return \DOMNodeList
     */
    private function createStyleNodeList($name)
    {
        /**
         * @var $this AbstractConverter
         */
        return $this->xpath->query('//style[@data-anim-name="' . $name . '"]');
    }

    /**
     * @param \DOMNodeList $list
     *
     * @throws \DomainException
     * @return \DOMElement
     */
    private function getFirstNodeFromList(\DOMNodeList $list)
    {
        if (1 !== count($list)) {
            throw new \LengthException('List contains invalid amount of elements. Expected: 1. Actual: ' . count($list));
        }

        $element = $list->item(0);

        if (!is_a($element, \DOMElement::class)) {
            throw new DomainException($element);
        }

        return $element;
    }
}
