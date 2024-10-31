<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\ScriptsStyles\AbstractScriptsStyles;
use Korobochkin\WPKit\ScriptsStyles\ScriptsStylesInterface;
use Setka\Editor\Admin\Ajax\DismissNoticesAction;
use Setka\Editor\Admin\Service\Js\EditorAdapterJsSettings;
use Setka\Editor\Plugin;

class AdminScriptStyles extends AbstractScriptsStyles implements ScriptsStylesInterface
{
    /**
     * @var EditorAdapterJsSettings
     */
    protected $editorAdapterJsSettings;

    /**
     * @var Screen
     */
    protected $screen;

    /**
     * @inheritdoc
     */
    public function register()
    {
        $file = 'assets/build/css/admin/main' . (($this->dev) ? '.css' : '.min.css');
        wp_register_style(
            'setka-editor-wp-admin-main',
            $this->baseUrl . $file,
            array(),
            Plugin::VERSION
        );

        $file = 'assets/build/editor-adapter.bundle.js';
        wp_register_script(
            'setka-editor-wp-admin-editor-adapter',
            $this->baseUrl . $file,
            array('jquery', 'backbone', 'setka-editor-editor', 'wp-pointer', 'dompurify'), //'dompurify'
            Plugin::VERSION,
            true
        );

        $file = 'assets/build/editor-adapter-initializer.bundle.js';
        wp_register_script(
            'setka-editor-wp-admin-editor-adapter-initializer',
            $this->baseUrl . $file,
            array('setka-editor-wp-admin-editor-adapter', 'uri-js'),
            Plugin::VERSION,
            true
        );

        $file = 'assets/build/setting-pages-initializer/setting-pages-initializer' .
                (($this->dev) ? '.js' : '.min.js');

        wp_register_script(
            'setka-editor-wp-admin-setting-pages-initializer',
            $this->baseUrl . $file,
            array('underscore'),
            Plugin::VERSION,
            true
        );

        wp_register_script(
            'setka-editor-wp-admin-common',
            $this->baseUrl . 'assets/build/common/common.min.js',
            array('jquery'),
            Plugin::VERSION,
            true
        );

        wp_register_script(
            'setka-editor-wp-admin-gutenberg-tweaks',
            $this->baseUrl . 'assets/build/gutenberg-tweaks.bundle.js',
            array(),
            Plugin::VERSION,
            true
        );

        return $this;
    }

    /**
     * Enqueue scripts & styles.
     *
     * @return $this For chain calls.
     */
    public function enqueue()
    {
        $this->enqueueForAllPages();
        if ('edit' === $this->screen->getBase()) {
            $this->enqueueForPostCatalogPages();
        }
        return $this;
    }

    /**
     * Enqueue scripts & styles for all /wp-admin/ pages.
     *
     * @return $this For chain calls.
     */
    public function enqueueForAllPages()
    {
        wp_enqueue_style('setka-editor-wp-admin-main');
        $this->localizeAdminCommon();
        wp_enqueue_script('setka-editor-wp-admin-common');
        return $this;
    }

    /**
     * Enqueue scripts & styles for pages with posts table (list).
     *
     * @return $this For chain calls.
     */
    public function enqueueForPostCatalogPages()
    {
        wp_enqueue_script('setka-editor-wp-admin-gutenberg-tweaks');
        return $this;
    }

    /**
     * Localize Editor Adapter.
     *
     * @return $this For chain calls.
     */
    public function localizeAdminEditorAdapter()
    {
        $tabsPointer = array(
            'target' => '#wp-content-editor-tools .wp-editor-tabs',
            'options' => array(
                'pointerClass' => 'wp-pointer setka-editor-pointer-centered-arrow',
                'position' => array('edge' => 'top', 'align' => 'middle'),
            ),
        );

        wp_localize_script(
            'setka-editor-wp-admin-editor-adapter',
            'setkaEditorAdapterL10n',
            array(
                'view' => array(
                    'editor' => array(
                        'tabName' => _x('Setka Editor', 'editor tab name', Plugin::NAME),
                        'switchToDefaultEditorsConfirm' => __('Are you sure that you want to switch to default WordPress editor? You will lose all the formatting and design created in Setka Editor.', Plugin::NAME),
                        'switchToSetkaEditorConfirm' => __('Post will be converted by Setka Editor. Its appearance may change. This action can’t be undone. Continue?', Plugin::NAME)
                    ),
                ),
                'names' => array(
                    'css' => Plugin::NAME,
                    '_'   => Plugin::_NAME_
                ),
                'settings' => $this->getEditorAdapterJsSettings()->getSettings(),
                'pointers' => array(
                    'disabledEditorTabs' => array_replace_recursive($tabsPointer, array(
                        'options' => array(
                            'content' => sprintf(
                                '<h3>%s</h3><p>%s</p>',
                                __('Why Text and Visual tabs are blocked?', Plugin::NAME),
                                __('Posts created with Setka Editor may contain complex design elements that are not compatible with other post editors.', Plugin::NAME)
                            ),
                        ),
                    )),
                    'publishedPost' => array_replace_recursive($tabsPointer, array(
                        'target' => '#wp-content-editor-tools .wp-editor-tabs .switch-setka-editor',
                        'options' => array(
                            'content' => sprintf(
                                '<h3>%s</h3><p>%s</p>',
                                __('Why Setka Editor is blocked?', Plugin::NAME),
                                __('You can switch to Setka Editor only in the posts that are not published.', Plugin::NAME)
                            ),
                        ),
                    )),
                ),
            )
        );

        return $this;
    }

    /**
     * Localize common scripts for admin pages.
     *
     * @return $this For chain calls.
     */
    public function localizeAdminCommon()
    {
        wp_localize_script(
            'setka-editor-wp-admin-common',
            'setkaEditorCommon',
            array(
                'ajaxName' => Plugin::NAME,
                'notices' => array(
                    'dismissAction' => DismissNoticesAction::class,
                    'dismissIds' => array(
                        'wp-kit-notice-setka-editor_amp_sync_failure',
                    ),
                ),
            )
        );
        return $this;
    }

    /**
     * Enqueue scripts and styles for edit post page.
     *
     * @return $this For chain calls.
     */
    public function enqueueForEditPostPage()
    {
        // Editor
        wp_enqueue_script('setka-editor-editor');
        wp_enqueue_style('setka-editor-editor');
        wp_enqueue_style('setka-editor-theme-resources');

        // Editor Initializer for /wp-admin/ pages
        $this->localizeAdminEditorAdapter();
        wp_enqueue_script('setka-editor-wp-admin-editor-adapter-initializer');

        wp_enqueue_style('wp-pointer');

        return $this;
    }

    /**
     * @return EditorAdapterJsSettings
     */
    public function getEditorAdapterJsSettings()
    {
        return $this->editorAdapterJsSettings;
    }

    /**
     * @param EditorAdapterJsSettings $editorAdapterJsSettings
     *
     * @return $this
     */
    public function setEditorAdapterJsSettings($editorAdapterJsSettings)
    {
        $this->editorAdapterJsSettings = $editorAdapterJsSettings;
        return $this;
    }

    /**
     * @param Screen $screen
     */
    public function setScreen(Screen $screen)
    {
        $this->screen = $screen;
    }
}
