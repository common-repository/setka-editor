<?php
namespace Setka\Editor\CLI\Commands;

use Setka\Editor\Admin\Service\FilesManager\AssetsStatus;
use Setka\Editor\Admin\Service\FilesManager\FilesServiceManager;

class FilesCommand extends AbstractStylesCommand
{
    /**
     * FilesCommand constructor.
     *
     * @param FilesServiceManager $serviceManager
     * @param AssetsStatus $status
     */
    public function __construct(FilesServiceManager $serviceManager, AssetsStatus $status)
    {
        parent::__construct($serviceManager, $status);
    }
}
