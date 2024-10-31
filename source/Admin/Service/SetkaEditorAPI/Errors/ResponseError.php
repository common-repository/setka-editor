<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ResponseError
 */
class ResponseError extends ConstraintViolation
{
    /**
     * ResponseError constructor.
     */
    public function __construct()
    {
        $code = Plugin::_NAME_ . '_setka_api_server_response';

        $message = sprintf(
            __('Oops... We could not load your Setka Editor plugin data. Error code: <code>%1$s</code>. Please contact Setka Editor support team <a href="mailto:support@tiny.cloud">support@tiny.cloud</a>.', Plugin::NAME),
            esc_html($code)
        );

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
