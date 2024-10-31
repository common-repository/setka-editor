<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI;

use Setka\Editor\Admin\Service\APIs\ActionInterface;
use Setka\Editor\Admin\Service\APIs\Exceptions\ConnectionException;
use Setka\Editor\Admin\Service\APIs\Exceptions\HandleResponseException;
use Setka\Editor\Admin\Service\APIs\Exceptions\InvalidRequestDataException;
use Setka\Editor\Admin\Service\APIs\Exceptions\UnexpectedValueException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetkaEditorAPI extends \Setka\Editor\Admin\Service\APIs\API
{
    /**
     * @var AuthCredits
     */
    private $authCredits;

    public function setAuthCredits(AuthCredits $authCredits): void
    {
        $this->authCredits = $authCredits;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver
            ->setRequired('app_version')
            ->setAllowedTypes('app_version', 'string')

            ->setRequired('plugin_version')
            ->setAllowedTypes('plugin_version', 'string')

            ->setRequired('domain')
            ->setAllowedTypes('domain', 'string')

            ->setDefault('endpoint', Endpoints::API);
    }

    public function request(ActionInterface $action): void
    {
        $action->setAPI($this);

        try {
            parent::request($action);
        } catch (\Exception $exception) {
            switch (get_class($exception)) {
                case InvalidRequestDataException::class:
                    $error = new Errors\InvalidRequestDataError();
                    $action->addError($error);
                    break;

                case ConnectionException::class:
                    /**
                     * @var $exception ConnectionException
                     */
                    $action->addError(
                        new Errors\ConnectionError(array(
                            'error' => $exception->getError(),
                        ))
                    );
                    break;

                case UnexpectedValueException::class:
                case HandleResponseException::class:
                default:
                    $action->addError(new Errors\ResponseError());
                    break;
            }
        }
    }

    protected function getRequestUrlQueryRequired(): array
    {
        return array(
            'app_version' => $this->options['app_version'],
            'domain'      => $this->options['domain'],
        );
    }

    public function getRequestDetailsRequired(ActionInterface $action): array
    {
        $details = parent::getRequestDetailsRequired($action);

        $details['body'] = array(
            'plugin_version' => $this->options['plugin_version'],
        );

        if ($action->isAuthenticationRequired()) {
            $details['body']['token'] = $this->authCredits->getToken();
        }

        return $details;
    }
}
