<?php
namespace Setka\Editor\Service\SystemReport;

abstract class AbstractSection implements SectionInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $buildMethods = array();

    /**
     * @var \Exception[]
     */
    protected $errors = array();

    /**
     * @var boolean
     */
    protected $isChecked = false;

    abstract public function build();

    public function getTitle(): string
    {
        return $this->title;
    }

    private function checkFunctions(array $functions): void
    {
        foreach ($functions as $function) {
            if (is_array($function)) {
                $this->checkClassFunction($function);
            } else {
                $this->checkFunction($function);
            }
        }
    }

    private function checkFunction(string $function): void
    {
        if (function_exists($function)) {
            return;
        }
        $this->errors[] = new \Exception('Function "' . $function . '" disabled or not exists in current PHP interpreter.');
    }

    private function checkClassFunction(array $callback): void
    {
        if (in_array($callback[1], get_class_methods($callback[0]), true)) {
            return;
        }
        $this->errors[] = new \Exception('Function "' . implode('::', $callback) . '" not exists.');
    }

    public function isBuildable(): bool
    {
        if (!$this->isChecked) {
            $this->checkFunctions($this->buildMethods);
            $this->isChecked = true;
        }
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(\Exception $exception): void
    {
        $this->errors[] = $exception;
    }
}
