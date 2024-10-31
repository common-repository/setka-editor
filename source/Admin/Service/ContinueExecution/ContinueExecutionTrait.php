<?php
namespace Setka\Editor\Admin\Service\ContinueExecution;

trait ContinueExecutionTrait
{
    /**
     * @var callable
     */
    protected $continueExecution;

    /**
     * @return true If we can continue execution.
     * @throws OutOfTimeException If time of current process is over.
     */
    protected function continueExecution()
    {
        return call_user_func($this->continueExecution);
    }
}
