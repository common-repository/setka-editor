<?php
namespace Setka\Editor\Service\Manager;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\Options\Special\BoolOption;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Admin\Options\Styles\ConfigIdOptionInterface;
use Setka\Editor\Admin\Options\Styles\ConfigOptionInterface;
use Setka\Editor\Service\Manager\Exceptions\AttemptsLimitException;
use Setka\Editor\Service\Manager\Exceptions\InvalidConfigException;
use Setka\Editor\Service\Manager\Exceptions\PendingFilesException;
use Setka\Editor\Service\Manager\Traits\AttemptsLimitTrait;
use Setka\Editor\Service\Manager\Traits\FailureTrait;

class ConfigSupportExtendedManager extends ConfigSupportManager
{
    use FailureTrait;

    use AttemptsLimitTrait;

    /**
     * @var BoolOption
     */
    private $finishedOption;

    /**
     * @param AbstractSyncStageOption $currentStageOption
     * @param array $stagesMap
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param ConfigIdOptionInterface $configIdOption
     * @param ConfigOptionInterface $configOption
     * @param string $configPostType
     * @param BoolOption $failureOption
     * @param OptionInterface $failureNameOption
     * @param BoolOption $failureNoticeOption
     * @param BoolOption $attemptsLimitOption
     * @param BoolOption $finishedOption
     */
    public function __construct(
        AbstractSyncStageOption $currentStageOption,
        array $stagesMap,
        $continueExecution,
        LoggerInterface $logger,
        ConfigIdOptionInterface $configIdOption,
        ConfigOptionInterface $configOption,
        $configPostType,
        BoolOption $failureOption,
        OptionInterface $failureNameOption,
        BoolOption $failureNoticeOption,
        BoolOption $attemptsLimitOption,
        BoolOption $finishedOption
    ) {
        parent::__construct(
            $currentStageOption,
            $stagesMap,
            $continueExecution,
            $logger,
            $configIdOption,
            $configOption,
            $configPostType
        );

        $this->failureOption       = $failureOption;
        $this->failureNameOption   = $failureNameOption;
        $this->failureNoticeOption = $failureNoticeOption;

        $this->attemptsLimitOption = $attemptsLimitOption;

        $this->finishedOption = $finishedOption;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        try {
            $this->beforeStages();
            if (!$this->finishedOption->get()) {
                return parent::run();
            }
        } catch (AttemptsLimitException $exception) {
            $this->logger->error('Limit exceeded of download attempts. Stop executing.', array($exception));
            $this->saveFailure($exception);
        } catch (PendingFilesException $exception) {
            $this->logger->info('Pending files exists in queue. Manager should be run again.');
        } catch (InvalidConfigException $exception) {
            $this->logger->error('Invalid config.', array(
                'exception' => $exception,
                'constraints_violations' => (function (InvalidConfigException $exception) {
                    $errors = array();

                    foreach ($exception->getConstraintViolationList() as $violation) {
                        $errors[] = (string) $violation;
                    }

                    return $errors;
                })($exception),
                'previous_exception' => $exception->getPrevious(),
            ));
        } catch (\Exception $exception) {
            $this->logger->error('Exception caught.', array($exception));
            $this->saveFailure($exception);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function reset()
    {
        parent::reset();
        $this->finishedOption->delete();
        $this->deleteFailure();
        $this->deleteLimitAttempts();
        return $this;
    }

    /**
     * @throws AttemptsLimitException
     * @throws Exceptions\NoConfigException
     */
    protected function beforeStages()
    {
        parent::beforeStages();
        if ($this->isAttemptsLimitExceeded()) {
            throw new AttemptsLimitException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function afterStage($finishedStageName, $nextStageName)
    {
        if (AbstractSyncStageOption::OK === $nextStageName) {
            $this->markFinished();
        }
        return parent::afterStage($finishedStageName, $nextStageName);
    }

    /**
     * Mark process complete for other code.
     *
     * @return $this.
     */
    protected function markFinished()
    {
        $this->logger->info('Manager successfully finished its work.');
        $this->finishedOption->updateValue(true);
        $this->deleteFailure();
        return $this;
    }
}
