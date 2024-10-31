<?php
namespace Setka\Editor\API\V1;

use Korobochkin\WPKit\AlmostControllers\ActionInterface;
use Korobochkin\WPKit\AlmostControllers\Exceptions\ActionNotFoundException;
use Korobochkin\WPKit\AlmostControllers\Exceptions\UnauthorizedException;
use Korobochkin\WPKit\AlmostControllers\HttpStack;
use Setka\Editor\Admin\Options\WebhooksEndpointOption;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\ConstraintViolationList;

class SetkaEditorPluginHttpStack extends HttpStack
{
    /**
     * @var WebhooksEndpointOption
     */
    private $webhooksEndpointOption;

    /**
     * @inheritdoc
     */
    public function register()
    {
        if (empty($this->actions)) {
            throw new \LogicException('You need set actions before call register method.');
        }

        if (WebhooksEndpointOption::TYPE_AJAX === $this->webhooksEndpointOption->get()) {
            $actionPrefix = 'wp_ajax_nopriv_';
        } else {
            $actionPrefix = 'admin_post_nopriv_';
        }

        foreach ($this->actions as $actionName => $actionClass) {
            add_action($actionPrefix . $actionName, array($this, 'handleRequest'));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function handleRequest()
    {
        $this->setResponse(new JsonResponse());
        return parent::handleRequest();
    }

    /**
     * @inheritdoc
     */
    public function requestManager()
    {
        // Find the requested action.
        $action = $this->request->request->get('action');
        if (is_null($action)) {
            $action = $this->request->query->get('action');
        }

        if (!is_null($action) && isset($this->actions[$action])) {
            // Initialize the action.
            if (is_string($this->actions[$action])) {
                $this->actions[$action] = new $this->actions[$action]();
            }

            $this->currentAction = $this->actions[$action];

            if (is_user_logged_in()) {
                // For signed in users.
                if (!$this->currentAction->isEnabledForLoggedIn()) {
                    throw new UnauthorizedException();
                }
            } else {
                // For not signed in users
                if (!$this->currentAction->isEnabledForNotLoggedIn()) {
                    throw new UnauthorizedException();
                }
            }

            // Action should not overwrite response object.
            $this->currentAction
                ->setContainer($this->container);

            $this->currentAction
                ->setViolationsList(new ConstraintViolationList())
                ->setRequest($this->request)
                ->setResponse($this->response)
                ->setResponseData(new ParameterBag())
                ->handleRequest();

            if (count($this->getCurrentAction()->getViolationsList()) !== 0) {
                $errors = array();
                foreach ($this->getCurrentAction()->getViolationsList() as $violation) {
                    $errors[] = (string) $violation;
                }
                $this->getCurrentAction()->getResponseData()->set('errors', $errors);
            }

            $this->getResponse()->setData($this->getCurrentAction()->getResponseData()->all());

            return $this;
        } else {
            // Not supported action or action name invalid (null).
            throw new ActionNotFoundException();
        }
    }

    /**
     * @return ActionInterface
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    /**
     * @param WebhooksEndpointOption $webhooksEndpointOption
     */
    public function setWebhooksEndpointOption(WebhooksEndpointOption $webhooksEndpointOption)
    {
        $this->webhooksEndpointOption = $webhooksEndpointOption;
    }
}
