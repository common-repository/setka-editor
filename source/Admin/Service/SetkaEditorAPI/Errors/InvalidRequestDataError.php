<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class InvalidRequestDataError
 */
class InvalidRequestDataError extends ConstraintViolation
{
    /**
     * InvalidRequestDataError constructor.
     */
    public function __construct()
    {
        $message = __('Invalid request data.', Plugin::NAME);

        parent::__construct(
            $message,
            '',
            array(),
            null,
            null,
            null,
            null,
            Plugin::_NAME_ . '_setka_api_invalid_request_data'
        );
    }
}
