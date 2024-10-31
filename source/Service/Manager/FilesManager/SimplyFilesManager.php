<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractSyncStageOption;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Admin\Service\WPQueryFactory;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\Service\Manager\Exceptions\AttemptsLimitException;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\Manager;
use Setka\Editor\Service\Manager\Stacks\PendingFactory;

class SimplyFilesManager extends Manager implements FilesManagerInterface
{
    /**
     * @var AttemptsToDownloadPostMeta
     */
    protected $attemptsToDownloadPostMeta;

    /**
     * @var integer
     */
    protected $downloadAttempts;

    /**
     * @var string
     */
    private $postTypesGroup;

    /**
     * @var array
     */
    protected $postTypes;

    /**
     * SimplyFilesManager constructor.
     *
     * @param AbstractSyncStageOption $currentStageOption
     * @param array $stagesMap
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param int $downloadAttempts
     * @param string $postTypeGroup
     * @param array $postTypes
     */
    public function __construct(
        AbstractSyncStageOption $currentStageOption,
        array $stagesMap,
        $continueExecution,
        LoggerInterface $logger,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts,
        $postTypeGroup,
        array $postTypes
    ) {
        parent::__construct($currentStageOption, $stagesMap, $continueExecution, $logger);

        $this->attemptsToDownloadPostMeta = $attemptsToDownloadPostMeta;
        $this->downloadAttempts           = $downloadAttempts;
        $this->postTypesGroup             = $postTypeGroup;
        $this->postTypes                  = $postTypes;
    }

    /**
     * @inheritDoc
     * @throws AttemptsLimitException
     * @throws OutOfTimeException
     * @throws PostException
     */
    public function checkPendingFiles()
    {
        $stage = new CheckPendingFilesStage(
            $this->continueExecution,
            $this->logger,
            new PendingFactory($this->postTypes),
            $this->attemptsToDownloadPostMeta,
            $this->downloadAttempts
        );
        $stage->run();
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
            WPQueryFactory::createWherePostTypeGroupFactory($this->postTypesGroup)
        );
        $stage->run();

        return $this;
    }
}
