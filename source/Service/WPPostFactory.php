<?php
namespace Setka\Editor\Service;

use Setka\Editor\Admin\Service\WPQueryFactory;

class WPPostFactory
{
    /**
     * @return \WP_Post
     * @throws \UnexpectedValueException
     */
    public static function createFromGlobals()
    {
        $post = get_post();

        if (is_a($post, \WP_Post::class)) {
            return $post;
        }

        throw new \UnexpectedValueException();
    }

    /**
     * @param mixed $post
     *
     * @return bool
     */
    public static function isValidPost($post)
    {
        if (!is_a($post, \WP_Post::class)) {
            return false;
        }

        /**
         * @var $post \WP_Post
         */
        return is_int($post->ID) && $post->ID > 0;
    }

    /**
     * @param int $id
     *
     * @return \WP_Post
     * @throws \Exception
     */
    public static function createStandaloneById($id)
    {
        return self::createByIdAndPostType($id, SetkaPostTypes::getPostTypes(SetkaPostTypes::GROUP_STANDALONE));
    }

    /**
     * @param int $id
     * @param array $postTypes
     *
     * @return \WP_Post
     * @throws \Exception
     */
    private static function createByIdAndPostType($id, array $postTypes)
    {
        $query = WPQueryFactory::createByIdAndPostType($id, $postTypes);
        if (1 === (int) $query->found_posts) {
            $post = $query->next_post();
            if (self::isValidPost($post)) {
                return $post;
            }
        }
        throw new \Exception('Requested post not found. ID = ' . $id);
    }
}
