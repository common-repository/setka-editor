<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\Service\Manager\AbstractStage;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\PostOperationsTrait;
use Setka\Editor\Service\PostStatuses;

class ArchiveAndResetErrorsStage extends AbstractStage
{
    use PostOperationsTrait;

    /**
     * @var array
     */
    private $postTypes;

    /**
     * @var PostMetaInterface
     */
    private $attemptsToDownloadPostMeta;

    /**
     * @var array
     */
    private $queryParameters;

    /**
     * @var \WP_Post Current post.
     */
    private $post;

    /**
     * ArchiveAndResetErrorsAbstractStage constructor.
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param array $postTypes
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     */
    public function __construct(
        $continueExecution,
        LoggerInterface $logger,
        array $postTypes,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
    ) {
        parent::__construct($continueExecution, $logger);

        $this->postTypes                  = $postTypes;
        $this->attemptsToDownloadPostMeta = $attemptsToDownloadPostMeta;
    }

    /**
     * @throws PostException
     * @throws OutOfTimeException
     */
    public function run()
    {
        $this->queryParameters = $this->createQueryParameters();

        do {
            $query = $this->createQuery();
            $this->continueExecution();

            $this->logger->debug('Made query for posts.');

            $context = array('ids' => array(), 'counter' => (int) $query->found_posts);

            while ($query->have_posts()) {
                $this->post       = $query->next_post();
                $context['ids'][] = $this->post->ID;

                $this->archivePost();
                $this->resetAttemptsToDownload();
            }

            $this->logger->debug('Found posts.', $context);

            $query->rewind_posts();
        } while ($query->have_posts());

        $this->logger->info('Counters was removed for all posts.');
    }

    /**
     * @throws PostException
     */
    private function archivePost()
    {
        $this->post->post_status = PostStatuses::ARCHIVE;
        $this->updatePost($this->post);
    }

    private function resetAttemptsToDownload()
    {
        $this->attemptsToDownloadPostMeta->setPostId($this->post->ID)->delete();
    }

    /**
     * @return \WP_Query
     */
    private function createQuery()
    {
        return new \WP_Query($this->queryParameters);
    }

    /**
     * @return array
     */
    private function createQueryParameters()
    {
        return array(
            'post_type' => $this->postTypes,
            'posts_per_page' => 5,
            'post_status' => array(
                // Almost all post statuses except PostStatuses::ARCHIVE
                PostStatuses::PUBLISH,
                PostStatuses::DRAFT,
                PostStatuses::PENDING,
                PostStatuses::FUTURE,
                PostStatuses::TRASH,
            ),
            'orderby' => 'ID',

            // Don't save result into cache since this used only by cron.
            'cache_results' => false,
        );
    }
}
