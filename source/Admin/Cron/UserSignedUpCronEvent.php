<?php
namespace Setka\Editor\Admin\Cron;

use Korobochkin\WPKit\Cron\AbstractCronSingleEvent;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class UserSignedUpCronEvent extends AbstractCronSingleEvent
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var SetkaEditorAPI\SetkaEditorAPI
     */
    private $setkaEditorAPI;

    /**
     * UserSignedUpCronEvent constructor.
     */
    public function __construct()
    {
        $this
            ->immediately()
            ->setName(Plugin::_NAME_ . '_cron_user_signed_up');
    }

    public function execute()
    {
        if (!$this->setkaEditorAccount->isLoggedIn() || !$this->setkaEditorAccount->isTokenValid()) {
            return $this;
        }

        $this->setkaEditorAPI
            ->setAuthCredits(
                new SetkaEditorAPI\AuthCredits(
                    $this->setkaEditorAccount->getTokenOption()->get()
                )
            );

        $action = new SetkaEditorAPI\Actions\UpdateStatusAction();
        $action->setRequestDetails(array(
            'body' => array(
                'status' => 'plugin_installed',
            ),
        ));

        $this->setkaEditorAPI->request($action);
    }

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     */
    public function setSetkaEditorAccount(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
    }

    /**
     * @param SetkaEditorAPI\SetkaEditorAPI $setkaEditorAPI
     */
    public function setSetkaEditorAPI(SetkaEditorAPI\SetkaEditorAPI $setkaEditorAPI)
    {
        $this->setkaEditorAPI = $setkaEditorAPI;
    }
}
