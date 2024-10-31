<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class GetFilesAction extends SetkaEditorAPI\Prototypes\ActionAbstract
{
    public function __construct()
    {
        $this->setMethod(Request::METHOD_GET);
        $this->setEndpoint('/api/v1/wordpress/files.json');
    }

    public function handleResponse(): void
    {
        switch ($this->response->getStatusCode()) {
            case Response::HTTP_OK:
                $this->validateOk($this->response->getContent());
                break;

            default:
                $this->getErrors()->add(new Errors\UnknownError());
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
     * @return Constraint[]
     */
    public function buildConstraintsOk(): array
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\Collection(array(
                        'fields' => array(
                            'id' => array(
                                new Constraints\NotBlank(),
                                new Constraints\Type(array(
                                    'type' => 'numeric',
                                )),
                            ),
                            'url' => array(
                                new Constraints\NotBlank(),
                                new Constraints\Url(),
                            ),
                            'filetype' => array(
                                new Constraints\NotBlank(),
                                new Constraints\Type('string'),
                            ),
                        ),
                        'allowExtraFields' => true,
                    )),
                ),
            )),
        );
    }
}
