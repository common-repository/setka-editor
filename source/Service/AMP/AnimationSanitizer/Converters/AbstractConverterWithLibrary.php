<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters;

abstract class AbstractConverterWithLibrary extends AbstractConverter
{
    /**
     * @var KeyframesFactoryLibrary
     */
    protected $keyframesLibrary;

    /**
     * @inheritdoc
     */
    public function __construct(\DOMDocument $dom, \DOMElement $rootElement, \DOMXPath $xpath)
    {
        parent::__construct($dom, $rootElement, $xpath);
        $this->keyframesLibrary = new KeyframesFactoryLibrary();
    }
}
