<?php
namespace Setka\Editor\Service\Manager\Stacks;

use Setka\Editor\Service\PostStatuses;

class ConfigFactory implements ConfigFactoryInterface
{
    /**
     * @var string
     */
    private $postType;

    public function __construct(string $postType)
    {
        $this->postType = $postType;
    }

    public function createQuery(): \WP_Query
    {
        return new \WP_Query(array(
            'post_type' => $this->postType,
            'post_status' => PostStatuses::PUBLISH,
            'order' => 'DESC',
            'orderby' => 'ID',
            'posts_per_page' => 1,
        ));
    }
}
