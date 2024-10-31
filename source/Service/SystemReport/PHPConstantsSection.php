<?php
namespace Setka\Editor\Service\SystemReport;

class PHPConstantsSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'PHP Constants';

    /**
     * @var array
     */
    protected $buildMethods = array('get_defined_constants');

    public function build(): array
    {
        return get_defined_constants();
    }
}
