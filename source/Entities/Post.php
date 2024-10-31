<?php
namespace Setka\Editor\Entities;

class Post implements PostInterface
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @var boolean
     */
    protected $autoInit = false;

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @inheritdoc
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @inheritdoc
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAutoInit()
    {
        return $this->autoInit;
    }

    /**
     * @inheritdoc
     */
    public function setAutoInit($autoInit)
    {
        $this->autoInit = $autoInit;
        return $this;
    }
}
