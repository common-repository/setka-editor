<?php
namespace Setka\Editor\Service\Manager\Stacks;

class PostTypesAndStatusFactory implements StackItemFactoryInterface
{
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param array $postTypes
     * @param array $postStatuses
     * @param int $postsPerPage
     */
    public function __construct(array $postTypes, array $postStatuses, int $postsPerPage = 1)
    {
        $this->arguments = array(
            'post_type' => $postTypes,
            'posts_per_page' => $postsPerPage,
            'post_status' => $postStatuses,
            'orderby' => 'ID',

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,
        );
    }

    /**
     * @inheritDoc
     */
    public function createQuery(): \WP_Query
    {
        return new \WP_Query($this->arguments);
    }
}
