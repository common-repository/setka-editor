<?php
namespace Setka\Editor\Service\Manager\Exceptions;

use Setka\Editor\Exceptions\RuntimeException;

class EarlyFinishException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Manager finished the execution because the next steps cannot be performed (or not required) for current system configuration.');
    }
}
