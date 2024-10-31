<?php
namespace Setka\Editor\Admin\Cron\Files;

use Setka\Editor\Admin\Cron\AbstractStylesCronEvent;
use Setka\Editor\Plugin;

class FilesManagerCronEvent extends AbstractStylesCronEvent
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_cron_files_manager');
    }
}
