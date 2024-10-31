<?php
namespace Setka\Editor\CLI;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Setka\Editor\CLI\Commands\AccountCommand;
use Setka\Editor\CLI\Commands\AMPCommand;
use Setka\Editor\CLI\Commands\FilesCommand;
use Setka\Editor\CLI\Commands\StandaloneCommand;
use Setka\Editor\Plugin;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CliCommandsRunner implements RunnerInterface
{
    /**
     * @var ContainerInterface Container with services.
     */
    protected static $container;

    /**
     * Returns the ContainerBuilder with services.
     *
     * @return ContainerInterface Container with services.
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * Sets the ContainerBuilder with services.
     *
     * @param ContainerInterface $container Container with services.
     */
    public static function setContainer(ContainerInterface $container = null)
    {
        self::$container = $container;
    }

    /**
     * @inheritdoc
     */
    public static function run()
    {
        \WP_CLI::add_command(Plugin::NAME, self::getContainer()->get(AccountCommand::class));

        // Please keep this order of registering commands
        // Because WP CLI discard commands. For example, if you register
        // 1. "files create"
        // 2. "files"
        // Then "files create" will not registered.

        \WP_CLI::add_command(Plugin::NAME . ' amp', self::getContainer()->get(AMPCommand::class));
        \WP_CLI::add_command(Plugin::NAME . ' standalone', self::getContainer()->get(StandaloneCommand::class));
        \WP_CLI::add_command(Plugin::NAME . ' files', self::getContainer()->get(FilesCommand::class));
    }
}
