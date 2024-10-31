<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\Options\Special\BoolOption;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Admin\Options\Styles\ConfigIdOptionInterface;
use Setka\Editor\Admin\Options\Styles\ConfigOptionInterface;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\Service\Manager\ConfigSupportExtendedManager;
use Setka\Editor\Service\Manager\Exceptions\AttemptsLimitException;
use Setka\Editor\Service\Manager\Stacks\AnyFactory;
use Setka\Editor\Service\Manager\Stacks\PendingFactory;
use Setka\Editor\Service\SetkaPostTypes;

class BaseFilesManager extends ConfigSupportExtendedManager implements FilesManagerInterface
{
    /**
     * @var AttemptsToDownloadPostMeta
     */
    protected $attemptsToDownloadPostMeta;

    /**
     * @var integer
     */
    private $downloadAttempts;

    /**
     * @var string
     * @see SetkaPostTypes
     */
    protected $postTypesGroup;

    /**
     * @var array
     */
    protected $postTypes;

    /**
     * FilesManager constructor.
     *
     * @param AbstractSyncStageOption $currentStageOption
     * @param array $stagesMap
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param ConfigIdOptionInterface $configIdOption
     * @param ConfigOptionInterface $configOption
     * @param string $configPostType
     * @param BoolOption $failureOption
     * @param OptionInterface $failureNameOption
     * @param BoolOption $failureNoticeOption
     * @param BoolOption $attemptsLimitOption
     * @param BoolOption $finishedOption
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param integer $downloadAttempts
     * @param string $postTypesGroup
     * @param array $postTypes
     */
    public function __construct(
        AbstractSyncStageOption $currentStageOption,
        array $stagesMap,
        $continueExecution,
        LoggerInterface $logger,
        ConfigIdOptionInterface $configIdOption,
        ConfigOptionInterface $configOption,
        $configPostType,
        BoolOption $failureOption,
        OptionInterface $failureNameOption,
        BoolOption $failureNoticeOption,
        BoolOption $attemptsLimitOption,
        BoolOption $finishedOption,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts,
        $postTypesGroup,
        $postTypes
    ) {
        parent::__construct(
            $currentStageOption,
            $stagesMap,
            $continueExecution,
            $logger,
            $configIdOption,
            $configOption,
            $configPostType,
            $failureOption,
            $failureNameOption,
            $failureNoticeOption,
            $attemptsLimitOption,
            $finishedOption
        );
        $this->attemptsToDownloadPostMeta = $attemptsToDownloadPostMeta;
        $this->downloadAttempts           = $downloadAttempts;
        $this->postTypesGroup             = $postTypesGroup;
        $this->postTypes                  = $postTypes;
    }

    /**
     * @inheritDoc
     */
    public function checkPendingFiles()
    {
        try {
            $stage = new CheckPendingFilesStage(
                $this->continueExecution,
                $this->logger,
                new PendingFactory($this->postTypes),
                $this->attemptsToDownloadPostMeta,
                $this->downloadAttempts
            );
            $stage->run();
        } catch (AttemptsLimitException $exception) {
            $this->markLimitAttempts();
        } catch (OutOfTimeException $exception) {
        } catch (\Exception $exception) {
            $this->saveFailure($exception);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function deleteAllFiles()
    {
        $stage = new RemoveOldEntriesStage(
            $this->continueExecution,
            $this->logger,
            new AnyFactory($this->postTypes)
        );
        $stage->run();

        return $this;
    }
}
