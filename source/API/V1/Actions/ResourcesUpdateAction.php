<?php
namespace Setka\Editor\API\V1\Actions;

use Korobochkin\WPKit\AlmostControllers\ActionInterface;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options;
use Setka\Editor\Admin\Service\FilesManager\FilesServiceManager;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Actions\GetCurrentThemeAction;
use Setka\Editor\API\V1\AbstractExtendedAction;
use Setka\Editor\API\V1\Errors;
use Setka\Editor\Service\AMP\AMPServiceManager;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @api {post} /wp-admin/admin-post.php?action=/webhook/setka-editor/v1/resources/update/ Update
 * @apiName PostResourcesUpdate
 * @apiGroup Resources
 *
 * @apiDescription Theme files (`theme_files`) and Content editor files (`content_editor_files`) container can contain
 * multiple files stored as object (or array with integer indexes) with multiple cells (single files objects).
 * The order of files is not matter.
 *
 * WordPress check that at least one file with `css`-filetype and one `json`-filetype must exists
 * in `theme_files` list (bunch).
 *
 * At least one file with `css`-filetype and one with `js`-filetype must exists in `content_editor_files` list.
 *
 * While saving we use first available file in list to save as css-js-json file.
 *
 * @apiParam {String} action="/webhook/setka-editor/v1/resources/update/" Action endpoint.
 *
 * @apiUse AuthHelper
 * @apiUse ThemeFilesHelper
 * @apiUse ContentEditorFilesHelper
 * @apiUse ThemePluginsHelper
 *
 * @apiParamExample {json} All the files (editor + plugins + theme)
 * {
 *      "action": "/webhook/setka-editor/v1/resources/update/",
 *      "token": "R9rEUHQVbG6nRIvpfiGzs6SuxxqbpLTZ",
 *      "data": {
 *          "content_editor_files": {
 *              0: {
 *                  "id": 1,
 *                  "url": "https://ceditor-dev.setka.io/editor.min.css",
 *                  "filetype": "css"
 *              },
 *              1: {
 *                  "id": 2,
 *                  "url": "https://ceditor-dev.setka.io/editor.min.js",
 *                  "filetype": "js"
 *              }
 *          },
 *          "theme_files": {
 *              0: {
 *                  "id": 1,
 *                  "url": "https://ceditor-dev.setka.io/clients/RANDOM_STRING_HERE/css/185_setka_1_23.min.css",
 *                  "filetype": "css"
 *              },
 *              1: {
 *                  "id": 2,
 *                  "url": "https://ceditor-dev.setka.io/clients/RANDOM_STRING_HERE/json/185_setka_1_23.json",
 *                  "filetype": "json"
 *              },
 *              2: {
 *                  "id": 3,
 *                  "url": "https://example.com/image.svg",
 *                  "filetype": "svg"
 *              }
 *          },
 *          "plugins": {
 *              0: {
 *                  "url": "https://ceditor-dev.setka.io/plugins.min.js",
 *                  "filetype": "js"
 *              }
 *          }
 *      }
 * }
 *
 * @apiSuccess (Action) 200 If all data successfully saved. Be aware that **this status code also returned
 * by WordPress if Setka Editor plugin not installed or deactivated**.
 *
 * @apiError (Action) 400 If request not contain `data` and `token` fields.
 */
