<?php
namespace Setka\Editor\Service\Config;

class ImageSizesConfig
{
    const TYPE_INT = 'int';

    const TYPE_BOOL = 'bool';

    const SETTING_WIDTH = 'width';

    const SETTING_HEIGHT = 'height';

    const SETTING_CROP = 'crop';

    const SIZE_FULL = 'full';

    /**
     * @var array
     */
    private static $attributeMap = array(
        self::SETTING_WIDTH => array('_size_w', self::TYPE_INT),
        self::SETTING_HEIGHT => array('_size_h', self::TYPE_INT),
        self::SETTING_CROP => array('_crop', self::TYPE_BOOL),
    );

    /**
     * @var array
     */
    private static $full = array(
        self::SETTING_WIDTH => 0,
        self::SETTING_HEIGHT => 0,
        self::SETTING_CROP => false,
    );

    /**
     * @return array
     */
    public static function getAllImageSizes()
    {
        $sizes = self::getRegisteredImageSizes();

        $sizes[self::SIZE_FULL] = self::$full;

        return $sizes;
    }

    /**
     * @param $sizes array
     * @param $crop boolean
     */
    public static function filter(array $sizes, $crop = false)
    {
        foreach ($sizes as $name => $config) {
            if ($config[self::SETTING_CROP] === $crop) {
                continue;
            }
            unset($sizes[$name]);
        }

        return $sizes;
    }

    /**
     * @return array
     */
    public static function getRegisteredImageSizes()
    {
        $names = self::getRegistered();
        $sizes = array();

        foreach ($names as $name) {
            $size = array();

            foreach (self::$attributeMap as $attribute => $settings) {
                $size[$attribute] = self::cast(self::get($name, $attribute, $settings[0]), $settings[1]);
            }

            $sizes[$name] = $size;
        }

        return $sizes;
    }

    /**
     * @return array
     */
    private static function getRegistered()
    {
        return get_intermediate_image_sizes(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.get_intermediate_image_sizes_get_intermediate_image_sizes
    }

    private static function get($name, $setting, $option)
    {
        global $_wp_additional_image_sizes;

        if (isset($_wp_additional_image_sizes[$name][$setting])) {
            return $_wp_additional_image_sizes[$name][$setting];
        } else {
            return get_option($name . $option);
        }
    }

    /**
     * @param $value mixed
     * @param string $type
     *
     * @throws \LogicException
     *
     * @return mixed
     */
    private static function cast($value, $type = self::TYPE_INT)
    {
        switch ($type) {
            case self::TYPE_INT:
                return (int) $value;

            case self::TYPE_BOOL:
                if (is_bool($value)) {
                    return $value;
                } elseif (is_string($value) && '1' === $value) {
                    return true;
                }
                return false;

            default:
                throw new \LogicException('Unknown type');
        }
    }
}
