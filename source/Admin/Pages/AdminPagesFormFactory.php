<?php
namespace Setka\Editor\Admin\Pages;

use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AdminPagesFormFactory
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * FormFactory constructor.
     *
     * @param $validator ValidatorInterface
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function create()
    {
        $formFactoryBuilder = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension($this->validator))
            ->addExtension(new HttpFoundationExtension())
            ->getFormFactory();

        return $formFactoryBuilder;
    }
}
