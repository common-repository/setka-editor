<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class InternalServerError
 */
class InternalServerError extends ConstraintViolation
{
    /**
     * InternalServerError constructor.
     */
    public function __construct()
    {
        $message = __('Setka Editor Server is experiencing some technical issues. Please try again in a couple of minutes.', Plugin::NAME);

        parent::__construct(
            $message,
            '',
            array(),
            null,
            null,
            null,
            null,
            Plugin::_NAME_ . '_setka_api_internal_server_error'
        );
    }
}
