<?php
namespace Setka\Editor\CLI\Commands;

use Setka\Editor\Service\Standalone\StandaloneServiceManager;
use Setka\Editor\Service\Standalone\StandaloneStatus;

class StandaloneCommand extends AbstractStylesCommand
{
    /**
     * @param StandaloneServiceManager $serviceManager
     * @param StandaloneStatus $status
     */
    public function __construct(StandaloneServiceManager $serviceManager, StandaloneStatus $status)
    {
        parent::__construct($serviceManager, $status);
    }
}
