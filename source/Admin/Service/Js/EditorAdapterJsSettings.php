<?php
namespace Setka\Editor\Admin\Service\Js;

use Setka\Editor\Admin\Options\SrcsetSizesOption;
use Setka\Editor\Entities\PostInterface;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class EditorAdapterJsSettings
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var SrcsetSizesOption
     */
    private $srcsetSizesOption;

    /**
     * @var PostInterface
     */
    private $post;

    /**
     * EditorAdapterJsSettings constructor.
     * @param SetkaEditorAccount $setkaEditorAccount
     * @param SrcsetSizesOption $srcsetSizesOption
     */
    public function __construct(SetkaEditorAccount $setkaEditorAccount, SrcsetSizesOption $srcsetSizesOption)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        $this->srcsetSizesOption  = $srcsetSizesOption;
    }

    /**
     * Returns settings editor-adapter translations.settings.
     *
     * @since 0.0.2
     *
     * @return array Settings for editor-adapter translations.settings array field (cell).
     */
    public function getSettings()
    {
        return array(
            'useSetkaEditor' => $this->post->isAutoInit(),
            'editorConfig' => array(
                'layout' => $this->post->getLayout(),
                'theme' => $this->post->getTheme(),
                'medialib_image_alt_attr' => true,
                'user' => array(
                    'capabilities' => $this->getCapabilities(),
                ),
                'public_token' => $this->setkaEditorAccount->getPublicTokenOption()->get(),
                'wpSrcsetSizes' => $this->srcsetSizesOption->get(),
            ),
            'themeData' => $this->setkaEditorAccount->isLocalFilesUsage() ? $this->setkaEditorAccount->getThemeResourceJSLocalOption()->get() : $this->setkaEditorAccount->getThemeResourceJSOption()->get(),
        );
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        $user = get_userdata(get_current_user_id());
        if (is_a($user, \WP_User::class)) {
            $caps = $user->get_role_caps();
        } else {
            $caps = array();
        }
        return $caps;
    }

    /**
     * @param PostInterface $post
     * @return $this
     */
    public function setPost(PostInterface $post)
    {
        $this->post = $post;
        return $this;
    }
}
