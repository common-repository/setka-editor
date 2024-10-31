<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\NoticeErrorView;
use Korobochkin\WPKit\Notices\NoticeInterface;
use Korobochkin\WPKit\Notices\NoticesStackInterface;
use Korobochkin\WPKit\Notices\NoticeSuccessView;
use Korobochkin\WPKit\Notices\NoticeViewInterface;
use Setka\Editor\Admin\Notices\NoticeSuccessView as SetkaNoticeSuccessView;
use Setka\Editor\Admin\Notices\NoticeErrorView as SetkaNoticeErrorView;
use Setka\Editor\Admin\Service\Kses;
use Setka\Editor\Admin\Service\Screen;

class NoticesStack extends \Korobochkin\WPKit\Notices\NoticesStack implements NoticesStackInterface
{
    /**
     * @var boolean
     */
    private $gutenbergSupport;

    /**
     * @var Screen
     */
    private $screen;

    /**
     * NoticesStack constructor.
     *
     * @param bool $gutenbergSupport
     * @param Screen $screen
     */
    public function __construct(bool $gutenbergSupport, Screen $screen)
    {
        $this->gutenbergSupport = $gutenbergSupport;
        $this->screen           = $screen;
    }

    /**
     * @inheritdoc
     */
    public function run(): void
    {
        if ($this->gutenbergSupport && $this->screen->isBlockEditor()) {
            return;
        }
        parent::run();
    }

    /**
     * Return all relevant notices as array which can be useful in JS.
     *
     * @return array All notices info.
     */
    public function getNoticesAsArray(): array
    {
        /**
         * @var $notice NoticeInterface
         */
        $notices = array();
        foreach ($this->notices as $notice) {
            $notices[] = $this->buildNoticeData($notice);
        }
        return $notices;
    }

    private function buildNoticeData(NoticeInterface $notice): array
    {
        return array(
            'name' => $notice->getName(),
            'content' => Kses::filterBaseMarkup($notice->lateConstruct()->getContent()),
            'class' => get_class($notice),
            'relevant' => $notice->isRelevant(),
            'isDismissible' => true,
            'status' => $this->getNoticeStatus($notice->getView()),
        );
    }

    /**
     * @param NoticeViewInterface $noticeView
     *
     * @return string
     */
    private function getNoticeStatus(NoticeViewInterface $noticeView): string
    {
        switch (get_class($noticeView)) {
            case NoticeSuccessView::class:
            case SetkaNoticeSuccessView::class:
                $status = 'success';
                break;

            case NoticeErrorView::class:
            case SetkaNoticeErrorView::class:
                $status = 'error';
                break;

            default:
                $status = 'info';
                break;
        }
        return $status;
    }
}
