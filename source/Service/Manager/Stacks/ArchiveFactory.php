<?php
namespace Setka\Editor\Service\Manager\Stacks;

use Setka\Editor\Service\PostStatuses;

class ArchiveFactory extends PostTypesAndStatusFactory implements ArchiveFactoryInterface
{
    /**
     * @param array $postTypes
     * @param int $postsPerPage
     */
    public function __construct(array $postTypes, int $postsPerPage = 1)
    {
        parent::__construct($postTypes, array(PostStatuses::ARCHIVE), $postsPerPage);
    }
}
