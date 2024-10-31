<?php
namespace Setka\Editor\Service\Gutenberg;

class BlockList
{
    /**
     * @var BlockInterface[]
     */
    protected $blocks = array();

    /**
     * @param BlockInterface $block
     * @return $this
     */
    public function addBlock(BlockInterface $block)
    {
        $this->blocks[] = $block;
        return $this;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        $html = '';

        foreach ($this->blocks as $block) {
            if ($block->getName()) {
                $html .= $block->getRaw();
            } else {
                $html .= $block->getContent();
                continue;
            }
        }

        return $html;
    }
}
