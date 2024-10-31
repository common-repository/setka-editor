<?php
namespace Setka\Editor\Admin\Pages\Uninstall;

class SetkaEditorAssets
{
    /**
     * @var string
     */
    private static $style;

    /**
     * @var string
     */
    private static $plugins;

    public static function setup($style, $plugins)
    {
        self::$style = $style;

        self::$plugins = $plugins;

        add_action('wp_enqueue_scripts', array(self::class, 'validateAndEnqueue'));
    }

    public static function validateAndEnqueue()
    {
        $query = SetkaEditorUtils::getQuery();
        if ($query && $query->is_singular()) {
            $post = current($query->posts);
            if ($post && SetkaEditorUtils::isSetkaPost($post)) {
                self::enqueue();
            }
        }
    }

    public static function enqueue()
    {
        wp_enqueue_style(
            'setka-editor-theme-resources',
            self::$style,
            array(),
            '1.0.0'
        );
        wp_enqueue_script(
            'setka-editor-theme-plugins',
            self::$plugins,
            array('jquery'),
            '1.0.0',
            true
        );
    }
}
