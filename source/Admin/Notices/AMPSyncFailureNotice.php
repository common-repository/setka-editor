<?php
namespace Setka\Editor\Admin\Notices;

use Setka\Editor\Admin\Options\AMP\AMPSyncFailureNoticeOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncFailureOption;
use Setka\Editor\Admin\Options\AMP\AMPSyncLastFailureNameOption;
use Setka\Editor\Plugin;

class AMPSyncFailureNotice extends AbstractStylesNotice
{
    /**
     * @param AMPSyncFailureNoticeOption $syncFailureNoticeOption
     * @param AMPSyncFailureOption $syncFailureOption
     * @param AMPSyncLastFailureNameOption $syncLastFailureNameOption
     */
    public function __construct(
        AMPSyncFailureNoticeOption $syncFailureNoticeOption,
        AMPSyncFailureOption $syncFailureOption,
        AMPSyncLastFailureNameOption $syncLastFailureNameOption
    ) {
        parent::__construct($syncFailureNoticeOption, $syncFailureOption, $syncLastFailureNameOption);
        $this->setName(Plugin::NAME . '_amp_sync_failure');
    }

    /**
     * @inheritDoc
     */
    protected function buildContent(): string
    {
        $errorCode = $this->getErrorCode();
        if ($errorCode) {
            return sprintf(
                __('Setka Editor could not update styles for Google AMP. Error code: <code>%1$s</code>. Please contact Setka Editor team at <a href="mailto:support@tiny.cloud" target="_blank">support@tiny.cloud</a>.', Plugin::NAME),
                esc_html($errorCode)
            );
        } else {
            return __('Setka Editor could not update styles for Google AMP. Please contact Setka Editor team at <a href="mailto:support@tiny.cloud" target="_blank">support@tiny.cloud</a>.', Plugin::NAME);
        }
    }
}
