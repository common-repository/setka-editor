<?php
namespace Setka\Editor\Admin\Service;

class WPQueryParametersFactory
{
    /**
     * @var array
     */
    private $params;

    /**
     * WPQueryParametersFactory constructor.
     *
     * @param array|null $params
     */
    public function __construct(array $params = array())
    {
        $this->params = $params;
    }

    /**
     * @return \WP_Query
     */
    public function create()
    {
        return new \WP_Query($this->params);
    }

    /**
     * @return \WP_Query
     */
    public function __invoke()
    {
        return $this->create();
    }
}
