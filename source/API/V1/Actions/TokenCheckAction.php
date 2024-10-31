<?php
namespace Setka\Editor\API\V1\Actions;

use Korobochkin\WPKit\AlmostControllers\ActionInterface;
use Setka\Editor\API\V1\AbstractExtendedAction;
use Setka\Editor\Plugin;
use Setka\Editor\API\V1\Errors;
use Setka\Editor\Admin\Options\EditorVersionOption;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TokenCheckAction
 */
class TokenCheckAction extends AbstractExtendedAction implements ActionInterface
{
    /**
     * TokenCheckAction constructor.
     */
    public function __construct()
    {
        $this->setEnabledForNotLoggedIn(true);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $request      = $this->getRequest();
        $response     = $this->getResponse();
        $responseData = $this->getResponseData();

        if ($request->getMethod() !== Request::METHOD_POST) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolationsList()->add(new Errors\HttpMethodError());
            return $this;
        }

        /**
         * @var $account SetkaEditorAccount
         */
        $account = $this->get(SetkaEditorAccount::class);

        if (!$account->isLoggedIn()) {
            $this->getViolationsList()->add(new Errors\SiteError());
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $this;
        }

        if ($account->getTokenOption()->get() !== $request->request->get('token')) {
            $this->getViolationsList()->add(new Errors\AuthenticationError());
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $this;
        }

        /**
         * @var $editorVersionOption EditorVersionOption
         */
        $editorVersionOption = $this->get(EditorVersionOption::class);

        $responseData->set('status', 'The license key is valid.');
        $responseData->set('plugin_version', Plugin::VERSION);
        $responseData->set('content_editor_version', $editorVersionOption->get());
        $response->setStatusCode(Response::HTTP_OK);

        return $this;
    }
}
