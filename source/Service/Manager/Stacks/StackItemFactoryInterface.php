<?php
namespace Setka\Editor\Service\Manager\Stacks;

/**
 * Should return WP_Query instance with one result from stack.
 */
interface StackItemFactoryInterface
{
    /**
     * @return \WP_Query
     */
    public function createQuery(): \WP_Query;
}
