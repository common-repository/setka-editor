<?php
namespace Setka\Editor\Admin\Pages\Settings;

use Setka\Editor\Service\Config\ImageSizesConfig;

class ImageSize
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

    /**
     * @var boolean
     */
    private $crop;

    /**
     * ImageSize constructor.
     *
     * @param string $id
     * @param int $width
     * @param int $height
     * @param bool $crop
     */
    public function __construct($id, $width = 0, $height = 0, $crop = false)
    {
        $this->id     = $id;
        $this->width  = $width;
        $this->height = $height;
        $this->crop   = $crop;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return bool
     */
    public function isCrop()
    {
        return $this->crop;
    }

    /**
     * @return array
     */
    public function buildAttributes()
    {
        $attributes = array(
            'data-width' => $this->width,
            'data-height' => $this->height,
        );

        if (ImageSizesConfig::SIZE_FULL === $this->id) {
            $attributes['checked']  = 'checked';
            $attributes['disabled'] = 'disabled';
        }

        return $attributes;
    }
}
