<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\ManagerInterface;

interface FilesManagerInterface extends ManagerInterface
{
    /**
     * Transfer pending files back to download queue.
     *
     * @return $this
     */
    public function checkPendingFiles();

    /**
     * Remove all files from DB.
     *
     * @return $this
     * @throws OutOfTimeException
     * @throws PostException
     */
    public function deleteAllFiles();
}
