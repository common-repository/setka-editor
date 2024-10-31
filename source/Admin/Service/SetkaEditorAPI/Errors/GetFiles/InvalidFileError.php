<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Errors\GetFiles;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class InvalidFileError
 */
class InvalidFileError extends ConstraintViolation
{
    /**
     * InvalidFileError constructor.
     */
    public function __construct()
    {
        $message = __('One of file objects is invalid.', Plugin::NAME);

        parent::__construct(
            $message,
            '',
            array(),
            null,
            null,
            null,
            null,
            Plugin::_NAME_ . '_setka_api_get_files_invalid_file_error'
        );
    }
}
