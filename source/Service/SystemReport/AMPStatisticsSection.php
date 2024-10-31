<?php
namespace Setka\Editor\Service\SystemReport;

use Setka\Editor\Service\AMP\AMPStatus;

class AMPStatisticsSection extends AbstractStylesStatisticsSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'Setka Editor AMP Styles Statistics';

    public function __construct(AMPStatus $standaloneStatus)
    {
        parent::__construct($standaloneStatus);
    }
}
