<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Admin\Options\Styles\AbstractLastFailureNameOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureNoticeOption;
use Setka\Editor\Admin\Options\Styles\AbstractSyncFailureOption;

abstract class AbstractStylesNotice extends Notice
{
    /**
     * @var AbstractSyncFailureOption
     */
    protected $syncFailureOption;

    /**
     * @var AbstractLastFailureNameOption
     */
    protected $lastFailureNameOption;

    /**
     * @param AbstractSyncFailureNoticeOption $syncFailureNoticeOption
     * @param AbstractSyncFailureOption $syncFailureOption
     * @param AbstractLastFailureNameOption $lastFailureNameOption
     */
    public function __construct(
        AbstractSyncFailureNoticeOption $syncFailureNoticeOption,
        AbstractSyncFailureOption $syncFailureOption,
        AbstractLastFailureNameOption $lastFailureNameOption
    ) {
        $this->setRelevantStorage($syncFailureNoticeOption)
             ->setDismissible(true);

        $this->syncFailureOption     = $syncFailureOption;
        $this->lastFailureNameOption = $lastFailureNameOption;
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct(): self
    {
        $this->setView(new NoticeErrorView())
             ->getView()->setCssClasses(array_merge(
                 $this->getView()->getCssClasses(),
                 array('setka-editor-notice', ' setka-editor-notice-error')
             ));

        $this->setContent('<p>' . $this->buildContent() . '</p>');
        return $this;
    }

    abstract protected function buildContent(): string;

    /**
     * @inheritdoc
     */
    public function isRelevant(): bool
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        if (!$this->syncFailureOption->get()) {
            return false;
        }

        return parent::isRelevant();
    }

    protected function getErrorCode(): ?string
    {
        $errorCode = $this->lastFailureNameOption->get();
        return (is_string($errorCode) && !empty($errorCode)) ? $errorCode : null;
    }
}
