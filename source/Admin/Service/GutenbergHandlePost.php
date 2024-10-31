<?php
namespace Setka\Editor\Admin\Service;

use Setka\Editor\Service\Gutenberg\EditorGutenbergModule;

class GutenbergHandlePost
{
    /**
     * @var Screen
     */
    protected $screen;

    /**
     * @var EditorGutenbergModule
     */
    protected $editorGutenbergModule;

    /**
     * GutenbergHandlePost constructor.
     * @param Screen $screen
     * @param $editorGutenbergModule EditorGutenbergModule
     */
    public function __construct(Screen $screen, $editorGutenbergModule)
    {
        $this->screen                = $screen;
        $this->editorGutenbergModule = $editorGutenbergModule;
    }

    /**
     * @param \WP_REST_Response $response The response object.
     * @param \WP_Post          $post     Post object.
     * @param \WP_REST_Request  $request  Request object.
     *
     * @return \WP_REST_Response
     */
    public function maybeConvertClassicEditorPost($response, $post, $request)
    {
        if (!$this->screen->isBlockEditor()) {
            return $response;
        }

        try {
            $block = $this->editorGutenbergModule->convertFromClassicToGutenberg($post);

            $data                             = $response->get_data();
            $data['content']['raw']           = $block->getRaw();
            $data['content']['rendered']      = $block->getRendered();
            $data['content']['block_version'] = 1;

            $response->set_data($data);
        } catch (\Exception $exception) {
        }

        return $response;
    }
}
