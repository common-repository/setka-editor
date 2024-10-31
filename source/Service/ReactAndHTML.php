<?php
namespace Setka\Editor\Service;

class ReactAndHTML
{
    /**
     * @var \WP_REST_Response
     */
    private $response;

    /**
     * @var \WP_Post
     */
    private $post;

    /**
     * @var string
     */
    private $renderedContent;

    /**
     * @param \WP_REST_Response $response The response object.
     * @param \WP_Post          $post     Post object.
     * @param \WP_REST_Request  $request  Request object.
     */
    public function run(\WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request)
    {
        $this->response = $response;
        $this->post     = $post;

        if ($this->isResponseValid()) {
            $values = array(
                &$this->response->data['content']['raw'],
                &$this->response->data['content']['rendered'],
            );

            foreach ($values as &$value) {
                $value = $this->normalize($value);
            }
        }

        return $response;
    }

    /**
     * @param string $content
     * @return bool True if changes were made.
     */
    private function normalize(&$content)
    {
        $result = preg_replace(
            array(
                '/(<(img|hr|br|embed|input|area|col|source|track|wbr)[^>]*)\w*\/(>)/mU',
                '/(<[img][^>]*)srcSet(=.*>)/mU'
            ),
            array(
                '$1>',
                '$1srcset$2'
            ),
            $content
        );

        return is_string($result) ? $result : false;
    }

    /**
     * @return bool
     */
    private function isResponseValid()
    {
        return isset($this->response->data['content']['raw']) &&
               isset($this->response->data['content']['rendered']) &&
               $this->post->post_content === $this->response->data['content']['raw'];
    }
}
