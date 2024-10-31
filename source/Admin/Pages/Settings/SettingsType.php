<?php
namespace Setka\Editor\Admin\Pages\Settings;

use Setka\Editor\Admin\Options\WebhooksEndpointOption;
use Setka\Editor\Plugin;
use Setka\Editor\Service\Config\ImageSizesConfig;
use Setka\Editor\Service\Config\PluginConfig;
use Setka\Editor\Service\Constraints\WordPressNonceConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class SettingsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->buildPostTypes($builder)->buildRoles($builder);

        if (!$options['vip']) {
            $builder->add('srcset_sizes', Type\ChoiceType::class, array(
                'choices' => $this->getSrcsetSizesChoices($options['srcset_sizes']),
                'choice_label' => 'getId',
                'choice_value' => 'getId',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => 'buildAttributes',
            ));
        }

        $builder->add('endpoint', Type\ChoiceType::class, array(
            'label' => __('Webhooks Endpoint', Plugin::NAME),
            'choices' => array(
                'wp-admin/admin-post.php (default)' => WebhooksEndpointOption::TYPE_POST,
                'wp-admin/admin-ajax.php' => WebhooksEndpointOption::TYPE_AJAX,
            ),
            'multiple' => false,
            'expanded' => false,
        ));

        $stylesModes = array(
            /**
             * Sync this array with same value in SettingsPage::lateConstruct
             */
            Settings::STYLES_MODE_STANDALONE => array(
                __('Default. Only load styles needed for a specific Setka Editor post.', Plugin::NAME),
                __('All Setka Editor styles will be loaded on the page as separate files. This provides a balance between user experience and speed.', Plugin::NAME),
            ),
            Settings::STYLES_MODE_STANDALONE_CRITICAL => array(
                __('Inline critical post styles into a page and load the rest of the styles asynchronously.', Plugin::NAME),
                __('This will reduce the delay to display post content, but might result in post content redraws after the full page load. Read more about <a href="https://editor-help.setka.io/hc/en-us/articles/360053978293" target="_blank">critical styles and how they affect performance</a>.', Plugin::NAME),
            ),
            Settings::STYLES_MODE_COMBINED => array(
                __('Legacy. Load a combined CSS file with all your Setka Editor styles.', Plugin::NAME),
                __('This will increase the assets\' size and might affect load speeds. Use only if you plan on displaying Setka Editor posts in different styles (configured on <a href="https://editor.setka.io" target="_blank">editor.setka.io</a>) on a single page.', Plugin::NAME),
            ),
        );
        $builder->add('styles_mode', Type\ChoiceType::class, array(
            'label' => __('Styles', Plugin::NAME),
            'choices' => array(
                $stylesModes[Settings::STYLES_MODE_STANDALONE][0] => Settings::STYLES_MODE_STANDALONE,
                $stylesModes[Settings::STYLES_MODE_STANDALONE_CRITICAL][0] => Settings::STYLES_MODE_STANDALONE_CRITICAL,
                $stylesModes[Settings::STYLES_MODE_COMBINED][0] => Settings::STYLES_MODE_COMBINED,
            ),
            'multiple' => false,
            'expanded' => true,
            'required' => true,
        ));

        $builder->add('force_use_setka_cdn', Type\CheckboxType::class, array(
            'label' => __('Enable Setka CDN files', Plugin::NAME),
            'required' => false,
        ));

        $builder->add('white_label', Type\CheckboxType::class, array(
            'label' => __('Credits', Plugin::NAME),
            'required' => false,
        ));

        $builder->add('nonce', Type\HiddenType::class, array(
            'data' => wp_create_nonce(Plugin::NAME . '-save-settings'),
            'constraints' => array(
                new Constraints\NotBlank(),
                new WordPressNonceConstraint(array('name' => Plugin::NAME . '-save-settings')),
            ),
        ));

        $builder->add('submit', Type\SubmitType::class, array(
            'label' => __('Save Changes', Plugin::NAME),
            'attr' => array('class' => 'button button-primary'),
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('srcset_sizes');
        $resolver->setAllowedTypes('srcset_sizes', 'array');

        $resolver->setDefault('vip', false);
        $resolver->setAllowedTypes('vip', 'bool');
    }

    /**
     * @param FormBuilderInterface $builder
     * @return $this
     */
    private function buildPostTypes(FormBuilderInterface $builder)
    {
        $builder->add(
            'post_types',
            Type\ChoiceType::class,
            array(
                'choices' => $this->getPostTypeChoices(),
                'choice_label' => 'getName',
                'choice_value' => 'getId',
                'multiple' => true,
                'expanded' => true,
            )
        );

        return $this;
    }

    private function getPostTypeChoices()
    {
        $postTypes = $this->getPostTypes();
        $choices   = array();


        foreach ($postTypes as $key => $value) {
            $postTypeObject = $this->getPostTypeObject($value);
            $choices[]      = new PostType($postTypeObject->name, $postTypeObject->labels->name);
        }

        return $choices;
    }

    /**
     * @return array
     */
    private function getPostTypes()
    {
        return PluginConfig::getAvailablePostTypes();
    }

    /**
     * @param $postType string
     * @return \WP_Post_Type
     */
    private function getPostTypeObject($postType)
    {
        return get_post_type_object($postType);
    }

    /**
     * @param FormBuilderInterface $builder
     * @return $this
     */
    private function buildRoles(FormBuilderInterface $builder)
    {
        $roles = $this->getRoles();

        if (empty($roles)) {
            return $this;
        }

        $choices = array();

        foreach ($roles as $key => $value) {
            $choices[$value['name']] = $key;
        }

        $builder->add(
            'roles',
            Type\ChoiceType::class,
            array(
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
            )
        );

        return $this;
    }

    /**
     * @return array[]
     */
    private function getRoles()
    {
        return get_editable_roles();
    }

    /**
     * @param $sizes array
     * @return array
     */
    private function getSrcsetSizesChoices(array $sizes)
    {
        $values = array();

        foreach ($sizes as $name => $size) {
            $values[] = new ImageSize(
                $name,
                $size[ImageSizesConfig::SETTING_WIDTH],
                $size[ImageSizesConfig::SETTING_HEIGHT],
                $size[ImageSizesConfig::SETTING_CROP]
            );
        }

        return $values;
    }
}
