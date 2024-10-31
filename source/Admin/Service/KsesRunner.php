<?php
namespace Setka\Editor\Admin\Service;

use Korobochkin\WPKit\Runners\RunnerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KsesRunner implements RunnerInterface
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
    }

    /**
     * Setka Editor requires additional data-attributes and tags in HTML markup for posts.
     * We just add it to current WordPress list.
     *
     * @param $allowedPostTags array The list of html tags and their attributes.
     * @param $context string The name of context.
     *
     * @return array Array with required tags and attributes for Setka Editor.
     */
    public static function allowedHTML($allowedPostTags, $context)
    {
        if (!function_exists('wp_get_current_user') ||
            !function_exists('_wp_get_current_user') ||
            !function_exists('current_user_can')
        ) {
            // Plugins loads and runs in wp-includes/wp-settings.php before pluggable.php will load.
            // This check required to prevent PHP fatal errors
            // (some plugins uses functions with wp_kses_allowed_html filter too early).
            return $allowedPostTags;
        }

        /**
         * @var $kses Kses
         */
        $kses = self::getContainer()->get(Kses::class);
        return $kses->allowedHTML($allowedPostTags, $context);
    }
}
