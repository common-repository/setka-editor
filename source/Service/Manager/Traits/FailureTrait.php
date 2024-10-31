<?php
namespace Setka\Editor\Service\Manager\Traits;

use Korobochkin\WPKit\Options\OptionInterface;
use Korobochkin\WPKit\Options\Special\BoolOption;

trait FailureTrait
{
    /**
     * @var BoolOption
     */
    private $failureOption;

    /**
     * @var OptionInterface
     */
    private $failureNameOption;

    /**
     * @var BoolOption
     */
    private $failureNoticeOption;

    /**
     * Saves passed exception into site options.
     *
     * @param \Exception $exception
     *
     * @return $this For chain calls.
     */
    public function saveFailure(\Exception $exception)
    {
        $this->failureOption->updateValue(true);
        $this->failureNameOption->updateValue(get_class($exception));
        $this->failureNoticeOption->delete();
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteFailure()
    {
        $this->failureOption->delete();
        $this->failureNameOption->delete();
        $this->failureNoticeOption->delete();
        return $this;
    }
}
