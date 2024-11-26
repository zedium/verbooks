<?php

namespace VerBooks\Services;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rabbit\Contracts\BootablePluginProviderInterface;
use VerBooks\Classes\Books;
use VerBooks\Classes\MenuPage;

class BooksServiceProvider extends AbstractServiceProvider implements BootablePluginProviderInterface,BootableServiceProviderInterface
{

    protected $provides = [
        'Books',
    ];

    public function register()
    {

        global $wpdb;



        $this->getContainer()
            ->add( 'Books', Books::class )
            ->addArgument( $wpdb );

    }

    public function bootPlugin()
    {
        $container = $this->getContainer();
        $container->get('Books');

    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}