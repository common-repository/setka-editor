<?php
namespace Setka\Editor\Service\Manager;

use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;

interface StageInterface
{
    /**
     * @throws OutOfTimeException
     * @throws \Exception
     */
    public function run();
}
