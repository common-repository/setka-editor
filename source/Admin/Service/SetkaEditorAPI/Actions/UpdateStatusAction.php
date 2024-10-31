<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateStatusAction extends SetkaEditorAPI\Prototypes\ActionAbstract
{
    public function __construct()
    {
        $this->setMethod(Request::METHOD_POST);
        $this->setEndpoint('/api/v1/wordpress/setup_statuses/update_status.json');
    }

    public function handleResponse(): void
    {
        switch ($this->response->getStatusCode()) {
            case Response::HTTP_OK:
            case Response::HTTP_CREATED:
            case Response::HTTP_ACCEPTED:
            case Response::HTTP_NON_AUTHORITATIVE_INFORMATION:
            case Response::HTTP_NO_CONTENT:
            case Response::HTTP_RESET_CONTENT:
            case Response::HTTP_PARTIAL_CONTENT:
            case Response::HTTP_MULTI_STATUS:
            case Response::HTTP_ALREADY_REPORTED:
            case Response::HTTP_IM_USED:
                // For now we don't check anything because we don't use this data
                break;

            case Response::HTTP_UNPROCESSABLE_ENTITY: // Wrong `status` field in request.
                $this->errors->add(new Errors\InvalidRequestDataError());
                break;

            default:
                $this->errors->add(new Errors\UnknownError());
                break;
        }
    }
}
