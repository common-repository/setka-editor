<?php
namespace Setka\Editor\Service\AMP\AnimationSanitizer\Exceptions;

class DomainException extends \DomainException
{
    public function __construct($element)
    {
        parent::__construct($this->buildMessage($element));
    }

    /**
     * @param $element mixed
     * @return string
     */
    private function buildMessage($element)
    {
        $message = 'Element in list has wrong format. Expected: object \DOMElement, Actual: ';

        $type = gettype($element);

        if ('object' === $type) {
            $message .= get_class($element);
        } else {
            $message .= $type;
        }

        return $message;
    }
}
