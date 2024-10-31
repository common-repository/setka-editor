<?php
namespace Setka\Editor\Service\Gutenberg;

interface BlockInterface
{
    const VAR_NAME = 'blockName';

    const VAR_ATTRS = 'attrs';

    const VAR_INNER_HTML = 'innerHTML';

    /**
     * @return string
     */
    public function getRaw();

    /**
     * @return string
     */
    public function getRendered();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes);

    /**
     * @return string
     */
    public function getContent();

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content);
}
