<?php
namespace Setka\Editor\Entities;

use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostFactory
{
    /**
     * @var string[]
     */
    protected $metaMap = array(
        PostLayoutPostMeta::class => 'setLayout',
        PostThemePostMeta::class => 'setTheme',
        UseEditorPostMeta::class => 'setAutoInit',
    );

    /**
     * @var PostMetaInterface[]
     */
    protected $postMeta = array();

    /**
     * PostFactory constructor.
     *
     * @param PostMetaInterface[] $postMeta
     */
    public function __construct(array $postMeta)
    {
        $this->postMeta = $this->getOptionsResolver()->resolve($postMeta);
    }

    /**
     * @param \WP_Post $originalPost
     *
     * @return PostInterface
     */
    public function createFromWPPost(\WP_Post $originalPost)
    {
        $post = new WPPostProxyPost($originalPost);

        foreach ($this->metaMap as $meta => $setter) {
            $meta = $this->getMeta($meta)->setPostId($originalPost->ID);
            if ($meta->isValid()) {
                call_user_func(array($post, $setter), $meta->get());
            }
        }

        return $post;
    }

    /**
     * @param $className string
     *
     * @return PostMetaInterface
     * @throws \OutOfRangeException
     */
    protected function getMeta($className)
    {
        if (!isset($this->postMeta[$className])) {
            throw new \OutOfRangeException();
        }
        return $this->postMeta[$className];
    }

    /**
     * @return OptionsResolver
     */
    protected function getOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(array_keys($this->metaMap));

        foreach ($this->metaMap as $key => $value) {
            $resolver->setAllowedTypes($key, $key);
        }

        return $resolver;
    }
}
