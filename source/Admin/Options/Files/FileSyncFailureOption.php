<?php
namespace Setka\Editor\Admin\Options\Files;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Editor\Plugin;

/**
 * Class FileSyncFailureOption
 */
class FileSyncFailureOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_file_sync_failure')
            ->setDefaultValue(false);
    }
}
