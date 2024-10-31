<?php
namespace Setka\Editor\Service\Manager;

use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\Exceptions\PostMetaException;

trait PostOperationsTrait
{
    /**
     * @param \WP_Post $post
     *
     * @return $this
     * @throws PostException
     */
    protected function deletePost(\WP_Post $post)
    {
        return $this->isPostDeleted(wp_delete_post($post->ID), $post);
    }

    /**
     * Check if post was deleted.
     *
     * @param $result mixed Value returned by wp_delete_post.
     * @param $post \WP_Post Post which was tried to remove.
     *
     * @throws PostException
     *
     * @return $this
     */
    protected function isPostDeleted($result, \WP_Post $post)
    {
        if (is_a($result, \stdClass::class) || is_a($result, \WP_Post::class)) {
            return $this;
        }
        throw new PostException('Post not deleted. ID: ' . $post->ID);
    }

    /**
     * @param array|\WP_Post $post
     *
     * @return int
     * @throws PostException
     */
    protected function insertPost($post)
    {
        $id = $this->isPostSaved(wp_insert_post($post, true));
        if (is_a($post, \WP_Post::class)) {
            $post->ID = $id;
        }
        return $id;
    }

    /**
     * @param \WP_Post $post
     * @return int
     * @throws PostException
     */
    protected function updatePost(\WP_Post $post)
    {
        $post->ID = $this->isPostSaved(wp_update_post($post));
        return $post->ID;
    }

    /**
     * Check that post was created (or updated).
     *
     * @param $id int|\WP_Error Result of wp_update_post()
     * @throws PostException If post not updated (or created).
     *
     * @return int
     */
    protected function isPostSaved($id)
    {
        if (is_int($id) && $id > 0) {
            return $id;
        } elseif (is_wp_error($id)) {
            $message =  ' ' . $id->get_error_code() . ': ' . $id->get_error_message();
        } else {
            $message = '';
        }
        throw new PostException('Post was not saved.' . $message);
    }
}
