<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

class GetCompanyStatusAction extends SetkaEditorAPI\Prototypes\ActionAbstract
{
    public function __construct()
    {
        $this->setMethod(Request::METHOD_GET);
        $this->setEndpoint('/api/v1/wordpress/company_status.json');
    }

    public function handleResponse(): void
    {
        switch ($this->response->getStatusCode()) {
            case Response::HTTP_UNAUTHORIZED: // Token not found
                $this->addError(new Errors\ServerUnauthorizedError());
                break;

            case Response::HTTP_OK:
                $this->validateOk($this->response->getContent());
                break;

            case Response::HTTP_FORBIDDEN: // Canceled subscription
                $this->validateForbidden($this->response->getContent());
                break;

            default:
                $this->addError(new Errors\UnknownError());
                break;
        }
    }

    /**
     * Validates HTTP 200 data.
     *
     * @param ParameterBag $content Parameters to validate.
     */
    private function validateOk(ParameterBag $content): void
    {
        try {
            $results = $this->API->getValidator()->validate(
                $content->all(),
                $this->buildConstraintsOk()
            );
            $this->getErrors()->addAll($results);
        } catch (\Exception $exception) {
            $this->addError(new Errors\ResponseBodyInvalidError());
        }
    }

    /**
     * Validates HTTP 403 data.
     *
     * @param ParameterBag $content Parameters to validate.
     */
    private function validateForbidden(ParameterBag $content): void
    {
        try {
            $results = $this->API->getValidator()->validate(
                $content->all(),
                $this->buildConstraintsForbidden()
            );
            $this->getErrors()->addAll($results);
        } catch (\Exception $exception) {
            $this->addError(new Errors\ResponseBodyInvalidError());
        }
    }

    public function buildConstraintsOk(): Constraints\Collection
    {
        $activeUntil = new Options\SubscriptionActiveUntilOption();

        $constraints                         = $this->buildConstraintsForbidden();
        $constraints->fields['active_until'] = new Constraints\Required($activeUntil->buildConstraint());

        return $constraints;
    }

    public function buildConstraintsForbidden(): Constraints\Collection
    {
        $statusOption        = new Options\SubscriptionStatusOption();
        $paymentStatusOption = new Options\SubscriptionPaymentStatusOption();
        $planFeatures        = new Options\PlanFeatures\PlanFeaturesOption();

        return new Constraints\Collection(array(
            'fields' => array(
                'status'         => new Constraints\Required($statusOption->buildConstraint()),
                'payment_status' => new Constraints\Required($paymentStatusOption->buildConstraint()),
                'features'       => new Constraints\Required($planFeatures->buildConstraint()),
            ),
            'allowExtraFields' => true,
        ));
    }
}
