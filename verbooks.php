<?php
/**
 * Plugin Name:     Veronalabs BookInfo Assessment
 * Plugin URI:      https://github.com/zedium/verbooks
 * Plugin Prefix:   EP
 * Description:     This is an assessment
 * Author:          Ayub Zeitunli
 * Author URI:      https://github.com/zedium
 * Text Domain:     verbooks-text-domain
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
use VerBooks\Classes\MenuPage;
use VerBooks\Services;

define('VB_TEXT_DOMAIN', 'verbooks-text-domain');
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
            $this->application->addServiceProvider(Services\MenuServiceProvider::class);
            $this->application->addServiceProvider(Services\BooksServiceProvider::class);
            $this->application->addServiceProvider(Services\DataTableServiceProvider::class);




            /**
             * Activation hooks
             */
            $this->application->onActivation(function () {
                $books = $this->application->get('Books');
                $books->createBooksTable();
            });

            /**
             * Deactivation hooks
             */
            $this->application->onDeactivation(function () {
                // Clear events, cache or something else
            });

            $this->application->boot(function (Plugin $plugin) {

                $path = plugin_basename(dirname(__FILE__)) . '/languages';

                load_plugin_textdomain(VB_TEXT_DOMAIN, false, $path);

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

$GLOBALS['verbooks'] = verbooksInit();