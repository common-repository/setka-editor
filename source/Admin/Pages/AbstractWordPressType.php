<?php
namespace Setka\Editor\Admin\Pages;

use Setka\Editor\Plugin;
use Setka\Editor\Service\Constraints\WordPressNonceConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

abstract class AbstractWordPressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     */
    protected function addNonceType(FormBuilderInterface $builder)
    {
        $builder->add('nonce', Type\HiddenType::class, array(
            'data' => wp_create_nonce(Plugin::NAME . '-save-settings'),
            'constraints' => array(
                new Constraints\NotBlank(),
                new WordPressNonceConstraint(array('name' => Plugin::NAME . '-save-settings')),
            ),
        ));
    }
}
