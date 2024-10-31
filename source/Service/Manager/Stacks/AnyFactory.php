<?php
namespace Setka\Editor\Service\Manager\Stacks;

use Setka\Editor\Service\PostStatuses;

class AnyFactory extends PostTypesAndStatusFactory implements AnyFactoryInterface
{
    /**
     * @param array $postTypes
     * @param int $postsPerPage
     */
    public function __construct(array $postTypes, int $postsPerPage = 1)
    {
        parent::__construct($postTypes, PostStatuses::getAll(), $postsPerPage);
    }
}
