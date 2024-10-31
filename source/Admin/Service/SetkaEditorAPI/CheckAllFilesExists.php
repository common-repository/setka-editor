<?php
namespace Setka\Editor\Admin\Service\SetkaEditorAPI;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CheckAllFilesExists
{
    /**
     * @var array Required files extensions (file types).
     */
    protected $requiredFiles = array();

    /**
     * CheckAllFilesExists constructor.
     *
     * @param $requiredFiles array List of required file names (extensions).
     */
    public function __construct(array $requiredFiles)
    {
        $a = array();

        foreach ($requiredFiles as $file) {
            $a[$file] = false;
        }

        $this->requiredFiles = $a;
    }

    /**
     * Validates that all required files exists.
     *
     * @param mixed $data Data to validate.
     * @param ExecutionContextInterface $context Validation context.
     */
    public function validate($data, ExecutionContextInterface $context): void
    {
        if (count($context->getViolations()) !== 0) {
            return;
        }

        foreach ($data as $file) {
            if (isset($this->requiredFiles[$file['filetype']])) {
                $this->requiredFiles[$file['filetype']] = true;
            }
        }

        foreach ($this->requiredFiles as $fileName => $fileExists) {
            if (false === $fileExists) {
                $context
                    ->buildViolation('File with required filename %fileName% not found.')
                    ->setParameter('%fileName%', $fileName)
                    ->addViolation();
            }
        }
    }
}
