<?php
namespace Setka\Editor\Service\SystemReport;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ParameterBagSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Symfony DI Container ParameterBag';

    /**
     * @var array
     */
    protected $buildMethods = array(array(ParameterBag::class, 'all'));

    /**
     * @var ParameterBag
     */
    private $bag;

    public function __construct(ParameterBag $bag)
    {
        $this->bag = $bag;
    }

    public function build(): array
    {
        return $this->bag->all();
    }
}
