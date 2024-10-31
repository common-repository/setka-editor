<?php
namespace Setka\Editor\Service\Manager\FilesManager;

use Setka\Editor\Service\Manager\Exceptions\PendingFilesException;
use Setka\Editor\Service\Manager\Stacks\PendingFactoryInterface;
use Setka\Editor\Service\Manager\StageInterface;

class IsPendingExistsStage implements StageInterface
{
    /**
     * @var PendingFactoryInterface
     */
    private $pendingFactory;

    /**
     * @param PendingFactoryInterface $pendingFactory
     */
    public function __construct(PendingFactoryInterface $pendingFactory)
    {
        $this->pendingFactory = $pendingFactory;
    }

    /**
     * @throws PendingFilesException
     */
    public function run(): void
    {
        if ($this->isPendingFilesExists()) {
            throw new PendingFilesException();
        }
    }

    /**
     * Check if pending files exists.
     *
     * @return bool True if one or more pending files exists.
     */
    private function isPendingFilesExists(): bool
    {
        return $this->pendingFactory->createQuery()->have_posts();
    }
}
