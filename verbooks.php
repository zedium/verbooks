<?php
/**
 * Plugin Name:     Veronalabs BookInfo Assessment
 * Plugin URI:      https://github.com/zedium/verbooks
 * Plugin Prefix:   EP
 * Description:     This is an assessment
 * Author:          Ayub Zeitunli
 * Author URI:      https://github.com/zedium
 * Text Domain:     zedium-bookinfo
 * Domain Path:     /languages
 * Version:         0.1.0
 */

use Rabbit\Application;
use Rabbit\Redirects\RedirectServiceProvider;
use Rabbit\Database\DatabaseServiceProvider;
use Rabbit\Logger\LoggerServiceProvider;
use Rabbit\Plugin;
use Rabbit\Redirects\AdminNotice;
use Rabbit\Templates\TemplatesServiceProvider;
use Rabbit\Utils\Singleton;
use League\Container\Container;

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require dirname(__FILE__) . '/vendor/autoload.php';
}

/**
 * Class VerBooksInit
 * @package VerBooksInit
 */
class VerBooksInit extends Singleton
{
    /**
     * @var Container
     */
    private $application;

    /**
     * VerBooksInit constructor.
     */
    public function __construct()
    {
        $this->application = Application::get()->loadPlugin(__DIR__, __FILE__, 'config');
        $this->init();
    }

    public function init()
    {
        try {

            /**
             * Load service providers
             */
            $this->application->addServiceProvider(RedirectServiceProvider::class);
            $this->application->addServiceProvider(DatabaseServiceProvider::class);
            $this->application->addServiceProvider(TemplatesServiceProvider::class);
            $this->application->addServiceProvider(LoggerServiceProvider::class);
            // Load your own service providers here...
            $this->application->addServiceProvider(VerBooks\Pages\PagesManagerServiceProvider::class);

            /**
             * Activation hooks
             */
            $this->application->onActivation(function () {
                // Create tables or something else
            });

            /**
             * Deactivation hooks
             */
            $this->application->onDeactivation(function () {
                // Clear events, cache or something else
            });

            $this->application->boot(function (Plugin $plugin) {
                $plugin->loadPluginTextDomain();

                // load template
                $this->application->template('plugin-template.php', ['foo' => 'bar']);

                ///...

            });

        } catch (Exception $e) {
            /**
             * Print the exception message to admin notice area
             */
            add_action('admin_notices', function () use ($e) {
                AdminNotice::permanent(['type' => 'error', 'message' => $e->getMessage()]);
            });

            /**
             * Log the exception to file
             */
            add_action('init', function () use ($e) {
                if ($this->application->has('logger')) {
                    $this->application->get('logger')->warning($e->getMessage());
                }
            });
        }
    }

    /**
     * @return Container
     */
    public function getApplication()
    {
        return $this->application;
    }
}

/**
 * Returns the main instance of VerBooksInit.
 *
 * @return Singleton
 */
function verbooksInit()
{
    return VerBooksInit::get();
}

verbooksInit();