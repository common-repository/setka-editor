<?php
namespace Setka\Editor\Admin\Pages\SetkaEditor\Account;

use Korobochkin\WPKit\Pages\MenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Admin\Prototypes\Pages\PrepareTabsTrait;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Admin\Options;
use Setka\Editor\Service\SetkaAccount\SignOut;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolationList;

class AccountPage extends MenuPage
{
    use PrepareTabsTrait;

    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var \Setka\Editor\Service\SetkaAccount\SignIn
     */
    private $signIn;

    /**
     * @var SignOut
     */
    private $signOut;

    public function __construct()
    {
        $this->setPageTitle(__('Account', Plugin::NAME));
        $this->setMenuTitle($this->getPageTitle());
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME);

        $this->setName('account');

        $view = new TwigPageView();
        $view->setTemplate('admin/settings/setka-editor/account/page.html.twig');
        $this->setView($view);
    }

    public function lateConstruct()
    {
        $this->prepareTabs();

        $this->setFormEntity(new SignIn());
        $this->lateConstructEntity();
        $this->setForm($this->getFormFactory()->createNamed(Plugin::_NAME_, SignInType::class, $this->getFormEntity()));

        $this->handleRequest();

        $attributes = array(
            'page' => $this,
            'form' => $this->getForm()->createView(),
            'translations' => array(
                'already_signed_in' => __('You have already started the plugin.', Plugin::NAME),
            ),
            'signedIn' => $this->setkaEditorAccount->isLoggedIn(),
        );

        $this->getView()->setContext($attributes);

        return $this;
    }

    public function handleRequest()
    {
        $form = $this->getForm()->handleRequest($this->getRequest());

        if (!$form->isSubmitted()) {
            return;
        }

        if ($form->get('sync')->isClicked()) {
            $this->syncClicked();
        } elseif ($form->get('submitToken')->isClicked()) {
            $this->signOutClicked();
        }
    }

    private function syncClicked()
    {
        if (!$this->setkaEditorAccount->isLoggedIn()) {
            return;
        }

        $actions = $this->signIn->reSignIn();

        $violations = new ConstraintViolationList();

        $this->signIn->mergeActionErrors($actions, $violations);

        if (count($violations) !== 0) {
            foreach ($violations as $violation) {
                $this->getForm()->addError(new FormError(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getPlural()
                ));
            }
        }
    }

    public function signOutClicked()
    {
        $this->signOut->signOutAction();
        $this->signIn->signInAnonymous();
        $url = $this->getURL();
        $url = add_query_arg('account-type', 'sign-in', $url);
        wp_safe_redirect($url);
        exit();
    }

    protected function lateConstructEntity()
    {
        /**
         * @var $a SignIn
         */
        $a = $this->getFormEntity();

        $token = new Options\TokenOption();
        $a->setToken($token->get());
    }

    /**
     * @inheritdoc
     */
    public function getURL()
    {
        return add_query_arg(
            'page',
            $this->getMenuSlug(),
            admin_url('admin.php')
        );
    }

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     *
     * @return $this
     */
    public function setSetkaEditorAccount(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        return $this;
    }

    /**
     * @param \Setka\Editor\Service\SetkaAccount\SignIn $signIn
     */
    public function setSignIn(\Setka\Editor\Service\SetkaAccount\SignIn $signIn)
    {
        $this->signIn = $signIn;
    }

    /**
     * @param SignOut $signOut
     */
    public function setSignOut(SignOut $signOut)
    {
        $this->signOut = $signOut;
    }
}
