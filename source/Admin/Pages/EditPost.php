<?php
namespace Setka\Editor\Admin\Pages;

use Setka\Editor\Admin\Options\EditorAccessPostTypesOption;
use Setka\Editor\Admin\Service\AdminScriptStyles;
use Setka\Editor\Admin\Service\Screen;
use Setka\Editor\Admin\User\Capabilities\UseEditorCapability;
use Setka\Editor\Entities\PostFactory;
use Setka\Editor\Entities\PostInterface;
use Setka\Editor\Service\Gutenberg\EditorGutenbergModule;
use Setka\Editor\Service\Gutenberg\Exceptions\ConversionNotRequiredException;
use Setka\Editor\Service\Gutenberg\Exceptions\DomainException;
use Setka\Editor\Service\ScriptStyles;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;
use Setka\Editor\Service\WPPostFactory;

class EditPost
{
    /**
     * @var boolean
     */
    private $gutenbergSupport;

    /**
     * @var ScriptStyles
     */
    private $scriptStyles;

    /**
     * @var AdminScriptStyles
     */
    private $adminScriptStyles;

    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var EditorAccessPostTypesOption
     */
    private $editorAccessPostTypesOption;

    /**
     * @var Screen
     */
    private $screen;

    /**
     * @var EditorGutenbergModule
     */
    private $editorGutenbergModule;

    /**
     * @var \WP_Post
     */
    private $originalPost;

    /**
     * @var PostInterface
     */
    private $post;

    /**
     * @var PostFactory
     */
    private $postFactory;

    /**
     * EditPost constructor.
     *
     * @param bool $gutenbergSupport
     * @param ScriptStyles $scriptStyles
     * @param AdminScriptStyles $adminScriptStyles
     * @param SetkaEditorAccount $setkaEditorAccount
     * @param EditorAccessPostTypesOption $editorAccessPostTypesOption
     * @param Screen $screen
     * @param EditorGutenbergModule $editorGutenbergModule
     * @param PostFactory $postFactory
     */
    public function __construct(
        $gutenbergSupport,
        ScriptStyles $scriptStyles,
        AdminScriptStyles $adminScriptStyles,
        SetkaEditorAccount $setkaEditorAccount,
        EditorAccessPostTypesOption $editorAccessPostTypesOption,
        Screen $screen,
        EditorGutenbergModule $editorGutenbergModule,
        PostFactory $postFactory
    ) {
        $this->gutenbergSupport            = $gutenbergSupport;
        $this->scriptStyles                = $scriptStyles;
        $this->adminScriptStyles           = $adminScriptStyles;
        $this->setkaEditorAccount          = $setkaEditorAccount;
        $this->editorAccessPostTypesOption = $editorAccessPostTypesOption;
        $this->screen                      = $screen;
        $this->editorGutenbergModule       = $editorGutenbergModule;
        $this->postFactory                 = $postFactory;
    }

    /**
     * @throws \Exception If current post not found.
     * @return $this
     */
    public function enqueueScripts()
    {
        if (!$this->isSetkaEditorSupported()) {
            return $this;
        }

        if (!$this->gutenbergSupport) { // WordPress < 5.0
            $this->setupGlobalPost();
            $this->adminScriptStyles->enqueueForEditPostPage();
            return $this;
        }

        if ($this->isGutenberg()) {
            $this->setupGlobalPost();
            $this->scriptStyles->localizeGutenbergBlocks()->enqueueForGutenberg();
        } else {
            $this->gutenbergToClassicConversion();
            $this->adminScriptStyles->enqueueForEditPostPage();
        }

        return $this;
    }

    /**
     * Should Setka Editor enqueued or not.
     *
     * @return bool Enable editor or not.
     */
    public function isSetkaEditorSupported()
    {
        if (!$this->checkUserPermission()) {
            return false;
        }

        if (!$this->setkaEditorAccount->isEditorResourcesAvailable()) {
            return false;
        }

        if (!$this->checkPostType($this->originalPost->post_type)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    protected function checkUserPermission()
    {
        return (bool) current_user_can(UseEditorCapability::NAME);
    }

    /**
     * @param $postType string Post type to check.
     * @return bool True if enabled.
     */
    protected function checkPostType($postType)
    {
        if (!is_string($postType) || empty($postType)) {
            return false;
        }

        return in_array(
            $postType,
            $this->editorAccessPostTypesOption->get(),
            true
        );
    }

    /**
     * @throws \UnexpectedValueException
     * @return $this
     */
    public function setupOriginalPostFromGlobal()
    {
        $this->originalPost = WPPostFactory::createFromGlobals();
        return $this;
    }

    /**
     * @return bool
     */
    protected function isGutenberg()
    {
        if (!$this->gutenbergSupport) {
            return false;
        }

        return $this->screen->isBlockEditor();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function gutenbergToClassicConversion()
    {
        try {
            $this->post = $this->editorGutenbergModule
                ->convertFromGutenbergToClassic($this->originalPost);

            $this->adminScriptStyles->getEditorAdapterJsSettings()->setPost($this->post);

            add_action('post_edit_form_tag', array($this, 'insertConvertedContent'), 10, 1);
        } catch (ConversionNotRequiredException $exception) {
            $this->setupGlobalPost(); // Not a Setka Editor post.
        } catch (DomainException $exception) {
            // Gutenberg post with Setka Editor but cannot be converted to Classic.
            add_filter('wp_editor_settings', array($this, 'preventTinyMCEInit'), 10, 2);
            $this->setupGlobalPost();
        }
        return $this;
    }

    /**
     * @param array $settings TinyMCE settings.
     * @param string $id Editor id.
     */
    public function preventTinyMCEInit(array $settings, $id)
    {
        if ('content' === $id) {
            $settings['default_editor'] = 'html';
        }
        return $settings;
    }

    /**
     * Replace passed post content by converted version.
     * @param \WP_Post $post
     */
    public function insertConvertedContent(\WP_Post $post)
    {
        $post->post_content = $this->post->getContent();
    }

    /**
     * @return $this
     */
    protected function setupGlobalPost()
    {
        $this->adminScriptStyles->getEditorAdapterJsSettings()->setPost(
            $this->postFactory->createFromWPPost($this->originalPost)
        );
        return $this;
    }
}
