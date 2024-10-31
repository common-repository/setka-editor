<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\Cron\CronSingleEventInterface;
use Setka\Editor\Admin\Options;
use Setka\Editor\Plugin;
use Setka\Editor\PostMetas\PostLayoutPostMeta;
use Setka\Editor\PostMetas\PostThemePostMeta;
use Setka\Editor\PostMetas\UseEditorPostMeta;
use Setka\Editor\Service\Config\PluginConfig;

/**
 * We save additional post meta by TWO ways:
 *
 *   1. On POST request when user click "Publish" button.
 *   2. On post auto save events. See the following files for more info:
 *      * wp-includes/js/autosave.js
 *      * wp-includes/js/heartbeat.js
 *      * wp-admin/includes/misc.php:854 (heartbeat_autosave function)
 *
 * Class SavePost
 * @package Setka\Editor\Admin\Service
 */
class SavePost
{
    /**
     * @var boolean
     */
    private $doingAJAX;

    /**
     * @var Options\SetkaPostCreatedOption
     */
    protected $setkaPostCreatedOption;

    /**
     * @var CronSingleEventInterface
     */
    protected $setkaPostCreatedCronEvent;

    /**
     * @var UseEditorPostMeta
     */
    protected $useEditorPostMeta;

    /**
     * @var PostThemePostMeta
     */
    protected $postThemePostMeta;

    /**
     * @var PostLayoutPostMeta
     */
    protected $postLayoutPostMeta;

    /**
     * @var boolean
     */
    protected $gutenbergSupport;

    /**
     * SavePost constructor.
     *
     * @param boolean $doingAJAX
     * @param Options\SetkaPostCreatedOption $setkaPostCreatedOption
     * @param CronSingleEventInterface $setkaPostCreatedCronEvent
     * @param UseEditorPostMeta $useEditorPostMeta
     * @param PostThemePostMeta $postThemePostMeta
     * @param PostLayoutPostMeta $postLayoutPostMeta
     * @param boolean $gutenbergSupport
     */
    public function __construct(
        $doingAJAX,
        Options\SetkaPostCreatedOption $setkaPostCreatedOption,
        CronSingleEventInterface $setkaPostCreatedCronEvent,
        UseEditorPostMeta $useEditorPostMeta,
        PostThemePostMeta $postThemePostMeta,
        PostLayoutPostMeta $postLayoutPostMeta,
        $gutenbergSupport
    ) {
        $this->doingAJAX                 = $doingAJAX;
        $this->setkaPostCreatedOption    = $setkaPostCreatedOption;
        $this->setkaPostCreatedCronEvent = $setkaPostCreatedCronEvent;
        $this->useEditorPostMeta         = $useEditorPostMeta;
        $this->postThemePostMeta         = $postThemePostMeta;
        $this->postLayoutPostMeta        = $postLayoutPostMeta;
        $this->gutenbergSupport          = $gutenbergSupport;
    }

