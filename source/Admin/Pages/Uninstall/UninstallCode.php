<?php
namespace Setka\Editor\Admin\Pages\Uninstall;

use Setka\Editor\Service\SetkaAccount\SetkaEditorAccount;

class UninstallCode
{
    /**
     * @var array
     */
    private $code = array('<?php', PHP_EOL);

    /**
     * @var SetkaEditorAccount
     */
    private $setkaEditorAccount;

    /**
     * @var array
     * @see SetkaEditorAssets
     * @see SetkaEditorTheContent
     * @see SetkaEditorUtils
     */
    private $classes;

    /**
     * UninstallCode constructor.
     */
    public function __construct(SetkaEditorAccount $setkaEditorAccount)
    {
        $this->setkaEditorAccount = $setkaEditorAccount;

        $this->classes = array(
            SetkaEditorAssets::class => array('setup', $this->buildSetkaEditorAssetsArgs()),
            SetkaEditorTheContent::class => array('setup'),
            SetkaEditorUtils::class => null,
        );
    }

    /**
     * @return $this
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function build()
    {
        $runnersDefinitions = array();

        foreach ($this->classes as $class => $details) {
            $reflection = new \ReflectionClass($class);

            $codeFromFile = $this->getCodeFromFile($this->getClassFileName($reflection));

            $this->code[] = $this->createClassDefinitionRecord($codeFromFile);

            if ($details) {
                $runnersDefinitions[] = $this->createRunnerCallRecord($reflection, $details);
            }
        }

        foreach ($runnersDefinitions as $details) {
            $this->code[] = PHP_EOL . $details;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return implode('', $this->code);
    }

    /**
     * @param string $file
     *
     * @return array
     * @throws \Exception
     */
    private function getCodeFromFile($file)
    {
        $strings = file($file);

        if ($strings) {
            return $strings;
        }
        throw new \Exception('Error while reading file ' . $file);
    }

    /**
     * @param \ReflectionClass $reflection
     *
     * @return string
     * @throws \Exception
     */
    private function getClassFileName(\ReflectionClass $reflection)
    {
        $fileName = $reflection->getFileName();

        if ($fileName) {
            return $fileName;
        }
        throw new \Exception('Class does not exists: ' . $reflection->getName());
    }

    /**
     * @param array $lines
     *
     * @return string
     */
    private function createClassDefinitionRecord(array $lines)
    {
        for ($i = 0; $i < 3; $i++) {
            unset($lines[$i]);
        }

        return implode('', $lines);
    }

    /**
     * @param \ReflectionClass $reflection
     * @param array $details
     *
     * @return string
     * @throws \Exception
     */
    private function createRunnerCallRecord(\ReflectionClass $reflection, array $details)
    {
        return implode('', array(
            $reflection->getShortName(),
            '::',
            $details[0],
            '(',
            isset($details[1]) ? $this->createMethodArgumentsRecord($details[1]) : '',
            ');',
        ));
    }

    /**
     * @param array $arguments
     *
     * @return string
     * @throws \Exception
     */
    private function createMethodArgumentsRecord(array $arguments)
    {
        $value = array();

        foreach ($arguments as $argument) {
            switch (gettype($argument)) {
                case 'string':
                    $value[] = '\'' . $argument . '\'';
                    break;

                case 'integer':
                case 'float':
                    $value[] = $argument;
                    break;

                case 'boolean':
                    $value[] = ($argument) ? 'true' : 'false';
                    break;

                default:
                    throw new \Exception('Not scalar argument');
            }
        }

        return implode(', ', $value);
    }

    /**
     * @return array
     */
    private function buildSetkaEditorAssetsArgs()
    {
        return array(
            $this->setkaEditorAccount->getThemeResourceCSSOption()->get(),
            $this->setkaEditorAccount->getThemePluginsJSOption()->get(),
        );
    }
}
