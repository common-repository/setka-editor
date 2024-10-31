<?php
namespace Setka\Editor\Service\AMP\SanitizerExceptions;

class NoModificationsException extends \UnexpectedValueException
{
    public function __construct()
    {
        parent::__construct('No modifications during execution.');
    }
}
