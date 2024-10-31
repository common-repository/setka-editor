<?php
namespace Setka\Editor\Service\SystemReport;

class HooksAndFiltersSection extends AbstractSection implements ArraySectionInterface
{
    /**
     * @var string
     */
    protected $title = 'WordPress Hooks & Filters';

    public function build(): array
    {
        global $wp_filter;

        $value = array();

        foreach ($wp_filter as $name => $hook) {
            /**
             * @var $name string
             * @var $hook \WP_Hook
             */
            $value[$name] = $this->buildHook($hook);
        }

        return $value;
    }

    /**
     * @param $hook \WP_Hook|array
     */
    private function buildHook($hook): array
    {
        $value = array();
        foreach ($hook as $priority => $callbacksList) {
            $value[$priority] = $this->buildPriorityLevel($callbacksList);
        }
        return $value;
    }

    /**
     * @param $callbacksList array|\WP_Hook
     *
     * @return array
     */
    private function buildPriorityLevel(&$callbacksList): array
    {
        $value = array();
        foreach ($callbacksList as $name => &$item) {
            $value[$name] = $this->buildHookListener($item);
        }
        return $value;
    }

    /**
     * @param $callback array
     *
     * @return array
     */
    private function buildHookListener(array &$callback): array
    {
        if (is_array($callback['function'])) {
            if (is_object($callback['function'][0])) {
                $class = get_class($callback['function'][0]);
            } else {
                $class =& $callback['function'][0];
            }

            $value = array(
                'function' => array($class, $callback['function'][1]),
            );
        } else {
            $value = array('function' => $callback['function']);
        }

        $value['accepted_args'] =& $callback['accepted_args'];

        return $value;
    }
}
