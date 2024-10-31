<?php
namespace Setka\Editor\Service;

/**
 * Class Deactivation
 */
class Deactivation
{
    /**
     * @var string
     */
    protected $pluginFile;

    /**
     * Deactivation constructor.
     * @param string $pluginFile
     */
    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }

    /**
     * @return $this
     */
    public function run()
    {
        register_uninstall_hook(
            $this->pluginFile,
            array(Uninstall::class, 'run')
        );

        return $this;
    }
}
