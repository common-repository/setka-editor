<?php
namespace Setka\Editor\Admin\Cron\Files;

use Setka\Editor\Admin\Cron\AbstractStylesQueueCronEvent;
use Setka\Editor\Plugin;

class FilesQueueCronEvent extends AbstractStylesQueueCronEvent
{
    public function __construct()
    {
        parent::__construct();
        $this->setName(Plugin::_NAME_ . '_cron_files_queue');
    }
}
