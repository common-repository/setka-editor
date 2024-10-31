<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\Notice;
use Setka\Editor\Plugin;

class SettingsSavedSuccessfullyNotice extends Notice
{
    public function __construct()
    {
        $this->setName(Plugin::NAME . '_settings_saved_successfully');
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

        $this->setContent('<p>' . __('Settings saved successfully.', Plugin::NAME) . '</p>');

        return $this;
    }
}
