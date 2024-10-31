<?php
namespace Setka\Editor\Admin\Pages\Settings;

class PostType
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * PostType constructor.
     *
     * @param string $id
     * @param string|null $name
     */
    public function __construct($id, $name = null)
    {
        $this->id   = $id;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
