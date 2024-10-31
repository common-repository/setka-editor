<?php
namespace Setka\Editor\Admin\Service\FilesCreator;

use Setka\Editor\Admin\Options\Files\FilesOption;
use Setka\Editor\Admin\Service\FilesCreator\Exceptions\CantCreateMetaException;
use Setka\Editor\Admin\Service\FilesCreator\Exceptions\CantCreatePostException;
use Setka\Editor\Admin\Service\FilesCreator\Exceptions\UpdatePostException;
use Setka\Editor\PostMetas\FileSubPathPostMeta;
use Setka\Editor\PostMetas\OriginUrlPostMeta;
use Setka\Editor\PostMetas\SetkaFileTypePostMeta;
use Setka\Editor\PostMetas\SetkaFileIDPostMeta;
use Setka\Editor\Service\Manager\FilesManager\File;
use Setka\Editor\Service\PostStatuses;
use Setka\Editor\Service\SetkaPostTypes;

/**
 * Creates entries for each file from FilesOption.
 *
 * The class check for already existed file entry in DB by URL
 * and if this file exists then post_status updated to draft.
 */
class FilesCreator
{
    /**
     * @var FilesOption
     */
    private $filesOption;

    /**
     * @var array List of files from $this->filesOption.
     */
    private $filesList;

    /**
     * @var OriginUrlPostMeta
     */
    private $originUrlMeta;

    /**
     * @var SetkaFileIDPostMeta
     */
    private $setkaFileIDMeta;

    /**
     * @var SetkaFileTypePostMeta
     */
    private $setkaFileTypeMeta;

    /**
     * @var FileSubPathPostMeta
     */
    private $fileSubPathPostMeta;

    /**
     * @var callable Callback which checked after each iteration in $this->syncFiles().
     */
    private $continueExecution;

    /**
     * @param FilesOption $filesOption
     * @param callable $continueExecution
     */
    public function __construct(FilesOption $filesOption, $continueExecution)
    {
        $this->filesOption         = $filesOption;
        $this->continueExecution   = $continueExecution;
        $this->originUrlMeta       = new OriginUrlPostMeta();
        $this->setkaFileIDMeta     = new SetkaFileIDPostMeta();
        $this->setkaFileTypeMeta   = new SetkaFileTypePostMeta();
        $this->fileSubPathPostMeta = new FileSubPathPostMeta();
    }

    /**
     * Creates the posts in DB.
     *
     * @see createPostsHandler
     *
     * @throws \Exception
     *
     * @return mixed Result from other method.
     */
    public function createPosts()
    {
        return $this->createPostsHandler();
    }

    /**
     * Creates the file entries if they not exists.
     * Or update post_status to draft if this entry exists.
     * @return $this For chain calls.
     * @throws CantCreateMetaException
     * @throws CantCreatePostException
     * @throws UpdatePostException
     */
    private function createPostsHandler()
    {
        $this->filesList = $this->filesOption->get();

        if (empty($this->filesList)) {
            return $this;
        }

        foreach ($this->filesList as $item) {
            $query = new \WP_Query(array(
                'post_type' => SetkaPostTypes::FILE_POST_NAME,
                'post_status' => PostStatuses::ANY,
                'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    array(
                        'key' => $this->originUrlMeta->getName(),
                        'value' => $item['url'],
                    ),
                ),

                // Don't save result into cache since this used only by cron.
                'cache_results' => false,

                'posts_per_page' => 1,
            ));

            // Check can we do next iteration
            call_user_func($this->continueExecution);

            // Check if file already exists?
            if ($query->have_posts()) {
                // Update existing entry in DB
                $post = $query->next_post();

                if (PostStatuses::ARCHIVE === $post->post_status) {
                    $post->post_status = PostStatuses::DRAFT;
                    $result            = wp_update_post($post);

                    if (is_int($result) && $result > 0) {
                        continue;
                    } else {
                        throw new UpdatePostException();
                    }
                }
            } else {
                // Create new post. Draft means that file not downloaded.
                $post = new \WP_Post(new \stdClass());

                $post->post_type   = SetkaPostTypes::FILE_POST_NAME;
                $post->post_status = PostStatuses::DRAFT;

                $postID = wp_insert_post($post, true);

                if (is_int($postID) && $postID > 0) {
                    $this->originUrlMeta->setPostId($postID);
                    $postMetaURL = $this->originUrlMeta->updateValue($item['url']);
                    $postMetaURL = $this->isPostMetaCreated($postMetaURL);

                    $this->setkaFileIDMeta->setPostId($postID);
                    $postMetaSetkaID = $this->setkaFileIDMeta->updateValue($item['id']);
                    $postMetaSetkaID = $this->isPostMetaCreated($postMetaSetkaID);

                    $this->setkaFileTypeMeta->setPostId($postID);
                    $postMetaSetkaFileType = $this->setkaFileTypeMeta->updateValue($item['filetype']);
                    $postMetaSetkaFileType = $this->isPostMetaCreated($postMetaSetkaFileType);

                    $file = new File($post, $item['url']);
                    $this->fileSubPathPostMeta->setPostId($postID);
                    $postMetaFileSubPathPostMeta = $this->fileSubPathPostMeta->updateValue($file->getSubPath());
                    $postMetaFileSubPathPostMeta = $this->isPostMetaCreated($postMetaFileSubPathPostMeta);

                    if (!$postMetaURL || !$postMetaSetkaID || !$postMetaSetkaFileType || !$postMetaFileSubPathPostMeta) {
                        throw new CantCreateMetaException();
                    }
                } else {
                    throw new CantCreatePostException();
                }
            }
        }

        return $this;
    }

    /**
     * Check if meta saved.
     *
     * @param $meta mixed Result of updating meta.
     *
     * @return bool True if meta created, false otherwise.
     */
    private function isPostMetaCreated($meta)
    {
        if ((is_int($meta) && $meta > 0) || true === $meta) {
            return true;
        }
        return false;
    }
}
