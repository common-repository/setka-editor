<?php
namespace Setka\Editor\Service\SystemReport;

use Korobochkin\WPKit\Plugins\PluginInterface;

class PluginSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Setka Editor Plugin';

    /**
     * @var PluginInterface
     */
    private $plugin;

    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    public function build(): array
    {
        return array(
            'name' => $this->plugin->getName(),
            'file' => $this->plugin->getFile(),
            'version' => $this->plugin->getVersion(),
        );
    }
}
