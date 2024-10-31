<?php
namespace Setka\Editor\Admin\Service\FilesManager;

use Setka\Editor\Admin\Cron\Files\FilesManagerCronEvent;
use Setka\Editor\Admin\Cron\Files\FilesQueueCronEvent;
use Setka\Editor\Admin\Options\Files\ServiceSwitchOption;
use Setka\Editor\Exceptions\Exception;
use Setka\Editor\Service\Styles\AbstractServiceManager;

class FilesServiceManager extends AbstractServiceManager
{
    /**
     * @param ServiceSwitchOption $serviceSwitchOption
     * @param boolean $serviceSwitchEnv
     * @param FilesManagerCronEvent $syncCronEvent
     * @param FilesQueueCronEvent $queueCronEvent
     * @param FilesManager $filesManager
     */
    public function __construct(
        ServiceSwitchOption $serviceSwitchOption,
        $serviceSwitchEnv,
        FilesManagerCronEvent $syncCronEvent,
        FilesQueueCronEvent $queueCronEvent,
        FilesManager $filesManager
    ) {
        parent::__construct($serviceSwitchOption, $serviceSwitchEnv, $syncCronEvent, $queueCronEvent, $filesManager);
    }

    /**
     * @throws Exception
     */
    public function addNewConfig(array $config)
    {
        throw new Exception('The method addNewConfig() not implemented for Setka\Editor\Admin\Service\FilesManager\ServiceManager.');
    }
}
