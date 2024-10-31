<?php
namespace Setka\Editor\Service\Manager\Exceptions;

use Setka\Editor\Exceptions\DomainException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidConfigException extends DomainException
{
    /**
     * @var ?ConstraintViolationListInterface
     */
    private $constraintViolationList;

    public function __construct(
        ?ConstraintViolationListInterface $constraintViolationList = null,
        ?\Throwable $previous = null
    ) {
        $this->constraintViolationList = $constraintViolationList;
        parent::__construct('', 0, $previous);
    }

    /**
     * @return ?ConstraintViolationListInterface
     */
    public function getConstraintViolationList(): ?ConstraintViolationListInterface
    {
        return $this->constraintViolationList;
    }
}
