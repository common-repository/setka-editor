<?php
namespace Setka\Editor\Admin\Pages\SetkaEditor\SignUp;

use Korobochkin\WPKit\Pages\MenuPage;
use Korobochkin\WPKit\Pages\Views\TwigPageView;
use Setka\Editor\Admin\Prototypes\Pages\PrepareTabsTrait;
use Setka\Editor\Plugin;
use Setka\Editor\Admin\Transients;
use Setka\Editor\Service\SetkaAccount\SignIn;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolationList;

class SignUpPage extends MenuPage
{
    use PrepareTabsTrait;

    /**
     * @var SignIn
     */
    private $signIn;

    public function __construct(SignIn $signIn)
    {
        $this->setPageTitle(__('Log in', Plugin::NAME));
        $this->setMenuTitle(_x('Log in', 'Menu title', Plugin::NAME));
        $this->setCapability('manage_options');
        $this->setMenuSlug(Plugin::NAME);

        $this->setName('sign-up');

        $view = new TwigPageView();
        $view->setTemplate('admin/settings/setka-editor/page.html.twig');
        $this->setView($view);

        $this->signIn = $signIn;
    }

    /**
     * @inheritdoc
     */
    public function lateConstruct(): void
    {
        $this->prepareTabs();

        $this->setFormEntity(new SignUp());

        $formBuilder = $this->getFormFactory()->createNamedBuilder(Plugin::_NAME_, SignUpType::class, $this->getFormEntity());
        $form        = $formBuilder
            ->setAction($this->getURL())
            ->getForm();
        $this->setForm($form);

        $this->handleRequest();

        $attributes = array(
            'page' => $this,
            'form' => $form->createView(),
        );

        $this->getView()->setContext($attributes);
    }

    /**
     * @inheritdoc
     */
    public function handleRequest(): void
    {
        $form = $this->getForm()->handleRequest($this->getRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleRequestSignIn();
        }
    }

    public function handleRequestSignIn(): void
    {
        /**
         * @var $data SignUp
         */
        $form = $this->getForm();
        $data = $form->getData();

        $results    = $this->signIn->signInByToken($data->getToken());
        $violations = new ConstraintViolationList();

        $this->signIn->mergeActionErrors($results, $violations);

        if (count($violations) !== 0) {
            $field = $form->get('token');
            foreach ($violations as $violation) {
                $field->addError(new FormError(
                    $violation->getMessage(),
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getPlural()
                ));
            }
        } else {
            $transient = new Transients\AfterSignInNoticeTransient();
            $transient->updateValue(true);

            wp_safe_redirect($this->getURL());
            exit();
        }
    }

    /**
     * @inheritdoc
     */
    public function getURL(): string
    {
        return add_query_arg(
            'page',
            $this->getMenuSlug(),
            admin_url('admin.php')
        );
    }
}
