<?php

namespace VerBooks\Services;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rabbit\Contracts\BootablePluginProviderInterface;
use VerBooks\Classes\Books;
use VerBooks\Classes\DataTable;

class DataTableServiceProvider extends AbstractServiceProvider implements BootablePluginProviderInterface,BootableServiceProviderInterface
{

    protected $provides = [
        'DataTable',
    ];

    public function register()
    {

        global $wpdb;



        $this->getContainer()
            ->add( 'DataTable', DataTable::class )
            ->addArgument( $wpdb );

    }

    public function bootPlugin()
    {
//        $container = $this->getContainer();
//        $container->get('DataTable');

    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }

}