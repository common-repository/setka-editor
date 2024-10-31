<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class PaymentErrorNotice extends Notice
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    public function __construct(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setName(Plugin::NAME . '_payment_error');

        $this->setkaEditorAccount = $setkaEditorAccount;
    }

    public function lateConstruct(): self
    {
        $this
            ->setView(new NoticeErrorView())
            ->getView()->setCssClasses(array_merge(
                $this->getView()->getCssClasses(),
                array('setka-editor-notice', ' setka-editor-notice-error')
            ));

        $content  = '<p>' . __('We could not process your monthly payment using the card on file. Please <a href="https://editor.setka.io/app/" target="_blank">edit your credit card info</a> or check your balance.', Plugin::NAME) . '</p>';
        $content .= '<p>' . __('If the payment is not completed in 13 days your Setka Editor plugin functionality will be limited to the Free Plan.', Plugin::NAME) . '</p>';

        $this->setContent($content);

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

        if ($this->setkaEditorAccount->isSubscriptionPaymentPastDue()) {
            return true;
        }

        return false;
    }
}
