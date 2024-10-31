<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class ActionAbstract implements ActionInterface
{
    /**
     * @var string HTTP method.
     */
    protected $method;

    /**
     * @var string URL.
     */
    protected $endpoint;

    /**
     * @var API
     */
    protected $API;

    /**
     * @var ?Response
     */
    protected $response;

    /**
     * @var ConstraintViolationListInterface
     */
    protected $errors;

    /**
     * @var array The request details data
     */
    protected $requestDetails = array();

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function setAPI(API $API): void
    {
        $this->API = $API;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getErrors(): ConstraintViolationListInterface
    {
        return $this->errors;
    }

    public function setErrors(ConstraintViolationListInterface $errors): void
    {
        $this->errors = $errors;
    }

    public function addError(ConstraintViolationInterface $error): void
    {
        $this->errors->add($error);
    }

    public function getRequestUrlQuery(): array
    {
        return array();
    }

    public function getRequestDetails(): array
    {
        return $this->requestDetails;
    }

    public function setRequestDetails(array $requestDetails): void
    {
        $this->requestDetails = $requestDetails;
    }

    public function configureAndResolveRequestDetails(): void
    {
    }
}
