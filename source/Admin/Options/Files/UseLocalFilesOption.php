<?php
namespace Setka\Editor\Admin\Options\Files;

use Korobochkin\WPKit\Options\Special\BoolOption;
use Setka\Editor\Plugin;

/**
 * Indicate should we enqueue local files or not.
 */
class UseLocalFilesOption extends BoolOption
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_use_local_files')
            ->setDefaultValue(false);
    }
}
