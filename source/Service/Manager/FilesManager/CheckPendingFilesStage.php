<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Psr\Log\LoggerInterface;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\Service\Manager\AbstractStage;
use Setka\Editor\Service\Manager\Exceptions\AttemptsLimitException;
use Setka\Editor\Service\Manager\PostOperationsTrait;
use Setka\Editor\Service\Manager\Stacks\PendingFactoryInterface;
use Setka\Editor\Service\PostStatuses;

class CheckPendingFilesStage extends AbstractStage
{
    use PostOperationsTrait;

    /**
     * @var PendingFactoryInterface
     */
    private $pendingFactory;

    /**
     * @var AttemptsToDownloadPostMeta
     */
    private $attemptsToDownloadPostMeta;

    /**
     * @var integer
     */
    private $downloadAttempts;

    /**
     * CheckPendingFilesStage constructor.
     *
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param PendingFactoryInterface $pendingFactory
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     * @param int $downloadAttempts
     */
    public function __construct(
        $continueExecution,
        LoggerInterface $logger,
        PendingFactoryInterface $pendingFactory,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta,
        $downloadAttempts
    ) {
        parent::__construct($continueExecution, $logger);
        $this->pendingFactory             = $pendingFactory;
        $this->attemptsToDownloadPostMeta = $attemptsToDownloadPostMeta;
        $this->downloadAttempts           = $downloadAttempts;
    }

    /**
     * Transfer pending files back to download queue.
     *
     * @throws AttemptsLimitException
     * @throws \Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException
     * @throws \Setka\Editor\Service\Manager\Exceptions\PostException
     */
    public function run()
    {
        do {
            $query = $this->pendingFactory->createQuery();
            $this->continueExecution();

            if ($query->have_posts()) {
                $post = $query->next_post();
                $this->logger->debug('Pending file.', array('id' => $post->ID));

                $this->attemptsToDownloadPostMeta->setPostId($post->ID)->deleteLocal();
                $attempts = (int) $this->attemptsToDownloadPostMeta->get();

                if ($attempts < $this->downloadAttempts) {
                    $this->markDraft($post);
                    $this->updatePost($post);
                } else {
                    throw new AttemptsLimitException();
                }
                $query->rewind_posts();
            }
        } while ($query->have_posts());
    }

    /**
     * @param \WP_Post $post
     */
    private function markDraft(\WP_Post $post)
    {
        $post->post_status = PostStatuses::DRAFT;
    }
}
