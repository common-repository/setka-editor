<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs\Exceptions;

use Setka\Editor\Exceptions\RuntimeException;

class ConnectionException extends RuntimeException
{
    /**
     * @var \WP_Error
     */
    private $error;

    public function __construct(\WP_Error $error)
    {
        $this->error = $error;
        parent::__construct();
    }

    public function getError(): \WP_Error
    {
        return $this->error;
    }
}
