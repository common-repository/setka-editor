<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Service\SetkaEditorAPI\Prototypes\ActionAbstract;

abstract class AbstractCurrentThemeAction extends ActionAbstract
{
    const EDITOR_VERSION = 'content_editor_version';

    const EDITOR_FILES = 'content_editor_files';

    const THEME_FILES = 'theme_files';

    const PLUGINS = 'plugins';

    const AMP_STYLES = 'amp_styles';

    const STANDALONE_STYLES = 'standalone_styles';

    const PUBLIC_TOKEN = 'public_token';
}
