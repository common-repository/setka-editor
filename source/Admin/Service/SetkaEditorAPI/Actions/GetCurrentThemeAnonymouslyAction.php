<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Options\EditorVersionOption;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;

class GetCurrentThemeAnonymouslyAction extends AbstractCurrentThemeAction
{
    public function __construct()
    {
        $this->setMethod(Request::METHOD_GET);
        $this->setEndpoint('/api/v1/wordpress/default_files.json');
        $this->setAuthenticationRequired(false);
    }

    public function handleResponse(): void
    {
        switch ($this->response->getStatusCode()) {
            case Response::HTTP_OK:
                $this->validateOk($this->response->getContent());
                break;

            case Response::HTTP_UNAUTHORIZED: // Token not found
                $this->errors->add(new Errors\ServerUnauthorizedError());
                break;

            default:
                $this->errors->add(new Errors\UnknownError());
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

    private function buildConstraintsOk(): Constraints\Collection
    {
        $editorVersionOption    = new EditorVersionOption();
        $standaloneStylesOption = new Options\Standalone\StylesOption();

        return new Constraints\Collection(array(
            'fields' => array(


                self::EDITOR_VERSION => new Constraints\Required($editorVersionOption->buildConstraint()),


                self::EDITOR_FILES => array(
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
                                        new Constraints\Choice(array(
                                            'choices' => array('css', 'js'),
                                            'strict' => true,
                                        )),
                                    ),
                                ),
                                'allowExtraFields' => true,
                            )),
                        ),
                    )),
                    new Constraints\Callback(array(new SetkaEditorAPI\CheckAllFilesExists(array('css', 'js')), 'validate')),
                ),


                self::THEME_FILES => array(
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
                                        new Constraints\Choice(array(
                                            'choices' => array('css', 'js', 'svg', 'json'),
                                            'strict' => true,
                                        )),
                                    ),
                                ),
                                'allowExtraFields' => true,
                            )),
                        ),
                    )),
                    new Constraints\Callback(array(new SetkaEditorAPI\CheckAllFilesExists(array('css', 'json')), 'validate')),
                ),


                self::PLUGINS => array(
                    new Constraints\NotBlank(),
                    new Constraints\All(array(
                        'constraints' => array(
                            new Constraints\NotBlank(),
                            new Constraints\Collection(array(
                                'fields' => array(
                                    'url' => array(
                                        new Constraints\NotBlank(),
                                        new Constraints\Url(),
                                    ),
                                    'filetype' => array(
                                        new Constraints\NotBlank(),
                                        new Constraints\IdenticalTo(array(
                                            'value' => 'js',
                                        )),
                                    ),
                                ),
                                'allowExtraFields' => true,
                            )),
                        ),
                    )),
                    new Constraints\Callback(array(new SetkaEditorAPI\CheckAllFilesExists(array('js')), 'validate')),
                ),

                self::STANDALONE_STYLES => new Constraints\Optional($standaloneStylesOption->buildConstraint()),
            ),
            'allowExtraFields' => true,
        ));
    }
}
