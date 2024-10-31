<?php
namespace Setka\Editor\Service\SystemReport;

class OptionsSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'WordPress wp_options values';

    /**
     * @var array
     */
    protected $buildMethods = array('wp_load_alloptions', 'maybe_unserialize');

    public function build(): array
    {
        $options = wp_load_alloptions();

        foreach ($options as $key => &$option) {
            if ('_transient' === substr($key, 0, 10)
                ||
                false !== strpos($key, 'password')) {
                unset($options[$key]);
                continue;
            }
            $option = maybe_unserialize($option);
        }

        return $options;
    }
}
