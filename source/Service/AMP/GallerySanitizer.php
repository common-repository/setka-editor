<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Service\AMP\Traits\XPathFactoryTrait;

class GallerySanitizer extends \AMP_Base_Sanitizer
{
    use XPathFactoryTrait;

    /**
     * @inheritdoc
     */
    public function sanitize()
    {
        /**
         * @var $nodes \DOMNodeList
         */
        $this->setupXPath();
        $nodes = $this->xpath->query('//div[contains(@class, "stk-gallery")]');

        /**
         * @var $node \DOMElement
         * @var $child \DOMElement
         */
        foreach ($nodes as $node) {
            $maxWidth  = 600;
            $maxHeight = 400;
            $newNode   = $this->dom->createElement('amp-carousel');

            $newNode->setAttribute('layout', 'responsive');
            $newNode->setAttribute('type', 'slides');

            foreach ($node->childNodes as $child) {
                // Transfer (copy) all DOMElement nodes into refactored DOMElement
                if (!is_a($child, \DOMElement::class)) {
                    continue;
                }

                $newChild = $child->cloneNode(true);
                $image    = $this->xpath->query('.//amp-img', $newChild)->item(0);

                if ($image) {
                    $imageWidth  = $image->getAttribute('width');
                    $imageHeight = $image->getAttribute('height');
                    $image->setAttribute('layout', 'responsive');

                    if ($maxWidth < $imageWidth) {
                        $maxWidth = $imageWidth;
                    }
                    if ($maxHeight < $imageHeight) {
                        $maxHeight = $imageHeight;
                    }
                }
                $newNode->appendChild($newChild);
            }

            $newNode->setAttribute('width', $maxWidth);
            $newNode->setAttribute('height', $maxHeight);

            $node->parentNode->replaceChild($newNode, $node);
        }
    }
}
