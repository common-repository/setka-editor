<?php
namespace Setka\Editor\Admin\Service;

use Setka\Editor\PostMetas\UseEditorPostMeta;

class RichEdit
{
    /**
     * @var UseEditorPostMeta
     */
    private $useEditorPostMeta;

    /**
     * @var Screen
     */
    private $screen;

    /**
     * RichEdit constructor.
     *
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param Screen $screen
     */
    public function __construct(UseEditorPostMeta $useEditorPostMeta, Screen $screen)
    {
        $this->useEditorPostMeta = $useEditorPostMeta;
        $this->screen            = $screen;
    }

    /**
     * @param boolean $richEdit
     * @return boolean
     */
    public function userCanRichEdit($richEdit)
    {
        return !$this->screen->isBlockEditor() &&
               $this->useEditorPostMeta->getPostId() &&
               $this->useEditorPostMeta->get() ? false : $richEdit;
    }
}
