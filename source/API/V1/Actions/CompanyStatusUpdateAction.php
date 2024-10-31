<?php
namespace Setka\Editor\API\V1\Actions;

use Korobochkin\WPKit\AlmostControllers\ActionInterface;
use Setka\Editor\Admin\Cron\SyncAccountCronEvent;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Actions\GetCompanyStatusAction;
use Setka\Editor\API\V1\AbstractExtendedAction;
use Setka\Editor\API\V1\Errors;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CompanyStatusUpdateAction
 */
class CompanyStatusUpdateAction extends AbstractExtendedAction implements ActionInterface
{
    /**
     * CompanyStatusUpdateAction constructor.
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
        $request  = $this->getRequest();
        $response = $this->getResponse();

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
         */
        $data = $request->request->get('data');

        // Fix white label flag
        $features = $data->get('features');
        if (isset($features['white_label']) && !is_bool($features['white_label'])) {
            if ('false' === $features['white_label']) {
                $features['white_label'] = false;
            } elseif ('true' === $features['white_label']) {
                $features['white_label'] = true;
            }
            $data->set('features', $features);
        }
        unset($features);

        /**
         * @var $getCompanyStatusAction GetCompanyStatusAction
         * @var $validator ValidatorInterface
         */
        $getCompanyStatusAction = new GetCompanyStatusAction();
        $validator              = $this->get('wp.plugins.setka_editor.validator');

        try {
            if ($data->has('active_until')) {
                $errors = $validator->validate($data->all(), $getCompanyStatusAction->buildConstraintsOk());
            } else {
                $errors = $validator->validate($data->all(), $getCompanyStatusAction->buildConstraintsForbidden());
            }
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

        $subscriptionStatusOption        = new Options\SubscriptionStatusOption();
        $subscriptionPaymentStatusOption = new Options\SubscriptionPaymentStatusOption();
        $planFeaturesOption              = new Options\PlanFeatures\PlanFeaturesOption();
        $subscriptionActiveUntilOption   = new Options\SubscriptionActiveUntilOption();
        $syncAccountCronEvent            = new SyncAccountCronEvent();

        $syncAccountCronEvent->unScheduleAll();

        $subscriptionStatusOption->updateValue($data->get('status'));
        $subscriptionPaymentStatusOption->updateValue($data->get('payment_status'));
        $planFeaturesOption->updateValue($data->get('features'));
        if ($data->has('active_until')) {
            $subscriptionActiveUntilOption->updateValue($data->get('active_until'));
            $datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $subscriptionActiveUntilOption->get());
            if (is_a($datetime, \DateTime::class)) {
                $syncAccountCronEvent->setTimestamp($datetime->getTimestamp());
                $syncAccountCronEvent->schedule();
            }
        } else {
            $subscriptionActiveUntilOption->delete();
        }

        $response->setStatusCode(Response::HTTP_OK);

        return $this;
    }
}
