<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Admin\Service\Screen;
use Setka\Editor\Plugin;

class SetkaEditorThemeDisabledNotice extends Notice
{
    /**
     * @var Screen
     */
    private $screen;

    public function __construct(Screen $screen)
    {
        $this->setName(Plugin::NAME . '-setka-editor-theme-disabled');

        $this->screen = $screen;
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct(): self
    {
        $this
            ->setView(new NoticeErrorView())
            ->getView()->setCssClasses(array_merge(
                $this->getView()->getCssClasses(),
                array('setka-editor-notice', ' setka-editor-notice-error', 'hidden')
            ));

        $content = __('This post uses a disabled style. You can safely edit this post but if you change the style you won’t be able to switch it back.', Plugin::NAME);
        $content = '<p>' . $content . '</p>';

        $this->setContent($content);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRelevant(): bool
    {
        return 'post' === $this->screen->getBase() && !$this->screen->isBlockEditor();
    }
}
