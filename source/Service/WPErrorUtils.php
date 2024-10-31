<?php
namespace Setka\Editor\Service;

class WPErrorUtils
{

    public static function hasErrors(\WP_Error $errors)
    {
        if (is_wp_error($errors)) {
            if (count($errors->errors) !== 0) {
                return true;
            }
        }
        return false;
    }

    public static function mergeWPErrors(\WP_Error $addTo, \WP_Error $addFrom)
    {
        if (!empty($addFrom->errors)) {
            foreach ($addFrom->errors as $errorCode => $errorMessage) {
                $addTo->add(
                    $errorCode,
                    $addFrom->get_error_message($errorCode),
                    $addFrom->get_error_data($errorCode)
                );
            }
        }
    }
}
