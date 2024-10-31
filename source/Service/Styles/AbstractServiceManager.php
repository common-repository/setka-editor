<?php
namespace Setka\Editor\Service\Styles;

use Setka\Editor\Admin\Cron\AbstractStylesCronEvent;
use Setka\Editor\Admin\Cron\AbstractStylesQueueCronEvent;
use Setka\Editor\Admin\Options\Styles\AbstractServiceSwitchOption;
use Setka\Editor\Service\Manager\FilesManager\FilesManagerInterface;

abstract class AbstractServiceManager
{
    /**
     * @var AbstractServiceSwitchOption
     */
    private $serviceSwitchOption;

    /**
     * @var boolean
     */
    private $serviceSwitchEnv;

    /**
     * @var AbstractStylesCronEvent
     */
    private $syncCronEvent;

    /**
     * @var AbstractStylesQueueCronEvent
     */
    private $queueCronEvent;

    /**
     * @var FilesManagerInterface
     */
    private $filesManager;

    /**
     * AbstractServiceManager constructor.
     *
     * @param AbstractServiceSwitchOption $serviceSwitchOption
     * @param boolean $serviceSwitchEnv
     * @param AbstractStylesCronEvent $syncCronEvent
     * @param AbstractStylesQueueCronEvent $queueCronEvent
     * @param FilesManagerInterface $filesManager
     */
    public function __construct(
        AbstractServiceSwitchOption $serviceSwitchOption,
        $serviceSwitchEnv,
        AbstractStylesCronEvent $syncCronEvent,
        AbstractStylesQueueCronEvent $queueCronEvent,
        FilesManagerInterface $filesManager
    ) {
        $this->serviceSwitchOption = $serviceSwitchOption;
        $this->serviceSwitchEnv    = $serviceSwitchEnv;
        $this->syncCronEvent       = $syncCronEvent;
        $this->queueCronEvent      = $queueCronEvent;
        $this->filesManager        = $filesManager;
    }

    public function enable()
    {
        $this->serviceSwitchOption->updateValue(true);
        $this->restartCronEvents();
    }

    /**
     * @param bool $save
     */
    public function disable($save = true)
    {
        if ($save) {
            $this->serviceSwitchOption->updateValue(false);
        }
        $this->syncCronEvent->unscheduleAll();
        $this->queueCronEvent->unscheduleAll();
    }

    public function restart()
    {
        $this->enable();
        $this->discardCurrentState();
    }

    public function discardCurrentState()
    {
        $this->filesManager->reset();
    }

    /**
     * @throws \Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException
     * @throws \Setka\Editor\Service\Manager\Exceptions\EarlyFinishException
     * @throws \Exception
     */
    public function sync()
    {
        $this->filesManager->run();
    }

    public function pending()
    {
        $this->filesManager->checkPendingFiles();
    }

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function addNewConfig(array $config)
    {
        $this->filesManager->addNewConfig($config);
        $this->restartCronEvents();
    }

    /**
     * @throws \Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException
     * @throws \Setka\Editor\Service\Manager\Exceptions\PostException
     */
    public function deleteAllFiles()
    {
        $this->filesManager->deleteAllFiles();
    }

    /**
     * @return bool
     */
    public function isOnByOption()
    {
        return $this->serviceSwitchOption->get();
    }

    /**
     * @return bool
     */
    public function isOnByEnv()
    {
        return $this->serviceSwitchEnv;
    }

    /**
     * @return bool
     */
    public function isOn()
    {
        return $this->isOnByEnv() && $this->isOnByOption();
    }

    private function restartCronEvents()
    {
        $this->syncCronEvent->restart();
        $this->queueCronEvent->restart();
    }
}
