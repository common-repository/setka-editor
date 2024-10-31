<?php
namespace Setka\Editor\API\V1\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

class RequestDataError extends ConstraintViolation
{
    public function __construct()
    {
        $code    = Plugin::_NAME_ . '_api_request_data_error';
        $message = __('Invalid request data. The Plugin can\'t validate the server request.', Plugin::NAME);

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
