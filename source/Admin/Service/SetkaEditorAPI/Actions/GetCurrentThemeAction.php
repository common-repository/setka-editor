<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;

use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Errors;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Helpers;
use Symfony\Component\HttpFoundation\ParameterBag;
use Setka\Editor\Admin\Options;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;

class GetCurrentThemeAction extends AbstractCurrentThemeAction
{
    public function __construct()
    {
        $this->setMethod(Request::METHOD_POST);
        $this->setEndpoint('/api/v1/wordpress/current_theme.json');
    }

    public function handleResponse(): void
    {
        switch ($this->response->getStatusCode()) {
            case Response::HTTP_OK: // theme_files and content_editor_files must presented in response
                $this->validateOk($this->response->getContent());
                break;

            case Response::HTTP_UNAUTHORIZED: // Token not found
                $this->addError(new Errors\ServerUnauthorizedError());
                break;

            /**
             * This status code means what subscription is canceled.
             * But in this case API also response with valid theme_files.
             * Creating new posts functionality disabled but old posts
             * can correctly displayed.
             */
            case Response::HTTP_FORBIDDEN:
                $helper = new Helpers\ErrorHelper($this->API, $this->response, $this->errors);
                $helper->handleResponse();

                $this->validateForbidden($this->response->getContent());
                break;

            default:
                $this->addError(new Errors\UnknownError());
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
     * Validates HTTP 403 data.
     *
     * @param ParameterBag $content Parameters to validate.
     */
    private function validateForbidden(ParameterBag $content): void
    {
        try {
            $results = $this->API->getValidator()->validate(
                $content->all(),
                $this->buildConstraintsForbidden()
            );
            $this->getErrors()->addAll($results);
        } catch (\Exception $exception) {
            $this->addError(new Errors\ResponseBodyInvalidError());
        }
    }

    public function buildConstraintsOk(): Constraints\Collection
    {
        $editorVersionOption    = new Options\EditorVersionOption();
        $publicTokenOption      = new Options\PublicTokenOption();
        $ampStylesOption        = new Options\AMP\AMPStylesOption();
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


                self::AMP_STYLES => new Constraints\Optional($ampStylesOption->buildConstraint()),

                self::STANDALONE_STYLES => new Constraints\Optional($standaloneStylesOption->buildConstraint()),

                self::PUBLIC_TOKEN => new Constraints\Required($publicTokenOption->buildConstraint()),
            ),
            'allowExtraFields' => true,
        ));
    }

    public function buildConstraintsForbidden(): Constraints\Collection
    {
        $ampStylesOption = new Options\AMP\AMPStylesOption();

        return new Constraints\Collection(array(
            'fields' => array(


                'theme_files' => array(
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


                'plugins' => array(
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


                'amp_styles' => new Constraints\Optional($ampStylesOption->buildConstraint()),

            ),
            'allowExtraFields' => true,
        ));
    }
}
