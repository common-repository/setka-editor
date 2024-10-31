<?php
namespace Setka\Editor\Admin\Notices;

use Korobochkin\WPKit\Notices\NoticeInterface;

class NoticeErrorView extends \Korobochkin\WPKit\Notices\NoticeErrorView
{
    /**
     * @return array
     */
    public function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    /**
     * @param array $cssClasses
     * @return $this
     */
    public function setCssClasses(array $cssClasses): self
    {
        $this->cssClasses = $cssClasses;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function render(NoticeInterface $notice): void
    {
        $this->notice = $notice;
        $this->prepareCssClasses();

        if ($notice->getTitle()) {
            $title = '<p class="notice-title">' . $notice->getTitle() . '</p>';
        } else {
            $title = '';
        }

        $cssClasses = implode(' ', $this->cssClasses);

        $id = 'wp-kit-notice-' . $notice->getName();

        printf(
            '<div class="%1$s" id="%3$s" data-notice-class="%4$s">%2$s</div>',
            esc_attr($cssClasses),
            wp_kses(
                $title . $notice->getContent(),
                array('a' => array('href' => array(), 'target' => array()), 'p' => array(), 'code' => array())
            ),
            esc_attr($id),
            esc_attr(get_class($notice))
        );
    }
}
