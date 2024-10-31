<?php
namespace Setka\Editor\Admin\Service\Filesystem\Exceptions;

class MoveException extends Exception
{
    /**
     * MoveException constructor.
     *
     * @param string $source
     * @param string $destination
     */
    public function __construct($source, $destination)
    {
        parent::__construct('Can\'t move file from "' . $source . '" to "' . $destination . '"');
    }
}
