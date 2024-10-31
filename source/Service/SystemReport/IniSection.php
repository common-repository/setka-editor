<?php
namespace Setka\Editor\Service\SystemReport;

class IniSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'PHP ini variables';

    /**
     * @var array
     */
    protected $buildMethods = array('ini_get_all');

    public function build(): array
    {
        return ini_get_all();
    }
}
