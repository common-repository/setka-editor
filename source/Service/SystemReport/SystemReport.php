<?php
namespace Setka\Editor\Service\SystemReport;

class SystemReport
{
    /**
     * @var SectionInterface[]
     */
    private $sections;

    /**
     * @var TypeConverterInterface
     */
    private $converter;

    /**
     * @param SectionInterface[] $sections
     * @param TypeConverterInterface $converter
     */
    public function __construct(array $sections, TypeConverterInterface $converter)
    {
        $this->sections  = $sections;
        $this->converter = $converter;
    }

    /**
     * @return \Generator
     */
    public function walk(): \Generator
    {
        foreach ($this->sections as $section) {
            if ($section->isBuildable()) {
                try {
                    yield $section->getTitle() => $this->converter->convertToString($section->build());
                } catch (\Exception $exception) {
                    $section->addError($exception);
                    yield $section->getTitle() => $this->converter->convertToString($this->createArrayFromExceptions($section));
                }
            } else {
                yield $section->getTitle() => $this->converter->convertToString($this->createArrayFromExceptions($section));
            }
        }
    }

    private function createArrayFromExceptions(SectionInterface $section): array
    {
        $result = array();
        foreach ($section->getErrors() as $exception) {
            $result[] = $this->createArrayFromException($exception);
        }
        return $result;
    }

    private function createArrayFromException(\Exception $exception): array
    {
        return array(
            'classname' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
        );
    }

    public function buildFilename(): string
    {
        $url = parse_url($this->getSiteUrl()); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url

        $name = array(
            'report',
            str_replace('.', '-', $url['host']),
        );

        if (!empty($url['path']) && '/' !== $url['path']) {
            $path = str_replace('/', '-', $url['path']);

            if ($path) {
                $name[] = $path;
            }
        }

        $name[] = time();

        return implode('-', $name);
    }

    private function getSiteUrl(): string
    {
        return get_site_url();
    }
}
