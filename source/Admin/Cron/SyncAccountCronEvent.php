<?php
namespace Setka\Editor\Admin\Cron;

use Korobochkin\WPKit\Cron\AbstractCronSingleEvent;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\SetkaAccount\SignIn;

class SyncAccountCronEvent extends AbstractCronSingleEvent
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var SignIn
     */
    private $signIn;

    /**
     * SyncAccountCronEvent constructor.
     */
    public function __construct()
    {
        $this->setName(Plugin::_NAME_ . '_cron_sync_account');
    }

    public function execute()
    {
        if ($this->setkaEditorAccount->isLoggedIn() && $this->setkaEditorAccount->isTokenValid()) {
            $this->signIn->reSignIn();
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
