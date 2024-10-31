<?php
namespace Setka\Editor\Admin\Cron;

use Setka\Editor\Plugin;
use Setka\Editor\Service\Manager\FilesManager\FilesManagerInterface;

abstract class AbstractStylesCronEvent extends AbstractExtendedCronEvent
{
    /**
     * @var FilesManagerInterface
     */
    private $filesManager;

    public function __construct()
    {
        $this->setTimestamp(1);
        $this->setRecurrence(Plugin::_NAME_ . '_every_minute');
    }

    public function execute()
    {
        try {
            $this->filesManager->run();
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
