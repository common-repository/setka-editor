<?php
namespace Setka\Editor\Service\Manager;

use Monolog\Processor\ProcessorInterface;
use Setka\Editor\Exceptions\Exception;
use Setka\Editor\Service\Manager\Exceptions\PostMetaException;

class ManagerLoggerProcessor implements ProcessorInterface
{
    const CONTEXT = 'context';

    /**
     * @var array
     */
    private $record;

    /**
     * @var Exception
     */
    private $exception;

    /**
     * @param array $records
     * @return array
     */
    public function __invoke(array $records)
    {
        if (isset($records[self::CONTEXT][0]) && is_a($records[self::CONTEXT][0], Exception::class)) {
            $this->record    = $records;
            $this->exception = $records[self::CONTEXT][0];
            $this->process();
            return $this->record;
        }
        return $records;
    }

    private function process()
    {
        switch (get_class($this->exception)) {
            case PostMetaException::class:
                $this->setContext($this->exception->getData());
                break;

            default:
                break;
        }
    }

    /**
     * @param array $context
     */
    private function setContext(array $context)
    {
        $this->record[self::CONTEXT] = $context;
    }
}
