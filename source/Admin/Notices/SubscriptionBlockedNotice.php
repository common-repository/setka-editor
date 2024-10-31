<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class SubscriptionBlockedNotice extends Notice
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    public function __construct(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setName(Plugin::NAME . '_subscription_blocked');

        $this->setkaEditorAccount = $setkaEditorAccount;
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
                array('setka-editor-notice', 'setka-editor-notice-error')
            ));

        $content = __('Setka Editor plugin was deactivated because of the technical error. Please contact Setka Editor team at <a href="mailto:support@tiny.cloud" target="_blank">support@tiny.cloud</a>.', Plugin::NAME);
        $this->setContent('<p>' . $content . '</p>');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRelevant(): bool
    {
        if (!parent::isRelevant()) {
            return false;
        }

        if (!current_user_can('manage_options')) {
            return false;
        }

        if (!$this->setkaEditorAccount->isLoggedIn()) {
            return false;
        }

        if (!$this->setkaEditorAccount->isSubscriptionStatusRunning()) {
            return true;
        }

        return false;
    }
}
