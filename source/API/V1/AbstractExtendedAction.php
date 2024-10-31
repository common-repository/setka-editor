<?php
namespace Setka\Editor\API\V1;

use Korobochkin\WPKit\AlmostControllers\AbstractAction;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class AbstractExtendedAction
 */
abstract class AbstractExtendedAction extends AbstractAction
{
    /**
     * @var $responseData ParameterBag
     */
    protected $responseData;

    /**
     * @return ParameterBag
     */
    public function getResponseData()
    {
        return $this->responseData;
    }

    /**
     * @param ParameterBag $responseData
     *
     * @return $this
     */
    public function setResponseData(ParameterBag $responseData)
    {
        $this->responseData = $responseData;
        return $this;
    }
}
