<?php
namespace Setka\Editor\Admin\Pages\AMP;

use Setka\Editor\Plugin;
use Setka\Editor\Service\Constraints\WordPressNonceConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints;

class AMPType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add('amp_css', Type\TextareaType::class, array(
                 'attr' => array(
                     'class' => 'large-text code',
                     'rows' => '25',
                     'style' => 'white-space: nowrap; overflow-x: scroll; resize: none;',
                 ),
             ));

        $builder
            ->add('amp_fonts', Type\TextareaType::class, array(
                'attr' => array(
                    'class' => 'large-text code',
                    'rows' => '5',
                    'style' => 'white-space: nowrap; overflow-x: scroll; resize: none;',
                ),
            ));

        $builder
            ->add('nonce', Type\HiddenType::class, array(
                'data' => wp_create_nonce(Plugin::NAME .'-save-settings'),
                'constraints' => array(
                    new Constraints\NotBlank(),
                    new WordPressNonceConstraint(array('name' => Plugin::NAME .'-save-settings')),
                ),
            ));

        $builder
            ->add('submit', Type\SubmitType::class, array(
                'label' => __('Save Changes', Plugin::NAME),
                'attr' => array('class' => 'button button-primary'),
            ));
    }
}