    /**
     * Save post meta. This method handles only POST requests.
     *
     * WARNING: this method don't include any checks of current_user_can()
     * or nonce validation because this already happened in edit_post()
     *
     * @see \Setka\Editor\Plugin::runAdmin()
     * @see edit_post()
     * @see wp_update_post()
     * @see wp_insert_post()
     *
     * @since 0.0.2
     *
     * @param $postId int Post ID.
     * @param $post \WP_Post WordPress Post object
     * @param $update bool Update or create new post.
     *
     * @return $this For chain calls.
     */
    public function postAction($postId, \WP_Post $post, $update)
    {
        // Nonce already validated in wp-admin/post.php

        // Stop on autosave (see heartbeat_received() in this class for autosavings)
        if (PluginConfig::isDoingAutosave()) {
            return $this;
        }

        // Prevent quick edit from clearing custom fields
        if ($this->doingAJAX) {
            return $this;
        }

        if (PluginConfig::isRestRequest() && $this->gutenbergSupport) {
            $this->saveGutenbergPost($post);
            return $this;
        }

        // Our settings presented in request?
        if (!isset($_POST[Plugin::NAME . '-settings'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return $this;
        }

        // Parse settings from JSON object
        // WordPress use addslashes() in wp_magic_quotes()
        $settings = stripcslashes($_POST[Plugin::NAME . '-settings']); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $settings = json_decode($settings, true);

        $this->proceeding($settings, $postId);

        return $this;
    }

    /**
     * @param \WP_Post $post
     *
     * @return $this
     */
    protected function saveGutenbergPost(\WP_Post $post)
    {
        $postBlocks = parse_blocks($post->post_content);

        $setkaEditorExists = false;

        foreach ($postBlocks as $block) {
            if (isset($block['blockName']) && 'setka-editor/setka-editor' === $block['blockName']) {
                $setkaEditorExists = true;
                break;
            }
        }

        $this->useEditorPostMeta->setPostId($post->ID);

        if ($setkaEditorExists) {
            $this->useEditorPostMeta->updateValue(true);
        } else {
            $this->useEditorPostMeta->delete();
        }

        return $this;
    }

    /**
     * Handles default post auto saves (triggering by WordPress Heartbeat API).
     *
     * @see wp_ajax_heartbeat()
     * @see heartbeat_autosave()
     *
     * @since 0.0.2
     *
     * @param $response array Which will be sent back to the client in browser.
     * @param $data array The data comes from JavaScript (Browser).
     *
     * @return array Just pass $response for the next filters as is.
     */
    public function heartbeatReceived($response, $data)
    {
        // Our settings presented in request?
        if (isset($data[Plugin::NAME])) {
            // Create a link to long named variable :) just to write less code below
            $settings =& $data[Plugin::NAME];

            /**
             * @see heartbeat_autosave()
             * @see wp_autosave()
             */

            if (isset($settings['postId'])) {
                $settings['postId'] = (int) $settings['postId'];

                if (isset($settings['_wpnonce'])) {
                    // Check nonce like in heartbeat_autosave()
                    if (false === wp_verify_nonce($settings['_wpnonce'], 'update-post_' . $settings['postId'])) {
                        // Just pass $response for the next filters.
                        return $response;
                    }

                    // Check current_user_can edit post
                    $post = get_post($settings['postId']);
                    if ($post instanceof \WP_Post && property_exists($post, 'ID')) {
                        if (current_user_can('edit_post', $post->ID)) {
                            $this->proceeding($settings, $settings['postId']);
                        }
                    }
                }
            }
        }
        // Just pass $response for the next filters.
        return $response;
    }

    /**
     * Simply saves the post meta. Called from heartbeat_received() or from save_post().
     *
     * We need save some extra settings from our Grid Editor (layout style, theme name,
     * the number of cols...) as post meta. Currently we save three things here:
     *
     *   1. Post created with Grid Editor or not (default WP editor).
     *   2. Post layout.
     *   3. Post theme.
     *
     * @since 0.0.2
     *
     * @param $new_settings array Post settings.
     * @param $post_id int Post id.
     *
     * @return $this For chain calls.
     */
    public function proceeding($settings, $post_id)
    {

        /**
         * Possible additional checks:
         *   1. Post Type (post, page, attachment). Currently this not validates because
         *      post may already created with editor and now this post_type disabled but
         *      old post need to be available with editor.
         *
         *   2. Current user can use grid editor. Possible issue then user can edit post
         *      but don't have editor access.
         *
         * At now use only current_user_can('edit').
         */

        if (!isset($settings['useSetkaEditor'])) {
            return $this;
        }

        if (!in_array($settings['useSetkaEditor'], array('0', '1'), true)) {
            return $this;
        }

        // Transform useSetkaEditor after which is string (was sent as POST data).
        if ('1' === $settings['useSetkaEditor']) {
            $settings['useSetkaEditor'] = true;
        } else {
            $settings['useSetkaEditor'] = false;
        }

        // Check for the first Setka Editor Post on this site
        if ($settings['useSetkaEditor'] &&
            !$this->setkaPostCreatedOption->get() &&
            !$this->setkaPostCreatedCronEvent->isScheduled()) {
            $this->setkaPostCreatedCronEvent->schedule();
            $this->setkaPostCreatedOption->updateValue(true);
        }

        try {
            // Post created with Setka Editor or not.
            $this->useEditorPostMeta
                ->setPostId($post_id)
                ->set($settings['useSetkaEditor'])
                ->flush();

            $this->postThemePostMeta->setPostId($post_id);
            // Update Post Theme name. Example: 'village-2016'.
            if (isset($settings['editorConfig']['theme'])) {
                $this->postThemePostMeta->set($settings['editorConfig']['theme']);

                if ($this->postThemePostMeta->isValid()) {
                    $this->postThemePostMeta->flush();
                }
            }

            $this->postLayoutPostMeta->setPostId($post_id);
            // Update Post Layout. Example: '6' or '12'.
            if (isset($settings['editorConfig']['layout'])) {
                $this->postLayoutPostMeta->set($settings['editorConfig']['layout']);

                if ($this->postLayoutPostMeta->isValid()) {
                    $this->postLayoutPostMeta->flush();
                }
            }
        } finally {
            $this->useEditorPostMeta
                ->setPostId(null)
                ->deleteLocal();

            $this->postThemePostMeta
                ->setPostId(null)
                ->deleteLocal();

            $this->postLayoutPostMeta
                ->setPostId(null)
                ->deleteLocal();
        }

        return $this;
    }
}
