<?php
namespace Setka\Editor\Admin\Service;

use Setka\Editor\Exceptions\RuntimeException;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\Service\PostStatuses;
use Setka\Editor\Service\SetkaPostTypes;

class WPQueryFactory
{
    /**
     * @param int $id
     * @param array $postTypes
     *
     * @return \WP_Query
     */
    public static function createByIdAndPostType($id, array $postTypes): \WP_Query
    {
        return new \WP_Query(array(
            'p' => $id,
            'post_type' => $postTypes,
            'post_status' => array(
                // Almost all post statuses except PostStatuses::ARCHIVE
                PostStatuses::PUBLISH,
                PostStatuses::DRAFT,
                PostStatuses::PENDING,
                PostStatuses::FUTURE,
                PostStatuses::TRASH,
            ),
        ));
    }

    /**
     * @return \WP_Query
     */
    public static function createWhereFilesIsAny(): \WP_Query
    {
        return new \WP_Query(array(
            'post_type' => SetkaPostTypes::FILE_POST_NAME,
            'posts_per_page' => 1,
            'post_status' => PostStatuses::getAll(),

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,
        ));
    }

    /**
     * Returns \WP_Query instance with single file marked as pending.
     *
     * @return \WP_Query
     */
    public static function createWhereFilesIsPending(): \WP_Query
    {
        return new \WP_Query(array(
            'post_type' => SetkaPostTypes::FILE_POST_NAME,
            'posts_per_page' => 1,
            'post_status' => PostStatuses::PENDING,

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,
        ));
    }

    /**
     * @param string $url URL to JSON file
     *
     * @throws RuntimeException
     *
     * @return \WP_Query
     */
    public static function createThemeJSON($url): \WP_Query
    {
        self::validateUrl($url);

        $originUrlMeta = new OriginUrlPostMeta();

        return new \WP_Query(array(
            'post_type' => SetkaPostTypes::FILE_POST_NAME,
            'post_status' => PostStatuses::PUBLISH,

            'meta_key' => $originUrlMeta->getName(),
            'meta_value' => $url, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,

            'posts_per_page' => 1,
        ));
    }

    /**
     * @param string $url URL to CSS file
     *
     * @throws RuntimeException
     *
     * @return \WP_Query
     */
    public static function createThemeCSS($url): \WP_Query
    {
        self::validateUrl($url);

        $originUrlMeta = new OriginUrlPostMeta();

        return new \WP_Query(array(
            'post_type' => SetkaPostTypes::FILE_POST_NAME,
            'post_status' => PostStatuses::PUBLISH,

            'meta_key' => $originUrlMeta->getName(),
            'meta_value' => $url, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,

            'posts_per_page' => 1,
        ));
    }

    /**
     * @param mixed $url
     *
     * @throws RuntimeException
     */
    private static function validateUrl($url): void
    {
        if (is_string($url) && !empty($url)) {
            return;
        }
        throw new RuntimeException();
    }

    /**
     * @return \WP_Query
     */
    private static function createEmptyQuery(): \WP_Query
    {
        return new \WP_Query();
    }

    /**
     * @return \WP_Query
     */
    public static function getGlobalOrEmpty(): \WP_Query
    {
        global $wp_query;

        if (self::isValidAndNotEmptyQuery($wp_query)) {
            return $wp_query;
        }

        return self::createEmptyQuery();
    }

    /**
     * @param mixed $query
     *
     * @return bool
     */
    public static function isValidAndNotEmptyQuery($query): bool
    {
        if (!is_a($query, \WP_Query::class)) {
            return false;
        }

        /**
         * @var $query \WP_Query
         */
        return is_array($query->posts) && !empty($query->posts);
    }

    /**
     * @param array $postTypes
     * @return \WP_Query
     */
    public static function createWherePostTypes(array $postTypes): \WP_Query
    {
        return new \WP_Query(array(
            'post_type' => $postTypes,
            'posts_per_page' => 100,
            'orderby' => 'ID',
            'post_status' => PostStatuses::ANY,

            // Don't save result into cache since this used only by CLI.
            'cache_results' => false,
        ));
    }

    /**
     * @param string $postTypesGroup
     * @return WPQueryParametersFactory
     */
    public static function createWherePostTypeGroupFactory(string $postTypesGroup): WPQueryParametersFactory
    {
        return new WPQueryParametersFactory(array(
            'post_type' => SetkaPostTypes::getPostTypes($postTypesGroup),
            'posts_per_page' => 5,
            'post_status' => PostStatuses::ANY,
            'orderby' => 'ID',

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,
        ));
    }
}
