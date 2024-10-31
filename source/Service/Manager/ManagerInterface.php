<?php
namespace Setka\Editor\Service\Manager;

use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Service\Manager\Exceptions\EarlyFinishException;

interface ManagerInterface
{
    /**
     * @return $this|ManagerInterface
     * @throws OutOfTimeException
     * @throws EarlyFinishException
     * @throws \Exception
     */
    public function run();

    /**
     * @return $this
     */
    public function reset();
}
