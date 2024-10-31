<?php
namespace Setka\Editor\Service\Standalone;

use Setka\Editor\Admin\Cron\StandaloneStylesCronEvent;
use Setka\Editor\Admin\Cron\StandaloneStylesQueueCronEvent;
use Setka\Editor\Admin\Options\Standalone\ServiceSwitchOption;
use Setka\Editor\Admin\Options\Standalone\UseCriticalOption;
use Setka\Editor\Service\Styles\AbstractServiceManager;

class StandaloneServiceManager extends AbstractServiceManager
{
    /**
     * @var UseCriticalOption
     */
    private $useCriticalOption;

    /**
     * StandaloneServiceManager constructor.
     *
     * @param ServiceSwitchOption $serviceSwitchOption
     * @param boolean $serviceSwitchEnv
     * @param StandaloneStylesCronEvent $syncCronEvent
     * @param StandaloneStylesQueueCronEvent $queueCronEvent
     * @param StandaloneStylesManager $stylesManager
     * @param UseCriticalOption $useCriticalOption
     */
    public function __construct(
        ServiceSwitchOption $serviceSwitchOption,
        $serviceSwitchEnv,
        StandaloneStylesCronEvent $syncCronEvent,
        StandaloneStylesQueueCronEvent $queueCronEvent,
        StandaloneStylesManager $stylesManager,
        UseCriticalOption $useCriticalOption
    ) {
        parent::__construct($serviceSwitchOption, $serviceSwitchEnv, $syncCronEvent, $queueCronEvent, $stylesManager);
        $this->useCriticalOption = $useCriticalOption;
    }

    /**
     * @param bool $critical
     */
    public function enableWithFlags(bool $critical = true): void
    {
        $this->useCriticalOption->updateValue($critical);
        $this->enable();
    }

    /**
     * @return bool
     */
    public function isOnCritical(): bool
    {
        return $this->isOnByOption() && $this->useCriticalOption->get();
    }
}
