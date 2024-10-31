<?php
namespace Setka\Editor\CLI\Commands;

use Setka\Editor\Service\Manager\FilesManager\StatusInterface;
use Setka\Editor\Service\Styles\AbstractServiceManager;
use WP_CLI as Console;

abstract class AbstractStylesCommand extends \WP_CLI_Command // phpcs:ignore WordPressVIPMinimum.Classes.RestrictedExtendClasses.wp_cli
{
    /**
     * @var AbstractServiceManager
     */
    private $serviceManager;

    /**
     * @var StatusInterface
     */
    private $status;

    /**
     * @param AbstractServiceManager $serviceManager
     * @param StatusInterface $status
     */
    public function __construct(AbstractServiceManager $serviceManager, StatusInterface $status)
    {
        $this->serviceManager = $serviceManager;
        $this->status         = $status;
    }

    /**
     * Show styles sync status.
     *
     * @alias st
     *
     * @when after_wp_load
     */
    public function status()
    {
        \WP_CLI\Utils\format_items('yaml', $this->getCurrentSettings(), array('Name', 'Value'));
    }

    /**
     * @return array
     */
    private function getCurrentSettings()
    {
        $options = $this->status->getCurrentSettings();
        $data    = array();
        foreach ($options as $name => &$value) {
            $data[] = array(
                'Name' => $name,
                'Value' => $value,
            );
        }
        return $data;
    }

    /**
     * Run styles sync.
     *
     * @when after_wp_load
     */
    public function sync()
    {
        try {
            $this->serviceManager->sync();
        } catch (\Exception $exception) {
        }
    }

    /**
     * Check pending files (revert failed files back to queue).
     *
     * @when after_wp_load
     */
    public function pending()
    {
        try {
            $this->serviceManager->pending();
        } catch (\Exception $exception) {
        }
    }

    /**
     * Restart styles sync
     *
     * @alias res
     *
     * @when after_wp_load
     */
    public function restart()
    {
        $this->serviceManager->restart();
        Console::success('Restarted.');
    }

    /**
     * Disable styles sync
     *
     * @alias dis
     *
     * @when after_wp_load
     */
    public function disable()
    {
        $this->serviceManager->disable();
        Console::success('Disabled.');
    }

    /**
     * Enable styles sync
     *
     * @alias en
     *
     * @when after_wp_load
     */
    public function enable()
    {
        $this->serviceManager->enable();
        Console::success('Enabled.');
    }

    /**
     * Delete all files and options.
     *
     * @when after_wp_load
     */
    public function delete()
    {
        try {
            $this->serviceManager->discardCurrentState();
            $this->serviceManager->deleteAllFiles();
        } catch (\Exception $exception) {
            $message = 'Error while deleting posts:' . PHP_EOL .
                       'Exception name: ' . get_class($exception) . PHP_EOL .
                       'Exception code: ' . $exception->getCode() . PHP_EOL .
                       'Exception message: ' . $exception->getMessage();
            Console::error($message);
        }

        Console::success('All files were removed.');
    }

    /**
     * Show all files.
     *
     * @when after_wp_load
     */
    public function show()
    {
        $items = $this->status->getPostList();

        if (empty($items)) {
            Console::log('Files not found.');
            return;
        }

        \WP_CLI\Utils\format_items('table', $items, array('ID', 'post_name', 'post_status', 'post_type', 'post_date_gmt', 'setka_file_type'));
    }

    /**
     * Count different files.
     *
     * @when after_wp_load
     */
    public function count()
    {
        $counters = $this->status->getCountersByType();
        foreach ($counters as $counterName => $counter) {
            if (is_a($counter, \Exception::class)) {
                $counter = $counter->getMessage();
            }
            Console::log($counterName . ': ' . $counter);
        }
    }

    /**
     * Count files by post_status.
     *
     * @when after_wp_load
     */
    public function countStatus()
    {
        $counters = $this->status->getCountersByStatus();
        foreach ($counters as $counterName => $counter) {
            if (is_a($counter, \Exception::class)) {
                $counter = $counter->getMessage();
            }
            Console::log($counterName . ': ' . $counter);
        }
    }
}
