<?php
namespace Setka\Editor\Entities;

interface PostInterface
{
    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * @return string
     */
    public function getTheme();

    /**
     * @param string $theme
     * @return $this
     */
    public function setTheme($theme);

    /**
     * @return string
     */
    public function getLayout();

    /**
     * @param string $layout
     * @return $this
     */
    public function setLayout($layout);

    /**
     * @return bool
     */
    public function isAutoInit();

    /**
     * @param bool $autoInit
     * @return $this
     */
    public function setAutoInit($autoInit);
}
