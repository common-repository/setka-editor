<?php
namespace Setka\Editor\Service\SetkaAccount;

use Setka\Editor\Admin\Service\FilesManager\FilesServiceManager;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Actions;
use Setka\Editor\Admin\Cron;
use Setka\Editor\Admin\Options;
use Setka\Editor\Exceptions\LogicException;
use Setka\Editor\Service\AMP\AMPServiceManager;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;
use Setka\Editor\Service\Styles\AbstractServiceManager;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class SignIn
{
    /**
     * @var SetkaEditorAPI\SetkaEditorAPI
     */
    private $api;

    /**
     * @var FilesServiceManager
     */
    private $filesServiceManager;

    /**
     * @var AMPServiceManager
     */
    private $AMPServiceManager;

    /**
     * @var StandaloneServiceManager
     */
    private $standaloneServiceManager;

    /**
     * @var Cron\SyncAccountCronEvent
     */
    private $syncAccountCronEvent;

    /**
     * @var Cron\UpdateAnonymousAccountCronEvent
     */
    private $updateAnonymousAccountCronEvent;

    /**
     * @var Cron\UserSignedUpCronEvent
     */
    private $userSignedUpCronEvent;

    /**
     * @var Options\PlanFeatures\PlanFeaturesOption
     */
    private $planFeaturesOption;

    /**
     * @var Options\EditorCSSOption
     */
    private $editorCSSOption;

    /**
     * @var Options\EditorJSOption
     */
    private $editorJSOption;

    /**
     * @var Options\EditorVersionOption
     */
    private $editorVersionOption;

    /**
     * @var Options\PublicTokenOption
     */
    private $publicTokenOption;

    /**
     * @var Options\SetkaPostCreatedOption
     */
    private $setkaPostCreatedOption;

    /**
     * @var Options\SubscriptionActiveUntilOption
     */
    private $subscriptionActiveUntilOption;

    /**
     * @var Options\SubscriptionPaymentStatusOption
     */
    private $subscriptionPaymentStatusOption;

    /**
     * @var Options\SubscriptionStatusOption
     */
    private $subscriptionStatusOption;

    /**
     * @var Options\ThemePluginsJSOption
     */
    private $themePluginsJSOption;

    /**
     * @var Options\ThemeResourceCSSOption
     */
    private $themeResourceCSSOption;

    /**
     * @var Options\ThemeResourceJSOption
     */
    private $themeResourceJSOption;

    /**
     * @var Options\TokenOption
     */
    private $tokenOption;

    /**
     * SignIn constructor.
     *
     * @param SetkaEditorAPI\SetkaEditorAPI $api
     * @param FilesServiceManager $filesServiceManager
     * @param AMPServiceManager $AMPServiceManager
     * @param StandaloneServiceManager $standaloneServiceManager
     * @param Cron\SyncAccountCronEvent $syncAccountCronEvent
     * @param Cron\UpdateAnonymousAccountCronEvent $updateAnonymousAccountCronEvent
     * @param Cron\UserSignedUpCronEvent $userSignedUpCronEvent
     * @param Options\PlanFeatures\PlanFeaturesOption $planFeaturesOption
     * @param Options\EditorCSSOption $editorCSSOption
     * @param Options\EditorJSOption $editorJSOption
     * @param Options\EditorVersionOption $editorVersionOption
     * @param Options\PublicTokenOption $publicTokenOption
     * @param Options\SetkaPostCreatedOption $setkaPostCreatedOption
     * @param Options\SubscriptionActiveUntilOption $subscriptionActiveUntilOption
     * @param Options\SubscriptionPaymentStatusOption $subscriptionPaymentStatusOption
     * @param Options\SubscriptionStatusOption $subscriptionStatusOption
     * @param Options\ThemePluginsJSOption $themePluginsJSOption
     * @param Options\ThemeResourceCSSOption $themeResourceCSSOption
     * @param Options\ThemeResourceJSOption $themeResourceJSOption
     * @param Options\TokenOption $tokenOption
     */
    public function __construct(
        SetkaEditorAPI\SetkaEditorAPI           $api,
        FilesServiceManager                     $filesServiceManager,
        AMPServiceManager                       $AMPServiceManager,
        StandaloneServiceManager                $standaloneServiceManager,
        Cron\SyncAccountCronEvent               $syncAccountCronEvent,
        Cron\UpdateAnonymousAccountCronEvent    $updateAnonymousAccountCronEvent,
        Cron\UserSignedUpCronEvent              $userSignedUpCronEvent,
        Options\PlanFeatures\PlanFeaturesOption $planFeaturesOption,
        Options\EditorCSSOption                 $editorCSSOption,
        Options\EditorJSOption                  $editorJSOption,
        Options\EditorVersionOption             $editorVersionOption,
        Options\PublicTokenOption $publicTokenOption,
        Options\SetkaPostCreatedOption $setkaPostCreatedOption,
        Options\SubscriptionActiveUntilOption $subscriptionActiveUntilOption,
        Options\SubscriptionPaymentStatusOption $subscriptionPaymentStatusOption,
        Options\SubscriptionStatusOption $subscriptionStatusOption,
        Options\ThemePluginsJSOption $themePluginsJSOption,
        Options\ThemeResourceCSSOption $themeResourceCSSOption,
        Options\ThemeResourceJSOption $themeResourceJSOption,
        Options\TokenOption $tokenOption
    ) {
        $this->api = $api;

        $this->filesServiceManager             = $filesServiceManager;
        $this->AMPServiceManager               = $AMPServiceManager;
        $this->standaloneServiceManager        = $standaloneServiceManager;
        $this->syncAccountCronEvent            = $syncAccountCronEvent;
        $this->updateAnonymousAccountCronEvent = $updateAnonymousAccountCronEvent;
        $this->userSignedUpCronEvent           = $userSignedUpCronEvent;

        $this->planFeaturesOption              = $planFeaturesOption;
        $this->editorCSSOption                 = $editorCSSOption;
        $this->editorJSOption                  = $editorJSOption;
        $this->editorVersionOption             = $editorVersionOption;
        $this->publicTokenOption               = $publicTokenOption;
        $this->setkaPostCreatedOption          = $setkaPostCreatedOption;
        $this->subscriptionActiveUntilOption   = $subscriptionActiveUntilOption;
        $this->subscriptionPaymentStatusOption = $subscriptionPaymentStatusOption;
        $this->subscriptionStatusOption        = $subscriptionStatusOption;
        $this->themePluginsJSOption            = $themePluginsJSOption;
        $this->themeResourceCSSOption          = $themeResourceCSSOption;
        $this->themeResourceJSOption           = $themeResourceJSOption;
        $this->tokenOption                     = $tokenOption;
    }

    /**
     * Auth from settings pages.
     *
     * By default token updated in DB (but settings pages save token manually).
     *
     * @param $token string New token.
     * @param $updateToken bool Should this function save token or not.
     *
     * @return SetkaEditorAPI\Prototypes\ActionInterface[]
     */
    public function signInByToken($token, $updateToken = true)
    {
        $actions = $this->sendAuthRequests($token);

        if (!$this->isActionsValid($actions)) {
            return $actions;
        }

        if ($updateToken) {
            $this->setupToken($token);
        }

        $this->setupNewAccount(
            $actions[Actions\GetCurrentThemeAction::class],
            $actions[Actions\GetCompanyStatusAction::class]
        );

        return $actions;
    }

    /**
     * Send auth requests and return actions with validated responses.
     *
     * @param $token string Company token (license key).
     *
     * @return SetkaEditorAPI\Prototypes\ActionInterface[] Executed actions
     */
    private function sendAuthRequests($token)
    {
        $this->api->setAuthCredits(new SetkaEditorAPI\AuthCredits($token));
        $this->api->request($currentTheme  = new Actions\GetCurrentThemeAction());
        $this->api->request($companyStatus = new Actions\GetCompanyStatusAction());

        return array(
            Actions\GetCurrentThemeAction::class  => $currentTheme,
            Actions\GetCompanyStatusAction::class => $companyStatus,
        );
    }

    /**
     * Check that all actions are valid.
     *
     * @param SetkaEditorAPI\Prototypes\ActionInterface[] $actions
     *
     * @return bool True if all actions without errors.
     */
    private function isActionsValid(array $actions)
    {
        /**
         * @var $action SetkaEditorAPI\Prototypes\ActionInterface
         */
        foreach ($actions as $action) {
            if (!$this->isActionValid($action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param SetkaEditorAPI\Prototypes\ActionInterface $action
     *
     * @return bool
     */
    private function isActionValid(SetkaEditorAPI\Prototypes\ActionInterface $action)
    {
        if (count($action->getErrors()) === 0) {
            return true;
        }
        return false;
    }

    /**
     * @throws LogicException
     * @return SetkaEditorAPI\Prototypes\ActionInterface[]
     */
    public function reSignIn()
    {
        if (!$this->tokenOption->get()) {
            throw new LogicException('You must be logged in before calling ' . self::class . '::reSignIn method.');
        }
        return $this->signInByToken($this->tokenOption->get(), false);
    }

    /**
     * Save token.
     *
     * @param $token string Token.
     *
     * @return $this
     */
    private function setupToken($token)
    {
        $this->tokenOption->updateValue($token);
        return $this;
    }

    /**
     * @param Actions\GetCurrentThemeAction $currentTheme
     * @param Actions\GetCompanyStatusAction $companyStatus
     * @return $this For chain calls.
     */
    public function setupNewAccount(
        Actions\GetCurrentThemeAction $currentTheme,
        Actions\GetCompanyStatusAction $companyStatus
    ) {
        $this
            ->updateSubscriptionDetails($companyStatus)
            ->updateThemeDetails($currentTheme)
            ->updateEditorDetails($currentTheme)
            ->updateAMPDetails($currentTheme)
            ->updateStandaloneDetails($currentTheme)
            ->resetSetkaPostCreatedFlag()
            ->setupUserSignedUpEvent()
            ->removeAnonymousSync()
            ->setupFileSyncing();

        return $this;
    }

    /**
     * @param Actions\GetCompanyStatusAction $action
     *
     * @return $this
     */
    private function updateSubscriptionDetails(Actions\GetCompanyStatusAction $action)
    {
        $content = $action->getResponse()->getContent();

        $this->subscriptionPaymentStatusOption->updateValue($content->get('payment_status'));

        $this->subscriptionStatusOption->updateValue($content->get('status'));

        $this->syncAccountCronEvent->unScheduleAll();

        if ($action->getResponse()->isOk()) {
            $this->subscriptionActiveUntilOption->updateValue($content->get('active_until'));

            $datetime = \DateTime::createFromFormat(
                \DateTime::ISO8601,
                $content->get('active_until')
            );
            if ($datetime) {
                $this->syncAccountCronEvent->setTimestamp($datetime->getTimestamp())->schedule();
            }
        } else {
            $this->subscriptionActiveUntilOption->delete();
        }

        $this->planFeaturesOption->updateValue($content->get('features'));

        return $this;
    }

    /**
     * @return $this
     */
    private function removeSubscriptionDetails()
    {
        $this->subscriptionPaymentStatusOption->delete();
        $this->subscriptionStatusOption->delete();
        $this->syncAccountCronEvent->unScheduleAll();
        $this->subscriptionActiveUntilOption->delete();
        $this->planFeaturesOption->delete();
        return $this;
    }

    /**
     * Reset flag which shows that first Setka Post created.
     * @return $this
     */
    private function resetSetkaPostCreatedFlag()
    {
        $this->setkaPostCreatedOption->delete();
        return $this;
    }

    /**
     * @param Actions\GetCurrentThemeAction $action
     *
     * @return $this
     */
    private function updateThemeDetails(Actions\GetCurrentThemeAction $action)
    {
        return $this->updateThemeDetailsCommon($action);
    }

    /**
     * @param Actions\GetCurrentThemeAnonymouslyAction $action
     *
     * @return $this
     */
    private function updateThemeDetailsAnonymous(Actions\GetCurrentThemeAnonymouslyAction $action)
    {
        return $this->updateThemeDetailsCommon($action);
    }

    /**
     * @param SetkaEditorAPI\Prototypes\ActionInterface $action
     *
     * @return $this
     */
    private function updateThemeDetailsCommon(SetkaEditorAPI\Prototypes\ActionInterface $action)
    {
        $content = $action->getResponse()->getContent();

        foreach ($content->get(Actions\GetCurrentThemeAction::THEME_FILES) as $file) {
            switch ($file['filetype']) {
                case 'css':
                    $this->themeResourceCSSOption->updateValue($file['url']);
                    break;

                case 'json':
                    $this->themeResourceJSOption->updateValue($file['url']);
                    break;
            }
        }

        if ($content->has(Actions\GetCurrentThemeAction::PLUGINS)) {
            $plugins = $content->get(Actions\GetCurrentThemeAction::PLUGINS);
            $this->themePluginsJSOption->updateValue($plugins[0]['url']);
        } else {
            $this->themePluginsJSOption->delete();
        }

        return $this;
    }

    /**
     * @param Actions\GetCurrentThemeAction $action
     *
     * @return $this
     */
    private function updateEditorDetails(Actions\GetCurrentThemeAction $action)
    {
        $content = $action->getResponse()->getContent();

        $this->publicTokenOption->updateValue($content->get(Actions\GetCurrentThemeAction::PUBLIC_TOKEN));

        return $this->updateEditorDetailsCommon($action);
    }

    /**
     * @param Actions\GetCurrentThemeAnonymouslyAction $action
     *
     * @return $this
     */
    private function updateEditorDetailsAnonymous(Actions\GetCurrentThemeAnonymouslyAction $action)
    {
        return $this->updateEditorDetailsCommon($action);
    }

    /**
     * @param SetkaEditorAPI\Prototypes\ActionInterface $action
     *
     * @return $this
     */
    private function updateEditorDetailsCommon(SetkaEditorAPI\Prototypes\ActionInterface $action)
    {
        $content = $action->getResponse()->getContent();

        if ($action->getResponse()->isOk()) {
            foreach ($content->get(Actions\GetCurrentThemeAction::EDITOR_FILES) as $file) {
                switch ($file['filetype']) {
                    case 'css':
                        $this->editorCSSOption->updateValue($file['url']);
                        break;

                    case 'js':
                        $this->editorJSOption->updateValue($file['url']);
                        break;
                }
            }
            $this->editorVersionOption->updateValue($content->get(Actions\GetCurrentThemeAction::EDITOR_VERSION));
        } elseif ($action->getResponse()->getStatusCode() === Response::HTTP_FORBIDDEN) {
            $this->editorJSOption->delete();
            $this->editorCSSOption->delete();
            $this->editorVersionOption->delete();
        }

        return $this;
    }

    /**
     * @param Actions\GetCurrentThemeAction $action
     *
     * @return $this
     */
    private function updateAMPDetails(Actions\GetCurrentThemeAction $action)
    {
        return $this->updateAMPDetailsCommon($action, true);
    }

    /**
     * @param Actions\GetCurrentThemeAnonymouslyAction $action
     *
     * @return SignIn
     */
    private function updateAMPDetailsAnonymous(Actions\GetCurrentThemeAnonymouslyAction $action)
    {
        return $this->updateAMPDetailsCommon($action, false);
    }

    /**
     * @param SetkaEditorAPI\Prototypes\ActionInterface $action
     * @param boolean $serviceOn
     *
     * @return $this
     */
    private function updateAMPDetailsCommon(SetkaEditorAPI\Prototypes\ActionInterface $action, $serviceOn)
    {
        $this->styleManager(
            $content = $action->getResponse()->getContent(),
            Actions\GetCurrentThemeAction::AMP_STYLES,
            $this->AMPServiceManager->isOn() && $serviceOn,
            $this->AMPServiceManager
        );
        return $this;
    }

    /**
     * @param Actions\GetCurrentThemeAction $action
     *
     * @return $this
     */
    private function updateStandaloneDetails(Actions\GetCurrentThemeAction $action)
    {
        return $this->updateStandaloneDetailsCommon($action, true);
    }

    /**
     * @param Actions\GetCurrentThemeAnonymouslyAction $action
     *
     * @return $this
     */
    private function updateStandaloneDetailsAnonymous(Actions\GetCurrentThemeAnonymouslyAction $action)
    {
        return $this->updateStandaloneDetailsCommon($action, false);
    }

    /**
     * @param SetkaEditorAPI\Prototypes\ActionInterface $action
     * @param boolean $serviceOn
     *
     * @return $this
     */
    private function updateStandaloneDetailsCommon(SetkaEditorAPI\Prototypes\ActionInterface $action, $serviceOn)
    {
        $this->styleManager(
            $content = $action->getResponse()->getContent(),
            Actions\GetCurrentThemeAction::STANDALONE_STYLES,
            $this->standaloneServiceManager->isOn() && $serviceOn,
            $this->standaloneServiceManager
        );
        return $this;
    }

    /**
     * @param ParameterBag $content
     * @param string $key
     * @param boolean $serviceOn
     * @param AbstractServiceManager $serviceManager
     */
    private function styleManager(
        ParameterBag $content,
        $key,
        $serviceOn,
        AbstractServiceManager $serviceManager
    ) {
        if ($serviceOn && $content->has($key)) {
            try {
                $serviceManager->addNewConfig($content->get($key));
            } catch (\Exception $exception) {
            }
        } else {
            $serviceManager->disable(false);
        }
    }

    /**
     * @return $this
     */
    private function setupUserSignedUpEvent()
    {
        $this->userSignedUpCronEvent->unScheduleAll()->schedule();
        return $this;
    }

    /**
     * @return $this
     */
    private function removeUserSignedUpEvent()
    {
        $this->userSignedUpCronEvent->unscheduleAll();
        return $this;
    }

    /**
     * @return $this
     */
    private function removeAnonymousSync()
    {
        $this->updateAnonymousAccountCronEvent->unScheduleAll();
        return $this;
    }

    /**
     * @return $this
     */
    private function reScheduleAnonymousSync()
    {
        $this->updateAnonymousAccountCronEvent->unScheduleAll()->setTimestamp(time() + DAY_IN_SECONDS)->schedule();
        return $this;
    }

    /**
     * @return $this
     */
    private function setupFileSyncing()
    {
        if ($this->filesServiceManager->isOn()) {
            $this->filesServiceManager->restart();
        } else {
            $this->filesServiceManager->disable(false);
        }
        return $this;
    }

    private function setupFilesSyncingAnonymous()
    {
        $this->filesServiceManager->disable(false);
    }

    /**
     * @return Actions\GetCurrentThemeAnonymouslyAction
     */
    public function signInAnonymous()
    {
        $action = $this->sendAuthRequestsAnonymous();

        if (count($action->getErrors()) > 0) {
            return $action;
        }

        $this->setupAnonymousAccount($action);

        return $action;
    }

    /**
     * @param Actions\GetCurrentThemeAnonymouslyAction $currentTheme
     */
    public function setupAnonymousAccount(Actions\GetCurrentThemeAnonymouslyAction $currentTheme)
    {
        $this
            ->removeSubscriptionDetails()
            ->updateThemeDetailsAnonymous($currentTheme)
            ->updateEditorDetailsAnonymous($currentTheme)
            ->updateAMPDetailsAnonymous($currentTheme)
            ->updateStandaloneDetailsAnonymous($currentTheme)
            ->resetSetkaPostCreatedFlag()
            ->removeUserSignedUpEvent()
            ->reScheduleAnonymousSync()
            ->setupFilesSyncingAnonymous();

        $this->subscriptionStatusOption->updateValue('running');

        return $this;
    }

    /**
     * @return Actions\GetCurrentThemeAnonymouslyAction
     */
    public function sendAuthRequestsAnonymous()
    {
        $action = new Actions\GetCurrentThemeAnonymouslyAction();
        $this->api->request($action);
        return $action;
    }

    /**
     * Merge errors from all $actions.
     *
     * @param SetkaEditorAPI\Prototypes\ActionInterface[] $actions Actions with errors.
     * @param ConstraintViolationListInterface $violations Resulted errors list.
     *
     * @return $this For chain calls.
     */
    public function mergeActionErrors(array $actions, ConstraintViolationListInterface $violations)
    {
        $bodyError  = false;
        $errorCodes = array();

        foreach ($actions as $action) {
            foreach ($action->getErrors() as $violation) {
                if (get_class($violation) === ConstraintViolation::class) {
                    $bodyError = true;
                    continue;
                }
                /**
                 * @var $violation ConstraintViolationInterface
                 */
                if (!isset($errorCodes[$violation->getCode()])) {
                    $errorCodes[$violation->getCode()] = true;
                    $violations->add($violation);
                }
            }
        }

        $bodyErrorInstance = new SetkaEditorAPI\Errors\ResponseBodyInvalidError();
        if ($bodyError && !isset($errorCodes[$bodyErrorInstance->getCode()])) {
            $violations->add($bodyErrorInstance);
        }

        return $this;
    }
}
