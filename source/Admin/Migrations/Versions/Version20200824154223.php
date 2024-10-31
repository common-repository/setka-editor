<?php
namespace Setka\Editor\Admin\Migrations\Versions;

use Setka\Editor\Admin\Migrations\MigrationInterface;
use Setka\Editor\Service\Standalone\StandaloneServiceManager;

class Version20200824154223 implements MigrationInterface
{
    /**
     * @var StandaloneServiceManager
     */
    private $standaloneServiceManager;

    /**
     * Version20200824154223 constructor.
     *
     * @param StandaloneServiceManager $standaloneServiceManager
     */
    public function __construct(StandaloneServiceManager $standaloneServiceManager)
    {
        $this->standaloneServiceManager = $standaloneServiceManager;
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        if ($this->standaloneServiceManager->isOn()) {
            $this->standaloneServiceManager->restart();
        }
    }
}
