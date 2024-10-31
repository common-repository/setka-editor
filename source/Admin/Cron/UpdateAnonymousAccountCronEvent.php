<?php
namespace Setka\Editor\Admin\Cron;

use Korobochkin\WPKit\Cron\AbstractCronEvent;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\SetkaAccount\SignIn;

class UpdateAnonymousAccountCronEvent extends AbstractCronEvent
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var SignIn
     */
    private $signIn;

    public function __construct()
    {
        $this->setRecurrence('daily');
        $this->setName(Plugin::_NAME_.'_update_anonymous_account');
    }

    public function execute()
    {
        if (!$this->setkaEditorAccount->isLoggedIn()) {
            $this->signIn->signInAnonymous();
        }
    }

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     */
    public function setSetkaEditorAccount(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
    }

    /**
     * @param SignIn $signIn
     */
    public function setSignIn(SignIn $signIn)
    {
        $this->signIn = $signIn;
    }
}
