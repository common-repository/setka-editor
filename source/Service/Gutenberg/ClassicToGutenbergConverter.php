<?php
namespace Setka\Editor\Service\Gutenberg;

use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\Gutenberg\Exceptions\DomainException;
use Setka\Editor\Service\Gutenberg\Exceptions\NonSetkaEditorException;

class ClassicToGutenbergConverter
{
    /**
     * @var string
     */
    protected $blockName;

    /**
     * @var \WP_Post
     */
    protected $originalPost;

    /**
     * @var UseEditorPostMeta
     */
    protected $useEditorPostMeta;

    /**
     * @var PostMetaInterface[]
     */
    protected $attributesMap;

    /**
     * @var SetkaEditorBlock
     */
    protected $block;

    /**
     * @param string $blockName
     * @param \WP_Post $originalPost
     * @param PostLayoutPostMeta $postLayoutPostMeta
     * @param PostThemePostMeta $postThemePostMeta
     * @param UseEditorPostMeta $useEditorPostMeta
     */
    public function __construct(
        $blockName,
        \WP_Post $originalPost,
        PostLayoutPostMeta $postLayoutPostMeta,
        PostThemePostMeta $postThemePostMeta,
        UseEditorPostMeta $useEditorPostMeta
    ) {
        $this->blockName         = $blockName;
        $this->originalPost      = $originalPost;
        $this->useEditorPostMeta = $useEditorPostMeta;

        $this->attributesMap = array(
            SetkaEditorBlock::ATTRIBUTE_LAYOUT => $postLayoutPostMeta,
            SetkaEditorBlock::ATTRIBUTE_THEME => $postThemePostMeta,
        );
    }

    /**
     * @return SetkaEditorBlock
     * @throws NonSetkaEditorException
     * @throws DomainException
     */
    public function convert()
    {
        $this
            ->checkForPostMeta()
            ->checkForBlocks()
            ->createBlockInstance()
            ->generateAttributes();

        return $this->block;
    }

    /**
     * @return $this
     * @throws NonSetkaEditorException
     */
    protected function checkForPostMeta()
    {
        if (!$this->useEditorPostMeta->setPostId($this->originalPost->ID)->get()) {
            throw new NonSetkaEditorException();
        }

        return $this;
    }

    /**
     * @return $this
     * @throws DomainException
     */
    protected function checkForBlocks()
    {
        $hasBlocks = has_blocks($this->originalPost);

        if ($hasBlocks) {
            throw new DomainException();
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function createBlockInstance()
    {
        $this->block = new SetkaEditorBlock();
        $this->block->setName($this->blockName)->setContent($this->originalPost->post_content);
        return $this;
    }

    /**
     * @return $this
     */
    protected function generateAttributes()
    {
        $attributes = array();

        foreach ($this->attributesMap as $key => $meta) {
            if ($meta->setPostId($this->originalPost->ID)->isValid()) {
                $attributes[$key] = $meta->get();
            }
        }

        $this->block->setAttributes($attributes);

        return $this;
    }
}
