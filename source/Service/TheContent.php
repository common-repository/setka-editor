<?php
namespace Setka\Editor\Service;

use Setka\Editor\PostMetas\UseEditorPostMeta;

/**
 * Removes some filters before output the_content()
 * and then restore them back for the upcoming posts in loop.
 *
 * Because Setka Editor wrap all content into <p> and other HTML tags automaticly
 * there is no need to parse post content with wpautop(),
 * shortcode_unautop(), prepend_attachment() functions.
 * Must correct working with loop inside other loop.
 *
 * Class TheContent
 * @package Setka\Editor\Service
 */
class TheContent
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

    /**
     * Simply check if post created with Grid Editor. If yes - remove wpautop() an other similar filters
     * which formatting entry content. We don't need this because Grid Editor already formatting content properly.
     *
     * @since 0.0.1
     *
     * @param $content string Post content from WordPress
     *
     * @return string Not modified post content.
     */
    public static function checkTheContentFilters($content)
    {
        global $post;

        if (!WPPostFactory::isValidPost($post)) {
            return $content;
        }

        $useEditorPostMeta = new UseEditorPostMeta();
        $useEditorPostMeta->setPostId($post->ID);

        if ($useEditorPostMeta->get()) {
            self::maybeRemoveWPFilters();
        }

        return $content;
    }

    /**
     * Adds removed filters back.
     *
     * @since 0.0.1
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
     *
     * @since 0.0.1
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
     *
     * @since 0.0.1
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
