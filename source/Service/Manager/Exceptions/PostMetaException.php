<?php
namespace Setka\Editor\Service\Manager\Exceptions;

use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Setka\Editor\Exceptions\Exception;

class PostMetaException extends Exception
{
    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * @var PostMetaInterface
     */
    private $postMeta;

    /**
     * PostMetaException constructor.
     * @param \WP_Post $post
     * @param PostMetaInterface $postMeta
     */
    public function __construct(\WP_Post $post, PostMetaInterface $postMeta)
    {
        parent::__construct();
        $this->post     = $post;
        $this->postMeta = $postMeta;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return array(
            'post' => $this->getPost(),
            'meta' => array(
                'name' => $this->postMeta->getName(),
                'value' => $this->postMeta->get(),
                'isValid' => $this->postMeta->isValid(),
            ),
        );
    }

    /**
     * @return array
     */
    private function getPost()
    {
        $post = $this->post->to_array();

        $post['post_content_length'] = strlen($this->post->post_content);
        $post['post_content']        = substr($this->post->post_content, 0, 100);

        return $post;
    }
}
