<?php
namespace Setka\Editor\Service\SystemReport;

use Setka\Editor\Service\Manager\FilesManager\Status;

abstract class AbstractStylesStatisticsSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var Status
     */
    private $status;

    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    public function build(): array
    {
        return array(
            $this->status->getCountersByType(),
            $this->status->getPostList(),
            $this->status->getCurrentSettings(),
        );
    }
}
