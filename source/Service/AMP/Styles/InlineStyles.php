<?php
namespace Setka\Editor\Service\AMP\Styles;

use Setka\Editor\Exceptions\DomainException;
use Setka\Editor\Exceptions\Exception;

class InlineStyles
{
    /**
     * @var string
     */
    private $styles;

    /**
     * @var array
     */
    private $properties;

    /**
     * @param string $styles
     * @throws Exception
     */
    public function __construct($styles)
    {
        $this->styles = $styles;
        $this->decode();
    }

    /**
     * @throws Exception
     */
    private function decode()
    {
        if (empty($this->styles)) {
            throw new Exception();
        }

        $properties = explode(';', $this->styles);

        foreach ($properties as $part) {
            $part = trim($part);

            if (empty($part)) {
                continue;
            }

            $separatorPosition = strpos($part, ':');

            if ($separatorPosition >= 1) {
                $propertyValue = trim(substr($part, $separatorPosition + 1));
                $propertyName  = trim(substr($part, 0, $separatorPosition));

                if (empty($propertyName) || empty($propertyValue)) {
                    throw new Exception();
                }

                $this->properties[$propertyName] = $propertyValue;
            } else {
                throw new Exception();
            }
        }

        if (empty($this->properties)) {
            throw new Exception();
        }
    }

    /**
     * @return string
     */
    public function encode()
    {
        $result = '';
        foreach ($this->properties as $property => &$value) {
            $result .= $property . ':' . $value . ';';
        }
        return $result;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        unset($this->properties[$key]);
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function add($key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exist($key)
    {
        return isset($this->properties[$key]);
    }

    /**
     * @param string $key
     *
     * @return string
     *
     * @throws DomainException
     */
    public function get($key)
    {
        if ($this->exist($key)) {
            return $this->properties[$key];
        }
        throw new DomainException();
    }
}
