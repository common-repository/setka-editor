<?php
namespace Setka\Editor\API\V1\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

class SiteError extends ConstraintViolation
{
    public function __construct()
    {
        $code    = Plugin::_NAME_ . '_api_site_error';
        $message = __('Site is experiencing technical issues while handling request.', Plugin::NAME);

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
