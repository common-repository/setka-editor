<?php
declare(strict_types=1);

namespace Setka\Editor\Admin\Service\APIs;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ActionInterface
{
    public function getMethod(): string;

    public function setMethod(string $method): void;

    public function getEndpoint(): string;

    public function setEndpoint(string $endpoint): void;

    public function setAPI(API $API): void;

    public function getResponse(): ?Response;

    public function setResponse(Response $response): void;

    public function getErrors(): ConstraintViolationListInterface;

    public function setErrors(ConstraintViolationListInterface $errors): void;

    public function addError(ConstraintViolationInterface $error): void;

    public function getRequestUrlQuery(): array;

    public function getRequestDetails(): array;

    public function setRequestDetails(array $requestDetails): void;

    public function configureAndResolveRequestDetails(): void;

    public function handleResponse(): void;
}
