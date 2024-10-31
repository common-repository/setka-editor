<?php
namespace Setka\Editor\Service\Manager\Stacks;

use Setka\Editor\Service\PostStatuses;

class DraftFactory extends PostTypesAndStatusFactory implements DraftFactoryInterface
{
    /**
     * @param array $postTypes
     * @param int $postsPerPage
     */
    public function __construct(array $postTypes, int $postsPerPage = 1)
    {
        parent::__construct($postTypes, array(PostStatuses::DRAFT), $postsPerPage);
    }
}
