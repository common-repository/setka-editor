<?php
use Setka\Editor\Plugin;
use Setka\Editor\Compatibility\Compatibility;
use Setka\Editor\Compatibility\PHPVersionNotice;
use Setka\Editor\Compatibility\WPVersionNotice;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/*
Plugin Name: Setka Editor
Plugin URI: https://editor.setka.io/
Description: A WordPress plugin for beautiful content. The editor you've been waiting for to design your posts.
Author: Native Grid LLC
Author URI: https://editor.setka.io/
Version: 2.1.20
Text Domain: setka-editor
Domain Path: /languages/
License: GPLv2 or later
*/

function setkaEditorRunner()
{
    $compatibility   = true;
    $pluginVersion   = '2.1.20';
    $phpVersionMin   = '7.1.3';
    $phpVersionIDMin = 70130;
    $wpVersionMin    = '4.1';

    // Check for minimum PHP version
    require_once __DIR__ . '/source/Compatibility/Compatibility.php';
    if (!Compatibility::checkPHP($phpVersionIDMin)) {
        require_once __DIR__ . '/source/Compatibility/PHPVersionNotice.php';
        $PHPVersionNotice = new PHPVersionNotice();
        $PHPVersionNotice
            ->setBaseUrl(plugin_dir_url(__FILE__))
            ->setPluginVersion($pluginVersion)
            ->setPhpVersionMin($phpVersionMin)
            ->run();
        $compatibility = false;
    }

    // Check for minimum WordPress version
    if (!Compatibility::checkWordPress($wpVersionMin)) {
        require_once __DIR__ . '/source/Compatibility/WPVersionNotice.php';
        $WPVersionNotice = new WPVersionNotice();
        $WPVersionNotice
            ->setBaseUrl(plugin_dir_url(__FILE__))
            ->setPluginVersion($pluginVersion)
            ->setWpVersionMin($wpVersionMin)
            ->run();
        $compatibility = false;
    }

    if ($compatibility) {
        global $container;

        if (!class_exists('Setka\Editor\Plugin')) {
            // If class not exists this means what a wordpress.org version running
            // and we need require our own autoloader.
            // If you using WordPress installation with composer just require
            // your own autoload.php as usual. In this case plugin don't require any
            // additional autoloaders.
            require_once __DIR__ . '/vendor/autoload.php';
        }

        $plugin = $GLOBALS['WPSetkaEditorPlugin'] = new Plugin(__FILE__);

        if (isset($container) && is_a($container, ContainerBuilder::class)) {
            $plugin->setContainer($container);
        } else {
            $plugin->setContainer(new ContainerBuilder());
        }

        $plugin->configureDependencies()->runOnLoad();

        add_action('init', array($plugin, 'run'));

        if (is_admin()) {
            $plugin->runOnLoadAdmin();
            add_action('init', array($plugin, 'runAdmin'), 20);
        } else {
            add_action('init', array($plugin, 'runNonAdmin'), 20);
        }
    }
}
setkaEditorRunner();
