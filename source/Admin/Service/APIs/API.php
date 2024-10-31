<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class API
{
    /**
     * @var ClientInterface Interface to send HTTP requests
     */
    protected $client;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $options;

    public function __construct(ClientInterface $client, ValidatorInterface $validator, array $options = array())
    {
        $this->client = $client;
        $this->validator = $validator;

        if ($options) {
            $this->configureOptions($resolver = new OptionsResolver());
            $this->options = $resolver->resolve($options);
        } else {
            $this->options = $options;
        }
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->setRequired('endpoint')
            ->setAllowedTypes('endpoint', 'string')

            ->setDefault('basic_auth_login', false)
            ->setAllowedTypes('basic_auth_login', array('bool', 'string'))

            ->setDefault('basic_auth_password', false)
            ->setAllowedTypes('basic_auth_password', array('bool', 'string'));
    }

    public function request(ActionInterface $action): void
    {
        $action->setErrors(new ConstraintViolationList());
        $action->setAPI($this);

        try {
            $action->configureAndResolveRequestDetails();
            $response = $this->client->request($this->getRequestUrl($action), $this->getRequestDetails($action));
        } catch (\Exception $exception) {
            throw new Exceptions\InvalidRequestDataException('', 0, $exception);
        }

        // Can't connect or something similar (error from Curl)
        if (is_wp_error($response)) {
            /**
             * @var $response \WP_Error
             */
            throw new Exceptions\ConnectionException($response);
        }

        // Convert WordPress response into Symfony Response
        try {
            $responseForAction = ResponseFactory::create($response);
        } catch (\Exception $exception) {
            throw new Exceptions\UnexpectedValueException('', 0, $exception);
        }

        $action->setResponse($responseForAction);

        try {
            $action->handleResponse();
        } catch (\Exception $exception) {
            throw new Exceptions\HandleResponseException('', 0, $exception);
        }
    }

    /**
     * Returns an URL with desired parameters (query args-attrs) to make a request.
     *
     * I'm not using https://github.com/thephpleague/uri or http_build_url() because
     * they require additional libs in PHP such as ext-intl. This libs (additional dependencies)
     * not good for WordPress plugin.
     *
     * @param ActionInterface $action
     * @return string Request URL.
     */
    protected function getRequestUrl(ActionInterface $action): string
    {
        $url = $this->options['endpoint'];

        $endpoint = $action->getEndpoint();
        $endpoint = ltrim($endpoint, '/');
        $endpoint = '/' . $endpoint;

        $url .= $endpoint;

        return add_query_arg($this->getRequestUrlQuery($action), $url);
    }

    protected function getRequestUrlQuery(ActionInterface $action): array
    {
        return array_merge_recursive(
            $this->getRequestUrlQueryRequired(),
            $action->getRequestUrlQuery()
        );
    }

    protected function getRequestUrlQueryRequired(): array
    {
        return array();
    }

    protected function getRequestDetails(ActionInterface $action): array
    {
        return array_merge_recursive(
            $this->getRequestDetailsRequired($action),
            $action->getRequestDetails()
        );
    }

    public function getRequestDetailsRequired(ActionInterface $action): array
    {
        $details =  array(
            'method' => $action->getMethod(),
        );


        if ($this->options['basic_auth_login'] && $this->options['basic_auth_password']) {
            $details['headers'] = array(
                'Authorization' => 'Basic ' . base64_encode($this->options['basic_auth_login'] . ':' . $this->options['basic_auth_password'])
            );
        }

        return $details;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
