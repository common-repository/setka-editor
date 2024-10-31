<?php
namespace Setka\Editor\Service\AMP;

use Setka\Editor\Admin\Cron\AMPStylesCronEvent;
use Setka\Editor\Admin\Cron\AMPStylesQueueCronEvent;
use Setka\Editor\Admin\Options\AMP\ServiceSwitchOption;
use Setka\Editor\Service\Styles\AbstractServiceManager;

class AMPServiceManager extends AbstractServiceManager
{
    /**
     * AMPServiceManager constructor.
     *
     * @param ServiceSwitchOption $serviceSwitchOption
     * @param boolean $serviceSwitchEnv
     * @param AMPStylesCronEvent $syncCronEvent
     * @param AMPStylesQueueCronEvent $queueCronEvent
     * @param AMPStylesManager $stylesManager
     */
    public function __construct(
        ServiceSwitchOption $serviceSwitchOption,
        $serviceSwitchEnv,
        AMPStylesCronEvent $syncCronEvent,
        AMPStylesQueueCronEvent $queueCronEvent,
        AMPStylesManager $stylesManager
    ) {
        parent::__construct($serviceSwitchOption, $serviceSwitchEnv, $syncCronEvent, $queueCronEvent, $stylesManager);
    }
}
