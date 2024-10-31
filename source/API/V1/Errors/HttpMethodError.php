<?php
namespace Setka\Editor\API\V1\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

class HttpMethodError extends ConstraintViolation
{
    public function __construct()
    {
        $code    = Plugin::_NAME_ . '_api_http_method_error';
        $message = __('Invalid HTTP method. The plugin not allows this types of requests.', Plugin::NAME);

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
