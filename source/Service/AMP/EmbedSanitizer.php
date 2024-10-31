<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Service\AMP\Traits\XPathFactoryTrait;

class EmbedSanitizer extends \AMP_Base_Sanitizer
{
    use XPathFactoryTrait;

    /**
     * @var \DOMElement
     */
    protected $currentNode;

    /**
     * @inheritdoc
     */
    public function sanitize()
    {
        /**
         * @var $nodes \DOMNodeList
         * @var $node \DOMElement
         * @var $childNode \DOMElement
         */
        $this->setupXPath();
        $nodes = $this->xpath->query('//*[contains(@class, \'stk-code_keep-ratio\')]');

        foreach ($nodes as $node) {
            if (!is_a($node, \DOMElement::class)) {
                continue;
            }

            $this->removeAttributeFragment($node, 'class', 'stk-code_keep-ratio');

            $childNodes = $this->xpath->query('.//*[contains(@class, \'stk-code\')]', $node);
            foreach ($childNodes as $childNode) {
                if (!is_a($childNode, \DOMElement::class)) {
                    continue;
                }
                $childNode->removeAttribute('style');
            }

            $childNodes = $this->xpath->query('.//iframe', $node);
            foreach ($childNodes as $childNode) {
                if (!is_a($childNode, \DOMElement::class)) {
                    continue;
                }
                $childNode->setAttribute('layout', 'responsive');
            }
        }
    }

    /**
     * Remove string (value) from \DOMElement attribute.
     *
     * @param \DOMElement $node Node to operate with.
     * @param $attribute string Name of HTML attribute where $fragment will be searched.
     * @param $value string Value of HTML attribute which should be deleted.
     *
     * @return $this For chain calls.
     */
    public function removeAttributeFragment(\DOMElement $node, $attribute, $value)
    {
        $attributeValues = $node->getAttribute($attribute);
        $attributeValues = explode(' ', $attributeValues);
        $index           = array_search($value, $attributeValues, true);

        if (false !== $index) {
            unset($attributeValues[$index]);
            $node->setAttribute($attribute, implode(' ', $attributeValues));
        }

        return $this;
    }
}
