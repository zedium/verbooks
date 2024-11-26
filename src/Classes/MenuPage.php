<?php

namespace VerBooks\Classes;

class MenuPage
{

    private $config;

    public function __construct($config){

        $this->config = $config;

        add_action('admin_menu', [$this, 'addHelloWorldMenu']);
    }

    public function addHelloWorldMenu()
    {
        add_menu_page(
            $this->config['page_title'],            // Page title
            $this->config['menu_title'],            // Menu title
            'manage_options',         // Capability
            $this->config['menu_slug'],            // Menu slug
            [$this, 'renderHelloWorldPage'], // Callback
            $this->config['icon'],       // Icon
            20                        // Position
        );
    }

    public function renderHelloWorldPage()
    {
        echo '<div class="wrap"><h1>سلام دنیا</h1></div>';

    }
}