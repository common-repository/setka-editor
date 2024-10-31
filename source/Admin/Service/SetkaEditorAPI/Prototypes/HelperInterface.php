<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Prototypes;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;

interface HelperInterface
{
    /**
     * Add single error to errors list.
     *
     * @param ConstraintViolationInterface $error Error to add.
     */
    public function addError(ConstraintViolationInterface $error): void;

    /**
     * @return Constraint|Constraint[]
     */
    public function getResponseConstraints(): array;

    /**
     * @return Constraint|Constraint[]
     */
    public function buildResponseConstraints(): array;

    public function handleResponse(): void;
}
