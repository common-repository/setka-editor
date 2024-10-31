<?php
namespace Setka\Editor\Admin\Pages\Uninstall;

class SetkaEditorTheContent
{
    const KEY_FILTER = 0;

    const KEY_FILTER_CALLBACK = 'callable';

    const KEY_FILTER_PRIORITY = 'priority';

    const KEY_FILTER_ARGS = 'args';

    const KEY_NEED_TO_BE_REMOVED = 1;

    const KEY_HAS_FILTER = 2;

    /**
     * @var array Filters which we handle.
     */
    private static $filtersList = array(
        array(
            self::KEY_FILTER => array(
                self::KEY_FILTER_CALLBACK => 'wpautop',
                self::KEY_FILTER_PRIORITY => 10,
                self::KEY_FILTER_ARGS => 1
            ),
            self::KEY_NEED_TO_BE_REMOVED => true,
            self::KEY_HAS_FILTER => null
        ),
        array(
            self::KEY_FILTER => array(
                self::KEY_FILTER_CALLBACK => 'shortcode_unautop',
                self::KEY_FILTER_PRIORITY => 10,
                self::KEY_FILTER_ARGS => 1
            ),
            self::KEY_NEED_TO_BE_REMOVED => true,
            self::KEY_HAS_FILTER => null
        ),
        array(
            self::KEY_FILTER => array(
                self::KEY_FILTER_CALLBACK => 'prepend_attachment',
                self::KEY_FILTER_PRIORITY => 10,
                self::KEY_FILTER_ARGS => 1
            ),
            self::KEY_NEED_TO_BE_REMOVED => true,
            self::KEY_HAS_FILTER => null
        ),
    );

    public static function setup()
    {
        add_filter('the_content', array(self::class, 'checkTheContentFilters'), 1);
        add_filter('the_content', array(self::class, 'checkTheContentFiltersAfter'), 999);
    }

    /**
     * @param $content string Post content from WordPress
     *
     * @return string Not modified post content.
     */
    public static function checkTheContentFilters($content)
    {
        $post = SetkaEditorUtils::getPost();

        if ($post && SetkaEditorUtils::isSetkaPost($post)) {
            self::maybeRemoveWPFilters();
        }

        return $content;
    }

    /**
     * Adds removed filters back.
     *
     * @param $content string Post content from WordPress
     *
     * @return string Not modified post content.
     */
    public static function checkTheContentFiltersAfter($content)
    {
        self::maybeRestoreWPFilters();
        return $content;
    }

    /**
     * Removes default filters.
     */
    public static function maybeRemoveWPFilters()
    {
        foreach (self::$filtersList as $index => $value) {
            if ($value[self::KEY_NEED_TO_BE_REMOVED]) {
                $has_filter = has_filter('the_content', $value[self::KEY_FILTER][self::KEY_FILTER_CALLBACK]);
                if ($has_filter) {
                    self::$filtersList[$index][self::KEY_HAS_FILTER]                        = true;
                    self::$filtersList[$index][self::KEY_FILTER][self::KEY_FILTER_PRIORITY] = $has_filter;

                    remove_filter(
                        'the_content',
                        $value[self::KEY_FILTER][self::KEY_FILTER_CALLBACK],
                        $value[self::KEY_FILTER][self::KEY_FILTER_PRIORITY],
                        self::$filtersList[$index][self::KEY_FILTER][self::KEY_FILTER_PRIORITY]
                    );
                } else {
                    self::$filtersList[$index][self::KEY_HAS_FILTER] = false;
                }
            }
        }
    }

    /**
     * Restore previously removed filters.
     */
    public static function maybeRestoreWPFilters()
    {
        foreach (self::$filtersList as $index => $value) {
            if (true === $value[self::KEY_HAS_FILTER]) {
                add_filter(
                    'the_content',
                    $value[self::KEY_FILTER][self::KEY_FILTER_CALLBACK],
                    $value[self::KEY_FILTER][self::KEY_FILTER_PRIORITY],
                    $value[self::KEY_FILTER][self::KEY_FILTER_ARGS]
                );
                self::$filtersList[$index][self::KEY_HAS_FILTER] = false;
            }
        }
    }
}
