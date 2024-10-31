<?php
namespace Setka\Editor\Service\Manager\Stacks;

use Setka\Editor\Service\PostStatuses;

class PreviousConfigsFactory implements PreviousConfigsFactoryInterface
{
    /**
     * @var string
     */
    private $postType;

    /**
     * @var string
     */
    private $beforeGMT;

    /**
     * @param string $postType
     * @param string $beforeGMT
     */
    public function __construct(string $postType, string $beforeGMT)
    {
        $this->postType  = $postType;
        $this->beforeGMT = $beforeGMT;
    }

    public function createQuery(): \WP_Query
    {
        return new \WP_Query(array(
            'post_type' => $this->postType,
            'posts_per_page' => 5,
            'post_status' => array(
                // Almost all post statuses except PostStatuses::ARCHIVE
                PostStatuses::PUBLISH,
                PostStatuses::DRAFT,
                PostStatuses::PENDING,
                PostStatuses::FUTURE,
                PostStatuses::TRASH,
            ),

            'date_query' => array(
                'before' => $this->beforeGMT,
                'inclusive' => false,
                'column' => 'post_date_gmt',
            ),

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,
        ));
    }
}
