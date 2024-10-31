<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Converters\Keyframes;

use Setka\Editor\Exceptions\DomainException;

class KeyframesFromScriptFactory implements KeyframesFactoryInterface
{
    /**
     * @var \DOMElement|null
     */
    private $mobileScriptElement;

    /**
     * @var \DOMElement|null
     */
    private $desktopScriptElement;

    /**
     * @var array
     */
    private $mobileKeyframes;

    /**
     * @var array
     */
    private $desktopKeyframes;

    /**
     * KeyframesFromScriptFactory constructor.
     * @param \DOMElement|null $mobileScriptElement
     * @param \DOMElement|null $desktopScriptElement
     */
    public function __construct(\DOMElement $mobileScriptElement = null, \DOMElement $desktopScriptElement = null)
    {
        $this->mobileScriptElement  = $mobileScriptElement;
        $this->desktopScriptElement = $desktopScriptElement;
    }

    /**
     * @inheritDoc
     */
    public function build()
    {
        if ($this->mobileScriptElement) {
            $this->mobileKeyframes = $this->decodeKeyframes($this->mobileScriptElement);
        }

        if ($this->desktopScriptElement) {
            $this->desktopKeyframes = $this->decodeKeyframes($this->desktopScriptElement);
        }

        return $this;
    }

    /**
     * @param \DOMElement $script
     * @throws DomainException
     * @return array
     */
    private function decodeKeyframes(\DOMElement $script)
    {
        $config = json_decode($script->textContent, true);

        if (!is_array($config)) {
            throw new DomainException(json_last_error_msg());
        }

        if (!isset($config['keyframes'])) {
            throw new DomainException('No keyframes in configuration.');
        }

        return $config['keyframes'];
    }

    /**
     * @inheritDoc
     */
    public function getDesktop()
    {
        return $this->desktopKeyframes;
    }

    /**
     * @inheritDoc
     */
    public function getMobile()
    {
        return $this->mobileKeyframes;
    }

    /**
     * @return \DOMElement|null
     */
    public function getMobileScriptElement()
    {
        return $this->mobileScriptElement;
    }

    /**
     * @return \DOMElement|null
     */
    public function getDesktopScriptElement()
    {
        return $this->desktopScriptElement;
    }
}
