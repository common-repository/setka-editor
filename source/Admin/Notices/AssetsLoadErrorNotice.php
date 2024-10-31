<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Admin\Service\Screen;
use Setka\Editor\Plugin;

class AssetsLoadErrorNotice extends Notice
{
    /**
     * @var Screen
     */
    private $screen;

    public function __construct(Screen $screen)
    {
        $this->setName(Plugin::NAME . '-assets-load-error');

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

        $content = __('Couldn\'t load Setka Editor JSON file. Please try again or contact Setka Editor team at <a href="mailto:support@tiny.cloud">support@tiny.cloud</a>.', Plugin::NAME);
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
