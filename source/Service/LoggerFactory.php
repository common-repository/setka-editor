<?php
namespace Setka\Editor\Service;

use Monolog\Handler\NullHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Setka\Editor\Plugin;
use Setka\Editor\Service\Manager\ManagerLoggerProcessor;

class LoggerFactory
{
    /**
     * @var string Path to plugin folder
     */
    protected $pluginDirPath;

    /**
     * @var boolean
     */
    protected $logStatus;

    /**
     * @var boolean
     */
    protected $wpDebug;

    /**
     * @var boolean
     */
    protected $wpCli;

    /**
     * @var boolean
     */
    protected $vip;

    /**
     * LoggerFactory constructor.
     *
     * @param string $pluginDirPath
     * @param bool $logStatus
     * @param bool $wpDebug
     * @param bool $vip
     * @param bool $wpCli
     */
    public function __construct($pluginDirPath, $logStatus, $wpDebug, $wpCli, $vip)
    {
        $this->pluginDirPath = trailingslashit($pluginDirPath);
        $this->logStatus     = $logStatus;
        $this->wpDebug       = $wpDebug;
        $this->wpCli         = $wpCli;
        $this->vip           = $vip;
    }

    /**
     * Creates the Logger instance.
     *
     * It also additional set StreamHandler if WP in debug mode. And NullHandler
     * for production site (to prevent log leak).
     *
     * @param string $name Name of chanel for logger.
     *
     * @throws \Exception
     *
     * @return Logger Instance for logging.
     */
    public function create($name = Plugin::_NAME_)
    {
        $logger = $this->getLoggerFromFilter($name);

        if (is_a($logger, Logger::class)) {
            return $logger;
        }

        $logger = new Logger($name);

        if (!$this->logStatus) {
            $logger->pushHandler(new NullHandler());
            return $logger;
        }

        if ($this->wpDebug && !$this->vip) {
            $handler = new RotatingFileHandler($this->createFileName($name), 7);
        } else {
            $handler = new NullHandler();
        }
        $logger->pushHandler($handler);

        if ($this->wpCli) {
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        return $logger;
    }

    /**
     * @param string $name
     * @return Logger
     * @throws \Exception
     */
    public function createForStyleManager($name)
    {
        $logger = $this->create($name);
        $logger->pushProcessor(new ManagerLoggerProcessor());
        return $logger;
    }

    /**
     * @param string $name
     * @return null|Logger
     */
    protected function getLoggerFromFilter($name)
    {
        return apply_filters('setka_editor_logger', null, $name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function createFileName($name)
    {
        return apply_filters('setka_editor_log_path', $this->pluginDirPath . 'logs/' . $name . '.log', $name);
    }
}
