<?php
namespace Setka\Editor\Service\SystemReport;

class InstalledPluginsSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Installed Plugins';

    /**
     * @var string[]
     */
    protected $buildMethods = array('get_plugins');

    public function build(): array
    {
        return get_plugins();
    }
}
