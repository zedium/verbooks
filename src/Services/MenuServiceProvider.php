<?php

namespace VerBooks\Services;

use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rabbit\Contracts\BootablePluginProviderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

use VerBooks\Classes\MenuPage;

class MenuServiceProvider extends AbstractServiceProvider implements BootablePluginProviderInterface,BootableServiceProviderInterface
{

    protected $provides = [
        'MenuService',
    ];

    /**
     * @inheritDoc
     */
    public function register()
    {
        $container = $this->getContainer();
        $config = $container->config('menu');

        $this->getContainer()
            ->add( 'MenuService', MenuPage::class )
            ->addArgument( $config );

    }


    public function bootPlugin()
    {

        $container = $this->getContainer();
        $container->get('MenuService');

    }

    public function boot()
    {
        // TODO: Implement boot() method.
    }
}