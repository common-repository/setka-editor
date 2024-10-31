<?php
namespace Setka\Editor\Admin\Pages\Uninstall;

class SetkaEditorUtils
{
    /**
     * @param mixed $post
     *
     * @return bool
     */
    public static function isValidPost($post)
    {
        return is_a($post, \WP_Post::class) && is_int($post->ID) && $post->ID > 0;
    }

    /**
     * @param mixed $query
     *
     * @return bool
     */
    public static function isValidQuery($query)
    {
        return is_a($query, \WP_Query::class);
    }

    /**
     * @param \WP_Post $post
     *
     * @return bool
     */
    public static function isSetkaPost(\WP_Post $post)
    {
        return '1' === get_post_meta($post->ID, '_setka_editor_use_editor', true);
    }

    /**
     * @return \WP_Post|false
     */
    public static function getPost()
    {
        global $post;
        return self::isValidPost($post) ? $post : false;
    }

    /**
     * @return \WP_Query|false
     */
    public static function getQuery()
    {
        global $wp_query;
        return self::isValidQuery($wp_query) ? $wp_query : false;
    }

    public static function getPostFromQuery()
    {
        $query = self::getQuery();
        if (!$query) {
            return false;
        }

        $post = current($query->posts);
        return self::isValidPost($post) ? $post : false;
    }
}
