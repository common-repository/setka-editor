<?php
namespace Setka\Editor\Admin\Service\FilesManager;

use Korobochkin\WPKit\Options\OptionInterface;
use Setka\Editor\Admin\Options\Files\FilesOption;
use Setka\Editor\Admin\Service\SetkaEditorAPI\Actions\GetFilesAction;
use Setka\Editor\Admin\Service\SetkaEditorAPI\SetkaEditorAPI;
use Setka\Editor\Admin\Service\SetkaEditorAPI\AuthCredits;

/**
 * Class DownloadListOfFiles
 */
class DownloadListOfFiles
{
    /**
     * @var SetkaEditorAPI
     */
    protected $api;

    /**
     * @var OptionInterface
     */
    protected $tokenOption;

    /**
     * @var GetFilesAction
     */
    protected $action;

    /**
     * DownloadListOfFiles constructor.
     *
     * @param $api SetkaEditorAPI
     * @param $tokenOption OptionInterface
     */
    public function __construct(SetkaEditorAPI $api, OptionInterface $tokenOption)
    {
        $this->api         = $api;
        $this->tokenOption = $tokenOption;
    }

    /**
     * @throws \Exception
     * @return $this
     */
    public function execute()
    {
        $this->api->setAuthCredits(new AuthCredits($this->tokenOption->get()));

        $action = $this->action = new GetFilesAction();
        $this->api->request($action);

        if (count($action->getErrors()) !== 0) {
            throw new \Exception();
        } else {
            $filesOption = new FilesOption();
            $filesOption->updateValue($action->getResponse()->getContent()->all());
        }

        return $this;
    }
}
