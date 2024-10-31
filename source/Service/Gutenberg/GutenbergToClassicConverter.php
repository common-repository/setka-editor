<?php
namespace Setka\Editor\Service\Gutenberg;

use Setka\Editor\Entities\Post;
use Setka\Editor\Entities\PostInterface;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\Gutenberg\Exceptions\DomainException;
use Setka\Editor\Service\Gutenberg\Exceptions\NoBlocksException;
use Setka\Editor\Service\Gutenberg\Exceptions\NonSetkaEditorException;

class GutenbergToClassicConverter
{
    /**
     * @var string Setka Editor block name.
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
     * @var array Parsed Gutenberg post.
     */
    protected $blocks;

    /**
     * @var array Link to Setka Editor Block (from $this->blocks).
     * @see findSetka
     */
    protected $block;

    /**
     * @var Post|PostInterface
     */
    protected $post;

    /**
     * @var string Converted content.
     */
    protected $content;

    /**
     * @param $blockName
     * @param \WP_Post $post
     * @param UseEditorPostMeta $useEditorPostMeta
     */
    public function __construct($blockName, \WP_Post $post, UseEditorPostMeta $useEditorPostMeta)
    {
        $this->blockName         = $blockName;
        $this->originalPost      = $post;
        $this->useEditorPostMeta = $useEditorPostMeta;
        $this->post              = new Post();
    }

    /**
     * @return Post|PostInterface
     * @throws NonSetkaEditorException
     * @throws NoBlocksException
     * @throws DomainException
     * @throws \Exception
     */
    public function convert()
    {
        $this
            ->checkForPostMeta()
            ->checkForBlocks()
            ->parse()
            ->checkNumberOfBlocks()
            ->findSetka()
            ->convertContent()
            ->convertAttributes()
            ->enableAutoInit();

        return $this->post;
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
     * @throws NoBlocksException
     */
    protected function checkForBlocks()
    {
        $hasBlocks = has_blocks($this->originalPost);

        if (!$hasBlocks) {
            throw new NoBlocksException();
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function parse()
    {
        $this->blocks = parse_blocks($this->originalPost->post_content);
        return $this;
    }

    /**
     * @throws DomainException
     * @return $this
     */
    protected function checkNumberOfBlocks()
    {
        if (count($this->blocks) === 1) {
            return $this;
        }

        throw new DomainException();
    }

    /**
     * @throws DomainException
     * @return $this
     */
    protected function findSetka()
    {
        foreach ($this->blocks as $block) {
            if ($this->blockName === $block['blockName']) {
                $this->block =& $block;
                return $this;
            }
        }
        throw new DomainException();
    }

    /**
     * @return $this
     * @throws DomainException
     */
    protected function convertContent()
    {
        $content = preg_replace(
            '/^\s*<div\s+class\s*=\s*"[^"]+"\s*>/m',
            '',
            $this->block['innerHTML'],
            1,
            $counter
        );

        if (1 !== $counter) {
            throw new DomainException();
        }

        unset($counter);

        $content = preg_replace(
            '/<\/div\s*>$/m',
            '',
            $content,
            1,
            $counter
        );

        if (1 !== $counter) {
            throw new DomainException();
        }

        $this->post->setContent($content);

        return $this;
    }

    /**
     * @return $this
     * @throws DomainException
     */
    protected function convertAttributes()
    {
        foreach ($this->getAttributesMap() as $attribute => $config) {
            if (isset($this->block['attrs'][$attribute])) {
                call_user_func(array($this->post, $config['setter']), $this->block['attrs'][$attribute]);
                continue;
            }

            if ($config['required']) {
                throw new DomainException('Required block attribute not found.');
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getAttributesMap()
    {
        return array(
            SetkaEditorBlock::ATTRIBUTE_LAYOUT => array(
                'setter' => 'setLayout',
                'required' => true,
            ),
            SetkaEditorBlock::ATTRIBUTE_THEME => array(
                'setter' => 'setTheme',
                'required' => true,
            ),
        );
    }

    /**
     * @return $this
     */
    protected function enableAutoInit()
    {
        $this->post->setAutoInit(true);
        return $this;
    }
}
