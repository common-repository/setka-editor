<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Helpers;

use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Symfony\Component\Validator\Constraints;

class ErrorHelper extends SetkaEditorAPI\Prototypes\HelperAbstract
{
    public function buildResponseConstraints(): array
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\All(array(
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new Constraints\All(array(
                        'constraints' => array(
                            new Constraints\NotBlank(),
                            new Constraints\Type(array(
                                'type' => 'string',
                            )),
                        ),
                    )),
                ),
            )),
        );
    }

    public function handleResponse(): void
    {
        if ($this->response->getContent()->has('error') && !$this->response->getContent()->has('errors')) { // Single error message from API
            $errors = array(
                '1' => array(
                    $this->response->getContent()->get('error')
                )
            );
        } elseif ($this->response->getContent()->has('errors') && !$this->response->getContent()->has('error')) { // Multiple error messages from API
            $errors = $this->response->getContent()->get('errors');
        } else {
            $this->errors->add(new Errors\ResponseBodyInvalidError());
            return;
        }

        try {
            $results = $this->API->getValidator()->validate(
                $errors,
                $this->buildResponseConstraints()
            );
            $this->errors->addAll($results);
        } catch (\Exception $exception) {
            $this->addError(new Errors\ResponseBodyInvalidError());
        }
    }
}
