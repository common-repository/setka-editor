<?php
namespace Setka\Editor\Service\Manager\Traits;

use Korobochkin\WPKit\Options\Special\BoolOption;

trait AttemptsLimitTrait
{
    /**
     * @var BoolOption
     */
    protected $attemptsLimitOption;

    /**
     * @return $this
     */
    protected function markLimitAttempts()
    {
        $this->attemptsLimitOption->updateValue(true);
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteLimitAttempts()
    {
        $this->attemptsLimitOption->delete();
        return $this;
    }

    /**
     * @return bool
     */
    protected function isAttemptsLimitExceeded()
    {
        return (bool) $this->attemptsLimitOption->get();
    }
}
