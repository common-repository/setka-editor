<?php
namespace Setka\Editor\Admin\Pages;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\PostMeta\PostMetaInterface;
use Setka\Editor\Admin\User\Capabilities\UseEditorCapability;
use Setka\Editor\Plugin;

/**
 * Class QuickLinks
 */
class QuickLinks
{
    /**
     * @var PostMetaInterface
     */
    protected $useEditorPostMeta;

    /**
     * @var OptionInterface
     */
    protected $editorAccessPostTypesOption;

    /**
     * @var boolean True if Edit posts page, false otherwise.
     */
    protected $isPostListPage = false;

    /**
     * QuickLinks constructor.
     * @param PostMetaInterface $useEditorPostMeta
     * @param OptionInterface $editorAccessPostTypesOption
     */
    public function __construct(
        PostMetaInterface $useEditorPostMeta,
        OptionInterface $editorAccessPostTypesOption
    ) {
        $this->useEditorPostMeta           = $useEditorPostMeta;
        $this->editorAccessPostTypesOption = $editorAccessPostTypesOption;
    }

    /**
     * @return $this For chain calls.
     */
    public function addFilters()
    {
        $this->setupIsPostListPage();

        $postTypes = $this->editorAccessPostTypesOption->get();
        if (!empty($postTypes)) {
            foreach ($postTypes as $postType) {
                add_filter('views_edit-' . $postType, array($this, 'addLinks'));
            }
            add_action('pre_get_posts', array($this, 'preGetPosts'), 10, 1);
        }

        return $this;
    }

    /**
     * @return bool True if current user allowed to edit Setka Editor posts.
     */
    public function isAllowed()
    {
        return current_user_can(UseEditorCapability::NAME);
    }

    /**
     * @param $views array List of links.
     * @return array Modified list of links.
     */
    public function addLinks($views)
    {
        $screen = get_current_screen();
        $class  = $this->isSetkaCurrentPage() ? 'current' : '';
        $url    = add_query_arg(array(Plugin::NAME => ''), $screen->parent_file);

        if ('current' === $class) {
            $ariaCurrent = 'aria-current="page"';
        } else {
            $ariaCurrent = '';
        }

        $views['setka-editor'] = sprintf(
            '<a href="%s" class="%s" %s>%s</a>',
            esc_url($url),
            esc_attr($class),
            $ariaCurrent,
            __('Setka Editor', Plugin::NAME)
        );

        return $views;
    }

    /**
     * @param $query \WP_Query
     */
    public function preGetPosts(\WP_Query $query)
    {
        if ($this->isSetkaCurrentPage()) {
            $query->set('meta_key', $this->useEditorPostMeta->getName()); // phpcs:ignore WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts
            $query->set('meta_value', '1'); // phpcs:ignore WordPressVIPMinimum.Hooks.PreGetPosts.PreGetPosts
        }
    }

    /**
     * @return bool
     */
    public function isPostListPage()
    {
        return $this->isPostListPage;
    }

    /**
     * @param $isPostListPage bool
     * @return $this
     */
    public function setIsPostListPage($isPostListPage)
    {
        $this->isPostListPage = $isPostListPage;
        return $this;
    }

    /**
     * @return $this
     */
    public function setupIsPostListPage()
    {
        global $pagenow;
        if ('edit.php' ===  $pagenow) {
            $this->setIsPostListPage(true);
        } else {
            $this->setIsPostListPage(false);
        }
        return $this;
    }

    /**
     * @return bool True If current page with Setka Editor Posts.
     */
    public function isSetkaCurrentPage()
    {
        if ($this->isPostListPage() && isset($_GET[Plugin::NAME])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return true;
        }
        return false;
    }
}
