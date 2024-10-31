<?php
namespace Setka\Editor\Admin\Cron\Files;

use Korobochkin\WPKit\Cron\AbstractCronSingleEvent;
use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Service\FilesManager\AssetsStatus;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Actions\SendFilesStatAction;
use Setka\Editor\Admin\Service\SetkaEditorAPI\AuthCredits;
use Setka\Editor\Admin\Service\SetkaEditorAPI;
use Setka\Editor\Service\PostStatuses;
use Setka\Editor\Plugin;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class SendFilesStatCronEvent extends AbstractCronSingleEvent
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
    protected $useLocalFilesOption;

    /**
     * @var AssetsStatus
     */
    protected $assetsStatus;

    /**
     * @var array
     */
    private static $countersTemplate = array(
        'downloaded' => PostStatuses::PUBLISH,
        'failed' => PostStatuses::PENDING,
        'archived' => PostStatuses::ARCHIVE,
        'queued' => PostStatuses::DRAFT,
        'total' => PostStatuses::ANY,
    );

    /**
     * SendFilesStatCronEvent constructor.
     */
    public function __construct()
    {
        $this
            ->immediately()
            ->setName(Plugin::_NAME_ . '_cron_files_send_files_stat');
    }

    public function execute()
    {
        if (!$this->setkaEditorAccount->isLoggedIn()) {
            return $this;
        }

        $this->setkaEditorAPI
            ->setAuthCredits(
                new AuthCredits(
                    $this->setkaEditorAccount->getTokenOption()->get()
                )
            );
        $action = new SendFilesStatAction();

        $action->setRequestDetails(array(
            'body' => array(
                'event' => $this->getData(),
            ),
        ));

        $this->setkaEditorAPI->request($action);
    }

    /**
     * @return array
     */
    private function getData()
    {
        $send = $this->getCounters();

        $send['files_source'] = $this->useLocalFilesOption->get() ? 'self' : 'cdn';

        return $send;
    }

    /**
     * @return array
     */
    private function getCounters()
    {
        try {
            $counters  = $this->assetsStatus->getCountersByStatus();
            $converted = array();
            foreach (self::$countersTemplate as $index => &$status) {
                $converted[$index] = isset($counters[$status]) ? $counters[$status] : 0;
            }
        } catch (\Exception $exception) {
            $converted = array_map(
                function () {
                    return 0;
                },
                self::$countersTemplate
            );
        }
        return $converted;
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
     * @param OptionInterface $useLocalFilesOption
     * @return $this
     */
    public function setUseLocalFilesOption(OptionInterface $useLocalFilesOption)
    {
        $this->useLocalFilesOption = $useLocalFilesOption;
        return $this;
    }

    /**
     * @param AssetsStatus $assetsStatus
     */
    public function setAssetsStatus(AssetsStatus $assetsStatus)
    {
        $this->assetsStatus = $assetsStatus;
    }
}
