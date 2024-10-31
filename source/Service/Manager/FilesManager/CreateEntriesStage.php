<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Psr\Log\LoggerInterface;
use Setka\Editor\Admin\Options\Styles\AbstractStylesAggregateOption;
use Setka\Editor\Admin\Service\ContinueExecution\OutOfTimeException;
use Setka\Editor\PostMetas\AttemptsToDownloadPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\Service\Manager\AbstractStage;
use Setka\Editor\Service\Manager\Exceptions\InvalidConfigException;
use Setka\Editor\Service\Manager\Exceptions\PostException;
use Setka\Editor\Service\Manager\PostOperationsTrait;
use Setka\Editor\Service\Manager\Stacks\ArchiveByFileUrlFactory;
use Setka\Editor\Service\PostStatuses;

class CreateEntriesStage extends AbstractStage
{
    use PostOperationsTrait;

    /**
     * @var AbstractStylesAggregateOption
     */
    private $configOption;

    /**
     * @var array
     */
    private $postTypes;

    /**
     * @var ArchiveByFileUrlFactory
     */
    private $archiveFactory;

    /**
     * @var PostMetaInterface[]
     */
    private $metaMap;

    /**
     * CreateEntriesStage constructor.
     *
     * @param callable $continueExecution
     * @param LoggerInterface $logger
     * @param AbstractStylesAggregateOption $configOption
     * @param array $postTypes
     * @param OriginUrlPostMeta $originUrlPostMeta
     * @param SetkaFileTypePostMeta $setkaFileTypePostMeta
     * @param AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
     */
    public function __construct(
        $continueExecution,
        LoggerInterface $logger,
        AbstractStylesAggregateOption $configOption,
        array $postTypes,
        OriginUrlPostMeta $originUrlPostMeta,
        SetkaFileTypePostMeta $setkaFileTypePostMeta,
        AttemptsToDownloadPostMeta $attemptsToDownloadPostMeta
    ) {
        parent::__construct($continueExecution, $logger);

        $this->configOption   = $configOption;
        $this->archiveFactory = new ArchiveByFileUrlFactory($postTypes, $originUrlPostMeta);
        $this->postTypes      = $postTypes;

        $this->metaMap = array(
            array($originUrlPostMeta, AbstractStylesAggregateOption::FILE_URL),
            array($setkaFileTypePostMeta, AbstractStylesAggregateOption::FILE_TYPE),
            array($attemptsToDownloadPostMeta, false), // false mean that value will be deleted.
        );
    }

    /**
     * @throws PostException
     * @throws OutOfTimeException
     */
    public function run()
    {
        $config = $this->configOption->get();

        foreach ($this->postTypes as $sectionName => $postType) {
            if (!isset($config[$sectionName])) {
                throw new InvalidConfigException();
            }

            foreach ($config[$sectionName] as $index => &$file) {
                if (isset($file[AbstractStylesAggregateOption::FILE_WP_ID]) &&
                    isset($file[AbstractStylesAggregateOption::FILE_WP_PATH])) {
                    continue;
                }

                $query = $this->archiveFactory->createQueryByPostTypeAndFileUrl(
                    $postType,
                    $file[AbstractStylesAggregateOption::FILE_URL]
                );

                $this->continueExecution();

                $post = $query->have_posts() ? $query->next_post() : null;
                $post = $this->createDraftPost($postType, $file[AbstractStylesAggregateOption::FILE_ID], $post);
                $this->createOrUpdateEntry($post, $file);

                $fileInstance = new File($post, $file[AbstractStylesAggregateOption::FILE_URL]);

                $file[AbstractStylesAggregateOption::FILE_WP_ID]   = $post->ID;
                $file[AbstractStylesAggregateOption::FILE_WP_PATH] = $fileInstance->getSubPath();

                $this->configOption->updateValue($config);

                $this->logger->debug('Post for file.', array(
                    'section_name' => $sectionName,
                    'id' => $post->ID,
                    'name' => $post->post_name,
                ));
            }
        }

        $this->logger->debug('The config was updated.', $config);
    }

    /**
     * @param string $type
     * @param string $name
     * @param ?\WP_Post $post
     *
     * @return \WP_Post
     */
    private function createDraftPost($type, $name, ?\WP_Post $post)
    {
        if (!$post) {
            $post = new \WP_Post(new \stdClass()); // \WP_Post requires an object in constructor.
        }

        $post->post_type   = $type;
        $post->post_status = PostStatuses::DRAFT;
        $post->post_name   = $name;

        return $post;
    }

    /**
     * @param \WP_Post $post
     * @param array $file
     * @throws PostException
     */
    public function createOrUpdateEntry(\WP_Post $post, array &$file)
    {
        $this->logger->debug('Start creating/updating post.');

        $this->insertPost($post);

        foreach ($this->metaMap as $meta) {
            $meta[0]->setPostId($post->ID)->deleteLocal();
            if (is_string($meta[1])) {
                if ($meta[0]->get() !== $file[$meta[1]]) {
                    $meta[0]->updateValue($file[$meta[1]]);
                    $this->logger->debug(
                        'Post meta updated.',
                        array('name' => $meta[0]->getName(), 'value' => $file[$meta[1]])
                    );
                }
            } else {
                $meta[0]->delete();
            }
        }
    }
}
