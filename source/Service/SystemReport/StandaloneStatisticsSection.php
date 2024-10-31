<?php
namespace Setka\Editor\Service\SystemReport;

use Setka\Editor\Service\Standalone\StandaloneStatus;

class StandaloneStatisticsSection extends AbstractStylesStatisticsSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Setka Editor Standalone Styles Statistics';

    public function __construct(StandaloneStatus $standaloneStatus)
    {
        parent::__construct($standaloneStatus);
    }
}
