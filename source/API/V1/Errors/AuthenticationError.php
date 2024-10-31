<?php
namespace Setka\Editor\API\V1\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

class AuthenticationError extends ConstraintViolation
{
    public function __construct()
    {
        $code    = Plugin::_NAME_ . '_api_authenticate_error';
        $message = __('Sorry. We can\'t authenticate this request.', Plugin::NAME);

        parent::__construct(
            $message,
            '',
            array(),
            null,
            null,
            null,
            null,
            $code
        );
    }
}
