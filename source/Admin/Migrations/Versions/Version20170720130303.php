<?php
namespace Setka\Editor\Admin\Migrations\Versions;

use Setka\Editor\Admin\Migrations\MigrationInterface;
use Setka\Editor\Admin\Service\FilesManager\FilesServiceManager;
use Setka\Editor\Service\Config\PluginConfig;
use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class Version20170720130303 implements MigrationInterface
{
    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var FilesServiceManager
     */
    private $serviceManager;

    /**
     * @param SetkaEditorAccount $setkaEditorAccount
     * @param FilesServiceManager $serviceManager
     */
    public function __construct(SetkaEditorAccount $setkaEditorAccount, FilesServiceManager $serviceManager)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;
        $this->serviceManager     = $serviceManager;
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        if (!$this->setkaEditorAccount->isLoggedIn()) {
            return $this;
        }

        if (PluginConfig::isVIP()) {
            return $this;
        }

        $this->serviceManager->restart();

        return $this;
    }
}
