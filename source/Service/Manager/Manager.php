<?php
namespace Setka\Editor\Service\Manager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Admin\Service\ContinueExecution\ContinueExecutionTrait;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Service\Manager\Exceptions\EarlyFinishException;

class Manager implements ManagerInterface
{
    use ContinueExecutionTrait;

    /**
     * @var AbstractSyncStageOption
     */
    private $currentStageOption;

    /**
     * @var array
     */
    private $stagesMap;

    /**
     * @var array
     */
    private $stages;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param AbstractSyncStageOption $currentStageOption
     * @param array $stagesMap
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     */
    public function __construct(
        AbstractSyncStageOption $currentStageOption,
        array $stagesMap,
        $continueExecution,
        LoggerInterface $logger
    ) {
        $this->currentStageOption = $currentStageOption;
        $this->stagesMap          = $stagesMap;
        $this->continueExecution  = $continueExecution;
        $this->logger             = $logger;
        $this->stages             = array_keys($this->stagesMap);
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->loop();
        return $this;
    }

    /**
     * @return $this
     * @throws OutOfTimeException
     * @throws \Exception
     */
    protected function loop()
    {
        $stagesCounter = count($this->stages) - 1; // To prevent last stage ('ok') run.

        $this->logger->debug('Loop stages started.', array(
            'stagesCounter' => $stagesCounter,
            'stagesMap' => $this->stagesMap,
        ));

        for ($i = $this->findCurrentStageIndex(); $i < $stagesCounter; $i++) {
            $this->continueExecution();
            $this
                ->stage($this->stages[$i])
                ->afterStage($this->stages[$i], $this->stages[$i + 1])
                ->saveStage($this->stages[$i + 1]);
        }

        return $this;
    }

    /**
     * @param string $stage
     * @return $this
     * @throws \Exception
     * @throws EarlyFinishException
     */
    protected function stage($stage)
    {
        $context = array('stage' => $stage);
        $this->logger->info('Start of stage.', $context);
        call_user_func($this->stagesMap[$stage]);
        $this->logger->info('End of stage.', $context);
        return $this;
    }

    /**
     * @param string $finishedStageName
     * @param string $nextStageName
     * @throws \Exception
     * @throws EarlyFinishException
     * @return $this
     */
    protected function afterStage($finishedStageName, $nextStageName)
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        $this->logger->info('Resetting stage option.');
        $this->currentStageOption->delete();
        return $this;
    }

    /**
     * @return string
     */
    private function getCurrentStage()
    {
        $stage = $this->currentStageOption->get();
        $this->logger->debug('Current stage from option.', array('stage' => $stage));
        return $stage;
    }

    /**
     * @param string $stage
     */
    private function saveStage($stage)
    {
        $this->logger->debug('Saving stage name.', array('stage' => $stage));
        $this->currentStageOption->updateValue($stage);
    }

    /**
     * Finds current stage index. Or return first stage index if stage not found.
     * @return int
     */
    private function findCurrentStageIndex()
    {
        $current = $this->getCurrentStage();

        if (isset($this->stagesMap[$current])) {
            return array_search($current, $this->stages, true);
        }

        reset($this->stages);
        return key($this->stages);
    }
}
