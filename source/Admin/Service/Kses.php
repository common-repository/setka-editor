<?php
namespace Setka\Editor\Admin\Service;

use Setka\Editor\Admin\User\Capabilities\UseEditorCapability;

/**
 * Class Kses adds custom data attributes as allowed.
 *
 * IMPORTANT: call $this->allowedHTML() method only if WordPress users methods already loaded to prevent fatal errors.
 */
class Kses
{
    /**
     * @var array List of required HTML tags for Setka Editor.
     */
    protected $tags = array(
        'p',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'a',
        'b',
        'strong',
        'i',
        'em',
        'u',
        'del',
        'sup',
        'div',
        'span',
        'figure',
        'figcaption',
        'code',
        'img',
        'hr',
        'style',
    );

    /**
     * @var array List of HTML tag attributes for Setka Editor.
     */
    protected $attributes = array(
        'data-anim' => true,
        'data-anim-delay' => true,
        'data-anim-direction' => true,
        'data-anim-duration' => true,
        'data-anim-hash' => true,
        'data-anim-loop' => true,
        'data-anim-m' => true,
        'data-anim-m-delay' => true,
        'data-anim-m-direction' => true,
        'data-anim-m-duration' => true,
        'data-anim-m-hash' => true,
        'data-anim-m-loop' => true,
        'data-anim-m-name' => true,
        'data-anim-m-opacity' => true,
        'data-anim-m-played' => true,
        'data-anim-m-rotation' => true,
        'data-anim-m-scroll' => true,
        'data-anim-m-scroll-finish' => true,
        'data-anim-m-scroll-start' => true,
        'data-anim-m-shift' => true,
        'data-anim-m-sticky' => true,
        'data-anim-m-sticky-direction' => true,
        'data-anim-m-sticky-offset' => true,
        'data-anim-m-trigger' => true,
        'data-anim-m-type' => true,
        'data-anim-m-zoom' => true,
        'data-anim-name' => true,
        'data-anim-opacity' => true,
        'data-anim-played' => true,
        'data-anim-rotation' => true,
        'data-anim-scroll' => true,
        'data-anim-scroll-finish' => true,
        'data-anim-scroll-start' => true,
        'data-anim-shift' => true,
        'data-anim-sticky' => true,
        'data-anim-sticky-direction' => true,
        'data-anim-sticky-offset' => true,
        'data-anim-trigger' => true,
        'data-anim-type' => true,
        'data-anim-zoom' => true,
        'data-caption-disabled' => true,
        'data-ce-tag' => true,
        'data-cell-header' => true,
        'data-col-index' => true,
        'data-col-width' => true,
        'data-container-name' => true,
        'data-discussion-id' => true,
        'data-editor-version' => true,
        'data-embed-link' => true,
        'data-embed-mode' => true,
        'data-embed-responsive' => true,
        'data-image-id' => true,
        'data-image-name' => true,
        'data-instance-id' => true,
        'data-layout' => true,
        'data-layout-type' => true,
        'data-reset-type' => true,
        'data-responsive-type' => true,
        'data-stk' => true,
        'data-stk-action' => true,
        'data-stk-button' => true,
        'data-stk-click' => true,
        'data-stk-code' => true,
        'data-stk-css' => true,
        'data-stk-footnote' => true,
        'data-stk-footnote-body' => true,
        'data-stk-footnote-link' => true,
        'data-stk-hr' => true,
        'data-stk-id' => true,
        'data-stk-images' => true,
        'data-stk-key' => true,
        'data-stk-meta' => true,
        'data-stk-placeholder' => true,
        'data-stk-show' => true,
        'data-stk-sizes' => true,
        'data-stk-test' => true,
        'data-ui-id' => true,
        'style' => true,
    );

    /**
     * @var array Received HTML tags.
     */
    protected $incomeTags;

    /**
     * Setka Editor requires additional data-attributes and tags in HTML markup for posts.
     * We just add it to current WordPress list.
     *
     * @see current_user_can()
     *
     * @param $allowedPostTags array The list of html tags and their attributes.
     * @param $context string The name of context.
     *
     * @return array Array with required tags and attributes for Setka Editor.
     */
    public function allowedHTML($allowedPostTags, $context)
    {
        if ('post' === $context && current_user_can(UseEditorCapability::NAME)) {
            $this->incomeTags =& $allowedPostTags;
            $this->addRequiredTagsAndAttributes();
            return $this->incomeTags;
        }
        return $allowedPostTags;
    }

    /**
     * Adds required tags and attributes.
     *
     * @return $this
     */
    private function addRequiredTagsAndAttributes()
    {
        foreach ($this->tags as $tag) {
            if (isset($this->incomeTags[$tag])) {
                $this->incomeTags[$tag] = array_merge($this->incomeTags[$tag], $this->attributes);
            } else {
                $this->incomeTags[$tag] = $this->attributes;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     * @return $this
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param $content string
     * @return string
     */
    public static function filterBaseMarkup($content)
    {
        return wp_kses(
            $content,
            array(
                'a' => array('href' => true, 'target' => true),
                'code' => array(),
            ),
            array('http', 'https', 'mailto')
        );
    }
}