class ResourcesUpdateAction extends AbstractExtendedAction implements ActionInterface
{
    /**
     * ResourcesUpdateAction constructor.
     */
    public function __construct()
    {
        $this->setEnabledForNotLoggedIn(true);
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest() // phpcs:disable Generic.Metrics.CyclomaticComplexity.MaxExceeded
    {
        /**
         * @var $logger LoggerInterface
         */
        $request  = $this->getRequest();
        $response = $this->getResponse();
        $logger   = $this->get('wp.plugins.setka_editor.logger.main');

        $logger->info('Start executing handleRequest.', $this->generateContext());

        if ($request->getMethod() !== Request::METHOD_POST) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $this->getViolationsList()->add(new Errors\HttpMethodError());
            return $this;
        }

        $logger->debug('Validated HTTP method.');

        if (is_array($request->request->get('data'))) {
            $request->request->set(
                'data',
                new ParameterBag($request->request->get('data'))
            );
        }

        if (!is_a($request->request->get('data'), ParameterBag::class)) {
            $response->setStatusCode($response::HTTP_BAD_REQUEST);
            $this->getViolationsList()->add(new Errors\RequestDataError());
            return $this;
        }

        $logger->debug('Transformed data from request into ParameterBag.');

        /**
         * @var $account SetkaEditorAccount
         */
        $account = $this->get(SetkaEditorAccount::class);

        if (!$account->isLoggedIn()) {
            $this->getViolationsList()->add(new Errors\SiteError());
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $this;
        }

        if ($account->getTokenOption()->get() !== $request->request->get('token')) {
            $this->getViolationsList()->add(new Errors\AuthenticationError());
            $response->setStatusCode(Response::HTTP_UNAUTHORIZED);
            return $this;
        }

        $logger->info('Authorized. Access allowed.', $this->generateContext());
        $logger->debug('Token from request.', array('token' => $request->request->get('token')));

        /**
         * @var $data ParameterBag
         * @var $getCompanyStatusAction GetCurrentThemeAction
         * @var $validator ValidatorInterface
         */
        $data                  = $request->request->get('data');
        $validator             = $this->get('wp.plugins.setka_editor.validator');
        $getCurrentThemeAction = new GetCurrentThemeAction();

        $logger->debug('Data from request.', array('data' => $data->all()));

        try {
            if ($data->has('content_editor_version') || $data->has('content_editor_files') || $data->has('public_token')) {
                $errors = $validator->validate($data->all(), $getCurrentThemeAction->buildConstraintsOk());
                $logger->debug('Validate data with constraints from "buildConstraintsOk".');
            } else {
                $errors = $validator->validate($data->all(), $getCurrentThemeAction->buildConstraintsForbidden());
                $logger->debug('Validate data with constraints from "buildConstraintsForbidden".');
            }
        } catch (\Exception $exception) {
            $logger->debug('During data validation was thrown an exception.', array('exception' => $exception));
            $this->getViolationsList()->add(new Errors\RequestDataError());
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $this;
        }

        $logger->info('Data validation completed.');

        if (count($errors) !== 0) {
            $logger->debug('Data from request is invalid.', array('errors' => $errors));
            $this->getViolationsList()->addAll($errors);
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            return $this;
        }

        $logger->info('Data from request is valid. Start updating local data.');

        $themeResourceCSSOption = new Options\ThemeResourceCSSOption();
        $themeResourceJSOption  = new Options\ThemeResourceJSOption();
        $themePluginsJSOption   = new Options\ThemePluginsJSOption();

        $editorCSSOption     = new Options\EditorCSSOption();
        $editorJSOption      = new Options\EditorJSOption();
        $editorVersionOption = new Options\EditorVersionOption();
        $publicTokenOption   = new Options\PublicTokenOption();

        /**
         * @var $AMPServiceManager AMPServiceManager
         */
        $AMPServiceManager = $this->get(AMPServiceManager::class);
        if ($AMPServiceManager->isOn() && $data->has('amp_styles')) {
            try {
                $AMPServiceManager->addNewConfig($data->get('amp_styles'));
            } catch (\Exception $exception) {
                $this->getViolationsList()->add(new Errors\SiteError());
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                return $this;
            }
            $logger->info('AMP styles updated.');
        } else {
            $AMPServiceManager->disable(false);
        }

        /**
         * @var $standaloneServiceManager StandaloneServiceManager
         */
        $standaloneServiceManager = $this->get(StandaloneServiceManager::class);
        if ($standaloneServiceManager->isOn() && $data->has(GetCurrentThemeAction::STANDALONE_STYLES)) {
            try {
                $standaloneServiceManager->addNewConfig($data->get(GetCurrentThemeAction::STANDALONE_STYLES));
            } catch (\Exception $exception) {
                $this->getViolationsList()->add(new Errors\SiteError());
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                return $this;
            }
            $logger->info('Standalone styles updated.');
        } else {
            $standaloneServiceManager->disable(false);
        }

        // Enable CDN files and run syncing
        $filesManager = $this->get(FilesServiceManager::class);
        if ($filesManager->isOn()) {
            $filesManager->restart();
        } else {
            $filesManager->disable(false);
        }

        $logger->info('Files sync restarted.');

        foreach ($data->get('theme_files') as $file) {
            switch ($file['filetype']) {
                case 'css':
                    $themeResourceCSSOption->updateValue($file['url']);
                    $logger->debug('Theme CSS file updated.', array('value' => $file['url'], 'option_name' => $themeResourceCSSOption->getName()));
                    break;
                case 'json':
                    $themeResourceJSOption->updateValue($file['url']);
                    $logger->debug('Theme JSON file updated.', array('value' => $file['url'], 'option_name' => $themeResourceJSOption->getName()));
                    break;
            }
        }

        $themePluginsJSOption->updateValue($data->get('plugins')[0]['url']);
        $logger->debug('Theme plugins JS file updated.', array('value' => $data->get('plugins')[0]['url'], 'option_name' => $themePluginsJSOption->getName()));

        if ($data->has('content_editor_files')) {
            foreach ($data->get('content_editor_files') as $file) {
                switch ($file['filetype']) {
                    case 'css':
                        $editorCSSOption->updateValue($file['url']);
                        $logger->debug('Editor CSS file updated.', array('value' => $file['url'], 'option_name' => $editorCSSOption->getName()));
                        break;
                    case 'js':
                        $editorJSOption->updateValue($file['url']);
                        $logger->debug('Editor JS file updated.', array('value' => $file['url'], 'option_name' => $editorJSOption->getName()));
                        break;
                }
            }
        } else {
            $editorCSSOption->delete();
            $logger->debug('Editor CSS file deleted.');
            $editorJSOption->delete();
            $logger->debug('Editor JS file deleted.');
        }

        if ($data->has('content_editor_version')) {
            $editorVersionOption->updateValue($data->get('content_editor_version'));
            $logger->debug('Content editor version updated.', array('value' => $data->get('content_editor_version'), 'option_name' => $editorVersionOption->getName()));
        } else {
            $editorVersionOption->delete();
            $logger->debug('Content editor version deleted.');
        }

        if ($data->has('public_token')) {
            $publicTokenOption->updateValue($data->get('public_token'));
            $logger->debug('Public token updated.', array('value' => $data->get('public_token'), 'option_name' => $publicTokenOption->getName()));
        } else {
            $publicTokenOption->delete();
            $logger->debug('Public token deleted.');
        }

        $response->setStatusCode(Response::HTTP_OK);

        $logger->info('Finished executing handleRequest.', $this->generateContext());

        return $this;
    }

    public function generateContext()
    {
        return array(
            'name' => $this->getName(),
            'class' => get_class($this),
        );
    }
}
