<?php
namespace Setka\Editor\API\V1\Actions;

use Korobochkin\WPKit\AlmostControllers\ActionInterface;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Service\FilesManager\FilesServiceManager;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Actions\GetFilesAction;
use Setka\Editor\API\V1\Errors;
use Setka\Editor\API\V1;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateFilesAction extends V1\AbstractExtendedAction implements ActionInterface
{
    public function __construct()
    {
        $this->setEnabledForNotLoggedIn(true);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $request  = $this->getRequest();
        $response = $this->getResponse();
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);

        if ($request->getMethod() !== Request::METHOD_POST) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolationsList()->add(new Errors\HttpMethodError());
            return $this;
        }

        if (is_array($request->request->get('data'))) {
            $request->request->set(
                'data',
                new ParameterBag($request->request->get('data'))
            );
        }

        if (!is_a($request->request->get('data'), ParameterBag::class)) {
            $response->setStatusCode($response::HTTP_BAD_REQUEST);
            $this->getViolationsList()->add(new Errors\RequestDataError());
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
         * @var $data ParameterBag
         * @var $getFilesAction GetFilesAction
         * @var $validator ValidatorInterface
         */
        $data           = $request->request->get('data');
        $validator      = $this->get('wp.plugins.setka_editor.validator');
        $getFilesAction = new GetFilesAction();

        try {
            $errors = $validator->validate($data->get('files'), $getFilesAction->buildConstraintsOk());
        } catch (\Exception $exception) {
            $this->getViolationsList()->add(new Errors\RequestDataError());
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $this;
        }

        if (count($errors) !== 0) {
            $this->getViolationsList()->addAll($errors);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $this;
        }

        $filesOption = new Options\Files\FilesOption();
        $filesOption->updateValue($data->get('files'));

        $this->resetAllDownloadsCounters();

        if (count($this->getViolationsList()) === 0) {
            $response->setStatusCode($response::HTTP_OK);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function resetAllDownloadsCounters()
    {
        try {
            $this->get(FilesServiceManager::class)->restart();
        } catch (\Exception $exception) {
            $this->getViolationsList()->add(new Errors\SiteError());
            $this->getResponse()->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
        return $this;
    }
}
