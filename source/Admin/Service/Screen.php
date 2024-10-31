<?php
namespace Setka\Editor\Admin\Service;

class Screen
{
    /**
     * @var callable
     */
    private $screenFactory;

    public function __construct(callable $screenFactory)
    {
        $this->screenFactory = $screenFactory;
    }

    /**
     * @return bool
     */
    public function isBlockEditor(): bool
    {
        $screen = call_user_func($this->screenFactory);

        return is_object($screen) &&
               method_exists($screen, 'is_block_editor') &&
               $screen->is_block_editor();
    }

    /**
     * @return ?string
     */
    public function getParentBase(): ?string
    {
        $screen = call_user_func($this->screenFactory);

        return is_object($screen) && property_exists($screen, 'parent_base') ? $screen->parent_base : null;
    }

    /**
     * @return ?string
     */
    public function getBase(): ?string
    {
        $screen = call_user_func($this->screenFactory);

        return is_object($screen) && property_exists($screen, 'base') ? $screen->base : null;
    }

    public function getId(): ?string
    {
        $screen = call_user_func($this->screenFactory);

        return is_object($screen) && property_exists($screen, 'id') ? $screen->id : null;
    }

    public function getAction(): ?string
    {
        $screen = call_user_func($this->screenFactory);

        return is_object($screen) && property_exists($screen, 'action') ? $screen->action : null;
    }
}
