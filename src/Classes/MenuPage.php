<?php

namespace VerBooks\Classes;

class MenuPage
{

    private $config;
    private $data_table;

    /**
     * @return mixed|null
     */
    public function getDataTable(): mixed
    {
        return $this->data_table;
    }

    /**
     * @param mixed|null $data_table
     */
    public function setDataTable(mixed $data_table): void
    {
        $this->data_table = $data_table;
    }

    public function __construct($config, $data_table=null){

        $this->config = $config;
        $this->data_table = $data_table;
        add_action('admin_menu', [$this, 'addBooksMenu']);
    }


    public function addBooksMenu()
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
        $app = $GLOBALS['verbooks'];
        $data_table = $app->getApplication()->get('DataTable');

        $data_table->prepare_items();
    ?>
        <div id="wrap">
            <h1 class="wp-heading-inline"><?php _e('Books List', VB_TEXT_DOMAIN); ?></h1>
            <form method="get">
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>">
                <?php $data_table->search_box(__('Search Books', VB_TEXT_DOMAIN), 'book_search'); ?>
            </form>
    <?php
        $data_table->display();
    ?>
        </div>
    <?php

    }
}