<?php
namespace Setka\Editor\Admin\Pages\SetkaEditor\SignUp;

use Setka\Editor\Plugin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints;

class SignUpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('token', Type\TextType::class, array(
            'label' => __('License key', Plugin::NAME),
            'required' => false,
            'constraints' => array(
                new Constraints\NotBlank(array(
                    'groups' => array('sign-in'),
                )),
            ),
            'validation_groups' => array('sign-in'),
            'attr' => array(
                'class' => 'regular-text',
            ),
        ));

        $builder->add('submitToken', Type\SubmitType::class, array(
            'label' => __('Start working with Setka Editor', Plugin::NAME),
            'attr' => array(
                'class' => 'button button-primary',
            ),
            'validation_groups' => 'sign-in',
        ));
    }
}
