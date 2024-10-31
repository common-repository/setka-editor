<?php
namespace Setka\Editor\PostMetas;

use Korobochkin\WPKit\PostMeta\Special\BoolPostMeta;
use Setka\Editor\Plugin;

/**
 * Class UseEditorPostMeta
 */
class UseEditorPostMeta extends BoolPostMeta
{
    public function __construct()
    {
        parent::__construct();
        $this
            ->setName(Plugin::_NAME_ . '_use_editor')
            ->setVisibility(false)
            ->setDefaultValue(false);
    }
}
