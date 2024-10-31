<?php
namespace Setka\Editor\Admin\Migrations\Versions;

use Setka\Editor\Admin\Migrations\MigrationInterface;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\SetkaAccount\SignIn;

/**
 * Class Version20180102150532
 *
 * Migration fixing duplicated setka_editor_update_anonymous_account cron tasks.
 */
class Version20180102150532 implements MigrationInterface
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
     * Version20180102150532 constructor.
     * @param SetkaEditorAccount $setkaEditorAccount
     * @param SignIn $signIn
     */
    public function __construct(SetkaEditorAccount $setkaEditorAccount, SignIn $signIn)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        $this->signIn             = $signIn;
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        if ($this->setkaEditorAccount->isLoggedIn()) {
            $this->signIn->reSignIn();
        } else {
            $this->signIn->signInAnonymous();
        }

        return $this;
    }
}
