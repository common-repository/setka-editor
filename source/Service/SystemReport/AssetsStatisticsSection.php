<?php
namespace Setka\Editor\Service\SystemReport;

use Setka\Editor\Admin\Service\FilesManager\AssetsStatus;

class AssetsStatisticsSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Setka Editor Assets Statistics';

    /**
     * @var AssetsStatus
     */
    private $assetsStatus;

    public function __construct(AssetsStatus $assetsStatus)
    {
        $this->assetsStatus = $assetsStatus;
    }

    public function build(): array
    {
        return $this->assetsStatus->getCountersByStatus();
    }
}
