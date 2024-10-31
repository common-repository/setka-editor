<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Prototypes;

use Setka\Editor\Admin\Service\APIs\Response;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class HelperAbstract implements HelperInterface
{
    /**
     * @var SetkaEditorAPI\SetkaEditorAPI
     */
    protected $API;

    /**
     * @var Response
     */
    public $response;

    /**
     * @var ConstraintViolationListInterface
     */
    protected $errors;

    /**
     * @var Constraint[]
     */
    protected $responseConstraints;

    /**
     * HelperAbstract constructor.
     *
     * @param $API SetkaEditorAPI\SetkaEditorAPI
     * @param $response Response
     * @param $errors ConstraintViolationListInterface
     */
    public function __construct(SetkaEditorAPI\SetkaEditorAPI $API, Response $response, ConstraintViolationListInterface $errors)
    {
        $this->API = $API;
        $this->response = $response;
        $this->errors = $errors;
    }

    public function addError(ConstraintViolationInterface $error): void
    {
        $this->errors->add($error);
    }

    /**
     * @inheritdoc
     */
    public function getResponseConstraints(): array
    {
        if (!$this->responseConstraints) {
            $this->responseConstraints = $this->buildResponseConstraints();
        }
        return $this->responseConstraints;
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
