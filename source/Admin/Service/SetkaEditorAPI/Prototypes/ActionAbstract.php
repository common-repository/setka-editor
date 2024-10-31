<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Prototypes;

use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class ActionAbstract extends \Setka\Editor\Admin\Service\APIs\ActionAbstract implements ActionInterface
{
    /**
     * @var boolean Flag which shows should we authenticate this request or not.
     */
    protected $authenticationRequired = true;

    public function isAuthenticationRequired(): bool
    {
        return $this->authenticationRequired;
    }

    public function setAuthenticationRequired(bool $authenticationRequired): void
    {
        $this->authenticationRequired = $authenticationRequired;
    }

    /**
     * @param $violations ConstraintViolationListInterface
     * @throws \Exception If list have violations.
     */
    public function violationsToException(ConstraintViolationListInterface $violations): void
    {
        if (count($violations) !== 0) {
            throw new \Exception();
        }
    }
}
