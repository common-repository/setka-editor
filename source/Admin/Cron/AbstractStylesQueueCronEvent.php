<?php
namespace Setka\Editor\Admin\Cron;

use Setka\Editor\Service\Manager\FilesManager\FilesManagerInterface;

abstract class AbstractStylesQueueCronEvent extends AbstractExtendedCronEvent
{
    /**
     * @var FilesManagerInterface
     */
    private $filesManager;

    public function __construct()
    {
        $this->setTimestamp(1)
             ->setRecurrence('hourly');
    }

    public function execute()
    {
        try {
            $this->filesManager->checkPendingFiles();
        } catch (\Exception $exception) {
        }
    }

    /**
     * @param FilesManagerInterface $filesManager
     *
     * @return $this
     */
    public function setFilesManager(FilesManagerInterface $filesManager)
    {
        $this->filesManager = $filesManager;
        return $this;
    }
}
