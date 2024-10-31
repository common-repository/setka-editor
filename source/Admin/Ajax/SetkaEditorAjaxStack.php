<?php
namespace Setka\Editor\Admin\Ajax;

use Korobochkin\WPKit\AlmostControllers\AjaxStack;
use Symfony\Component\HttpFoundation\JsonResponse;

class SetkaEditorAjaxStack extends AjaxStack
{
    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $this->setResponse(new JsonResponse());
        return parent::handleRequest();
    }
}
