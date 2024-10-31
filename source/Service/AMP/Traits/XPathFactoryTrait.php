<?php
namespace Setka\Editor\Service\AMP\Traits;

trait XPathFactoryTrait
{
    /**
     * @var \DOMXPath
     */
    private $xpath;

    /**
     * @return \DOMXPath
     */
    private function createXPath()
    {
        return new \DOMXPath($this->dom);
    }

    /**
     * @return $this
     */
    private function setupXPath()
    {
        $this->xpath = $this->createXPath();
        return $this;
    }

    /**
     * @return \DOMNodeList|false
     */
    private function createSetkaPosts()
    {
        return $this->xpath->query('//div[contains(@class, "stk-post")]');
    }
}
