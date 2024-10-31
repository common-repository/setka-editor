<?php
namespace Setka\Editor\Entities;

class WPPostProxyPost extends Post implements PostInterface
{
    /**
     * @var \WP_Post
     */
    protected $originalPost;

    /**
     * WPPostProxyPost constructor.
     *
     * @param \WP_Post $originalPost
     */
    public function __construct(\WP_Post $originalPost)
    {
        $this->originalPost = $originalPost;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->originalPost->post_content;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->originalPost->post_content = $content;
        return $this;
    }
}
