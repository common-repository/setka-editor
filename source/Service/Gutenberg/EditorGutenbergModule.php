<?php
namespace Setka\Editor\Service\Gutenberg;

use Setka\Editor\Entities\Post;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\AMP\AMP;
use Setka\Editor\Service\DataFactory;
use Setka\Editor\Service\Gutenberg\Exceptions\DomainException;
use Setka\Editor\Service\Gutenberg\Exceptions\NoBlocksException;
use Setka\Editor\Service\Gutenberg\Exceptions\NonSetkaEditorException;
use Setka\Editor\Service\ScriptStyles;
use Setka\Editor\Service\Standalone\StandaloneStyles;

class EditorGutenbergModule
{
    /**
     * @var \WP_Block_Type
     */
    protected $blockType;

    /**
     * @var ScriptStyles
     */
    protected $scriptStyles;

    /**
     * @var DataFactory
     */
    protected $dataFactory;

    /**
     * @var AMP
     */
    protected $amp;

    /**
     * @var StandaloneStyles
     */
    private $standalone;

    /**
     * EditorGutenbergModule constructor.
     * @param ScriptStyles $scriptStyles
     * @param DataFactory $dataFactory
     * @param AMP $amp
     * @param StandaloneStyles $standalone
     */
    public function __construct(ScriptStyles $scriptStyles, DataFactory $dataFactory, AMP $amp, StandaloneStyles $standalone)
    {
        $this->scriptStyles = $scriptStyles;
        $this->dataFactory  = $dataFactory;
        $this->amp          = $amp;
        $this->standalone   = $standalone;
    }

    /**
     * Setup additional things for Setka Editor posts.
     *
     * @param $attributes array Additional configuration for Setka Editor post.
     * @param $content string Post content.
     * @return string Post content. without any changes.
     */
    public function render(array $attributes, $content)
    {
        $map = $this->getAttributesToCallersMap();

        foreach ($map as $name => $callbacks) {
            if (isset($attributes[$name]) && is_string($attributes[$name])) {
                foreach ($callbacks as $callback) {
                    call_user_func($callback, $attributes[$name]);
                }
            }
        }

        return $content;
    }

    /**
     * @return array
     */
    protected function getAttributesToCallersMap()
    {
        return array(
            SetkaEditorBlock::ATTRIBUTE_LAYOUT => array(
                array($this->amp, 'requireLayout'),
                array($this->standalone, 'requireLayout'),
            ),
            SetkaEditorBlock::ATTRIBUTE_THEME => array(
                array($this->amp, 'requireTheme'),
                array($this->standalone, 'requireTheme'),
            ),
        );
    }

    /**
     * @param \WP_Post $post
     *
     * @return SetkaEditorBlock
     * @throws NonSetkaEditorException
     * @throws DomainException
     */
    public function convertFromClassicToGutenberg(\WP_Post $post)
    {
        $converter = new ClassicToGutenbergConverter(
            $this->getBlockName(),
            $post,
            $this->dataFactory->create(PostLayoutPostMeta::class),
            $this->dataFactory->create(PostThemePostMeta::class),
            $this->dataFactory->create(UseEditorPostMeta::class)
        );

        return $converter->convert();
    }

    /**
     * @param \WP_Post $post
     *
     * @return Post|\Setka\Editor\Entities\PostInterface
     * @throws NonSetkaEditorException
     * @throws NoBlocksException
     * @throws DomainException
     * @throws \Exception
     */
    public function convertFromGutenbergToClassic(\WP_Post $post)
    {
        $converter = new GutenbergToClassicConverter(
            $this->getBlockName(),
            $post,
            $this->dataFactory->create(UseEditorPostMeta::class)
        );
        return $converter->convert();
    }

    /**
     * @param \WP_Block_Type $blockType
     *
     * @return $this
     */
    public function setBlockType(\WP_Block_Type $blockType)
    {
        $this->blockType = $blockType;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockName()
    {
        return $this->blockType->name;
    }
}
