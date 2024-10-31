<?php
namespace Setka\Editor\Service\Gutenberg;

class SetkaEditorBlock extends Block implements BlockInterface
{
    const NAME = 'setka-editor/setka-editor';

    const ATTRIBUTE_THEME = 'setkaEditorTheme';

    const ATTRIBUTE_LAYOUT = 'setkaEditorLayout';

    /**
     * @var string
     */
    protected $name = self::NAME;

    /**
     * @inheritdoc
     */
    public function getRendered()
    {
        return sprintf('<div class="alignfull">%s</div>', $this->content);
    }
}
