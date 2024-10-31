<?php
namespace Setka\Editor\Service\Manager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Service\ContinueExecution\ContinueExecutionTrait;

abstract class AbstractStage implements StageInterface
{
    use ContinueExecutionTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     */
    public function __construct($continueExecution, LoggerInterface $logger)
    {
        $this->continueExecution = $continueExecution;
        $this->logger            = $logger;
    }
}
