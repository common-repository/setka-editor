<?php
namespace Setka\Editor\Admin\Pages\Support;

use Setka\Editor\Admin\Pages\AbstractWordPressType;
use Setka\Editor\Plugin;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Constraints;

class SupportType extends AbstractWordPressType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addNonceType($builder);

        $builder->add('format', Type\HiddenType::class, array(
            'constraints' => array(
                new Constraints\NotBlank(),
                new Constraints\Choice(array(Support::FORMAT_JSON, Support::FORMAT_TEXT)),
            ),
        ));

        $builder->add('generate', Type\SubmitType::class, array(
            'label' => __('Generate diagnostic report', Plugin::NAME),
            'attr' => array('class' => 'button button-primary'),
        ));
    }
}
