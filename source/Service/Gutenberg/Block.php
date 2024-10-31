<?php
namespace Setka\Editor\Service\Gutenberg;

use Setka\Editor\Service\Gutenberg\Exceptions\DomainException;

class Block implements BlockInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    private $prefixCoreName = 'core/';

    /**
     * @var integer
     */
    private $prefixCoreNameLength = 5;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var string
     */
    protected $content;

    /**
     * @inheritdoc
     * @throws DomainException
     */
    public function getRaw()
    {
        return sprintf(
            '<!-- wp:%1$s %2$s-->%3$s<!-- /wp:%1$s -->',
            $this->getNameForRaw(),
            ($this->attributes) ? $this->prepareAttributes() . ' ' : '',
            $this->getRendered()
        );
    }

    protected function getNameForRaw()
    {
        if (substr($this->name, 0, $this->prefixCoreNameLength) === $this->prefixCoreName) {
            $name = substr($this->name, $this->prefixCoreNameLength);
        } else {
            $name =& $this->name;
        }

        return $name;
    }

    /**
     * @return string
     */
    protected function prepareAttributes()
    {
        $attributes = wp_json_encode($this->attributes);

        if (!is_string($attributes)) {
            throw new DomainException();
        }

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getRendered()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @inheritdoc
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
}
