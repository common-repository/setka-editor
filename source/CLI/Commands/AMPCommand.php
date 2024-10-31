<?php
namespace Setka\Editor\CLI\Commands;

use Setka\Editor\Service\AMP\AMPServiceManager;
use Setka\Editor\Service\AMP\AMPStatus;

class AMPCommand extends AbstractStylesCommand
{
    /**
     * @param AMPServiceManager $ampServiceManager
     * @param AMPStatus $ampStatus
     */
    public function __construct(AMPServiceManager $ampServiceManager, AMPStatus $ampStatus)
    {
        parent::__construct($ampServiceManager, $ampStatus);
    }
}
