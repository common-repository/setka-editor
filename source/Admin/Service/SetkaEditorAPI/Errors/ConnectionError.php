<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;

use Setka\Editor\Plugin;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * Class ConnectionError
 */
class ConnectionError extends ConstraintViolation
{
    /**
     * ConnectionError constructor.
     *
     * @param array $parameters Array containing the necessary params for message template.
     *  $parameters = array(
     *      'error' => \WP_Error An instance of \WP_Error with http error from wp_remote_request()
     *  )
     */
    public function __construct(array $parameters)
    {
        /**
         * translators: %1$s - error code for example `http_request_failed`.
         * %2$s - error description from cURL on English language
         */
        $message = __('Your WordPress could not connect to an external server. The error description: %1$s %2$s', Plugin::NAME);

        /**
         * @var $parameters['error'] \WP_Error
         */
        $message = sprintf(
            $message,
            '<code>' . esc_html($parameters['error']->get_error_code()) . '</code>',
            esc_html($parameters['error']->get_error_message())
        );

        parent::__construct(
            $message,
            '',
            array(),
            null,
            null,
            null,
            null,
            Plugin::_NAME_ . '_setka_api_connection'
        );
    }
}
