<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\Service\Manager\AbstractStage;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\PostOperationsTrait;
use Setka\Editor\Service\Manager\Stacks\StackItemFactoryInterface;

class RemoveOldEntriesStage extends AbstractStage
{
    use PostOperationsTrait;

    /**
     * @var StackItemFactoryInterface
     */
    private $queryFactory;

    /**
     * @var \WP_Post
     */
    protected $post;

    /**
     * RemoveOldEntriesStage constructor.
     *
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param StackItemFactoryInterface $queryFactory
     */
    public function __construct(
        $continueExecution,
        LoggerInterface $logger,
        StackItemFactoryInterface $queryFactory
    ) {
        parent::__construct($continueExecution, $logger);
        $this->queryFactory = $queryFactory;
    }

    /**
     * @throws OutOfTimeException
     * @throws PostException
     */
    public function run()
    {
        do {
            $query = $this->createQuery();
            $this->continueExecution();

            while ($query->have_posts()) {
                $this->post = $query->next_post();
                $this->delete();
                $this->deleteLog();
            }

            $query->rewind_posts();
        } while ($query->have_posts());
    }

    /**
     * @throws PostException
     */
    protected function delete()
    {
        $this->deletePost($this->post);
    }

    protected function deleteLog()
    {
        $this->logger->debug('Post deleted.', array('id' => $this->post->ID));
    }

    /**
     * @return \WP_Query
     */
    private function createQuery()
    {
        $query = $this->queryFactory->createQuery();
        $this->logger->debug(
            'Made query for posts.',
            array(
                'found_posts' => (int) $query->found_posts,
            )
        );
        return $query;
    }
}
