<?php
namespace Setka\Editor\Admin\Notices;

class NoticeSuccessView extends \Korobochkin\WPKit\Notices\NoticeSuccessView
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
}
