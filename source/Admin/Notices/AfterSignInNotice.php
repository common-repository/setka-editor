<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Admin\Transients\AfterSignInNoticeTransient;
use Setka\Editor\Plugin;

class AfterSignInNotice extends Notice
{
    public function __construct()
    {
        $this
            ->setName(Plugin::NAME . '_after_sign_in')
            ->setRelevantStorage(new AfterSignInNoticeTransient());
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct(): self
    {
        $this
            ->setView(new NoticeSuccessView())
            ->getView()->setCssClasses(array_merge(
                $this->getView()->getCssClasses(),
                array('setka-editor-notice', 'setka-editor-notice-success')
            ));

        $content = sprintf(
            /* translators: %1$s - url to post create page  */
            __('Congratulations! You can <a href="%1$s">create a new post with Setka Editor</a>.', Plugin::NAME),
            esc_url(admin_url('post-new.php?' . Plugin::NAME .  '-auto-init'))
        );

        $this->setContent('<p>' . $content . '</p>');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isRelevant(): bool
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        return parent::isRelevant();
    }
}
