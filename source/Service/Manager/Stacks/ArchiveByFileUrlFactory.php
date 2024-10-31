<?php
namespace Setka\Editor\Service\Manager\Stacks;

use Setka\Editor\PostMetas\OriginUrlPostMeta;

class ArchiveByFileUrlFactory extends ArchiveFactory
{
    public function __construct(array $postTypes, OriginUrlPostMeta $originUrlPostMeta)
    {
        parent::__construct($postTypes);
        $this->arguments['meta_key'] = $originUrlPostMeta->getName();
    }

    public function createQueryByPostTypeAndFileUrl(string $postType, string $url): \WP_Query
    {
        $arguments = $this->arguments;

        $arguments['post_type']    = $postType;
        $arguments['meta_value']   = $url; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
        $arguments['meta_compare'] = '=';

        return new \WP_Query($arguments);
    }
}
