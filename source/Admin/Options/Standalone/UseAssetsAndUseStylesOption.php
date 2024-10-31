<?php
namespace Setka\Editor\Admin\Options\Standalone;

use Setka\Editor\Admin\Options\Files\UseLocalFilesOption;
use Setka\Editor\Admin\Options\Styles\AbstractUseStylesOption;

class UseAssetsAndUseStylesOption extends AbstractUseStylesOption
{
    /**
     * @var UseStylesOption
     */
    private $standaloneUseStylesOption;

    /**
     * @var UseLocalFilesOption
     */
    private $useLocalFilesOption;

    /**
     * AssetsAndUseStylesOption constructor.
     *
     * @param UseStylesOption $standaloneUseStylesOption
     * @param UseLocalFilesOption $useLocalFilesOption
     */
    public function __construct(
        UseStylesOption $standaloneUseStylesOption,
        UseLocalFilesOption $useLocalFilesOption
    ) {
        parent::__construct();
        $this->standaloneUseStylesOption = $standaloneUseStylesOption;
        $this->useLocalFilesOption       = $useLocalFilesOption;
    }

    /**
     * @return bool
     */
    public function get()
    {
        return $this->standaloneUseStylesOption->get() && $this->useLocalFilesOption->get();
    }
}
