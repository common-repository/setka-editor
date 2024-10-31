<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Exceptions\DomainException;
use Setka\Editor\Exceptions\Exception;
use Setka\Editor\Service\AMP\Styles\InlineStyles;
use Setka\Editor\Service\AMP\Traits\XPathFactoryTrait;

class BackgroundSanitizer extends \AMP_Base_Sanitizer
{
    use XPathFactoryTrait;

    /**
     * @var \DOMNodeList
     */
    private $post;

    /**
     * @var \DOMElement
     */
    private $element;

    /**
     * @var InlineStyles
     */
    private $styles;

    /**
     * @var array
     */
    private $propertiesToRemove = array(
        'background-clip',
        'background-image',
        'background-origin',
        'background-position',
        'background-repeat',
        'background-size',
        'background-attachment',
    );

    const BACKGROUND = 'background';

    /**
     * @inheritdoc
     */
    public function sanitize()
    {
        $posts = $this->setupXPath()->createSetkaPosts();

        if (empty($posts)) {
            return;
        }

        foreach ($posts as $this->post) {
            $nodes = $this->createNodesList();

            if (empty($nodes)) {
                return;
            }

            foreach ($nodes as $this->element) {
                try {
                    $this->handleElement();
                } catch (\Exception $exception) {
                    continue;
                }
            }
        }
    }

    /**
     * @return \DOMNodeList|false
     */
    private function createNodesList()
    {
        return $this->xpath->query('//*[contains(@class, \'stk-mobile_no-bg\')]', $this->post);
    }

    /**
     * @throws Exception
     */
    private function handleElement()
    {
        $this->styles = new InlineStyles($this->element->getAttribute('style'));

        $this->cleanProperties();

        try {
            $this->cleanBackground();
        } catch (\Exception $exception) {
            // Property may not exists, then skip.
        }

        $result = $this->styles->encode();

        if ($result) {
            $this->element->setAttribute('style', $result);
        } else {
            $this->element->removeAttribute('style');
        }
    }

    private function cleanProperties()
    {
        foreach ($this->propertiesToRemove as $property) {
            $this->styles->delete($property);
        }
    }

    /**
     * @throws DomainException
     */
    private function cleanBackground()
    {
        $this->updateOrDeleteProperty(
            self::BACKGROUND,
            trim(preg_replace(
                '/url\s*\(.+\)\s*/mU',
                '',
                $this->styles->get(self::BACKGROUND)
            ))
        );
    }

    /**
     * @param string $key
     * @param string|null $value
     */
    private function updateOrDeleteProperty($key, $value)
    {
        if (empty($value)) {
            $this->styles->delete($value);
        } else {
            $this->styles->add($key, $value);
        }
    }
}
