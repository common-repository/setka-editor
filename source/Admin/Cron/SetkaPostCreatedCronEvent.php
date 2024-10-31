<?php
namespace Setka\Editor\Admin\Cron;

use Korobochkin\WPKit\Cron\AbstractCronSingleEvent;
use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

/**
 * Class SetkaPostCreatedCronEvent
 */
class SetkaPostCreatedCronEvent extends AbstractCronSingleEvent
{
    /**
     * @var SetkaEditorAccount
     */
    protected $setkaEditorAccount;

    /**
     * @var SetkaEditorAPI\SetkaEditorAPI
     */
    protected $setkaEditorAPI;

    /**
     * @var OptionInterface
     */
    protected $setkaPostCreatedOption;

    /**
     * SetkaPostCreatedCronEvent constructor.
     */
    public function __construct()
    {
        $this
            ->immediately()
            ->setName(Plugin::_NAME_ . '_cron_setka_post_created');
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
                'status' => 'post_saved',
            ),
        ));
        $this->setkaEditorAPI->request($action);

        // Delete setting if request was unsuccessful (the action have errors).
        // We make this in order to \Setka\Editor\Admin\Service\SavePost::proceeding()
        // could try add this cron task again.
        if (count($action->getErrors()) !== 0) {
            $this->setkaPostCreatedOption->delete();
        }

        return $this;
    }

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     * @return $this
     */
    public function setSetkaEditorAccount(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        return $this;
    }

    /**
     * @param SetkaEditorAPI\SetkaEditorAPI $setkaEditorAPI
     * @return $this
     */
    public function setSetkaEditorAPI(SetkaEditorAPI\SetkaEditorAPI $setkaEditorAPI)
    {
        $this->setkaEditorAPI = $setkaEditorAPI;
        return $this;
    }

    /**
     * @param OptionInterface $setkaPostCreatedOption
     * @return $this
     */
    public function setSetkaPostCreatedOption(OptionInterface $setkaPostCreatedOption)
    {
        $this->setkaPostCreatedOption = $setkaPostCreatedOption;
        return $this;
    }
}
