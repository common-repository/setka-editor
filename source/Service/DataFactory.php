<?php
namespace Setka\Editor\Service;

use Korobochkin\WPKit\DataComponents\NodeFactory;
use Korobochkin\WPKit\DataComponents\NodeInterface;

/**
 * @deprecated
 */
class DataFactory extends NodeFactory
{
    /**
     * Creates an instance of NodeInterface
     *
     * @param string $className Class implemented NodeInterface.
     * @param array|null $arguments
     *
     * @return NodeInterface Instance of passed class with validator and constraints.
     *
     * @throws \ReflectionException
     */
    public function createWithArgs($className, array $arguments = null)
    {
        /**
         * @var $dataComponent NodeInterface
         */
        $reflection    = new \ReflectionClass($className);
        $dataComponent = $reflection->newInstanceArgs($arguments);

        $dataComponent
            ->setValidator($this->validator)
            ->setConstraint($dataComponent->buildConstraint());

        return $dataComponent;
    }
}
