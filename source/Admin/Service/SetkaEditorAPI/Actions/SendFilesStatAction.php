<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SendFilesStatAction extends SetkaEditorAPI\Prototypes\ActionAbstract
{
    public function __construct()
    {
        $this->setMethod(Request::METHOD_POST);
        $this->setEndpoint('/api/v1/wordpress/files/event.json');
    }

    public function handleResponse(): void
    {
        switch ($this->response->getStatusCode()) {
            case Response::HTTP_OK:
                // For now we don't check anything because we don't use this data in plugin.
                break;

            default:
                $this->addError(new Errors\UnknownError());
                break;
        }
    }
}
