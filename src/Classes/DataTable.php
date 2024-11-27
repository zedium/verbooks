<?php

namespace VerBooks\Classes;

class DataTable extends \WP_List_Table
{
    /**
     * @var Data from database table
     */
    private $table_data;

    private $db;

    private static $instance = null;

    public function __construct($db=null,$args = array())
    {
        parent::__construct($args);
        $this->db = $db;
    }

    public static function getInstance($db)
    {

        if ( !self::$instance ) {
            self::$instance = new DataTable($db);
        }
        return self::$instance;

    }

    public function get_columns()
    {
        $columns = array(

            'post_id'=> __('ID', VB_TEXT_DOMAIN),
            'post_title'=>__('Title', VB_TEXT_DOMAIN),
            'isbn'=>__('ISBN', VB_TEXT_DOMAIN),

        );

        return $columns;
    }

    function prepare_items()
    {
        $columns = $this->get_columns();

        /*
         * Get data from database table
         */
        $this->table_data = $this->get_table_data();
        $hidden = array();

        $this->_column_headers = array($columns, $hidden, null);

        $this->items = $this->table_data;

    }

    public function get_table_data() {

        $table_name = Books::getInstance()->getTableName();
        $books_table = $this->db->base_prefix . $table_name;
        $posts_table = $this->db->base_prefix . 'posts';

        $search = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';

        $sql_query = "SELECT p.ID AS post_id, p.post_title, b.isbn
         FROM $posts_table AS p
         INNER JOIN $books_table AS b
         ON p.ID = b.post_id
         WHERE p.post_type = 'book' AND p.post_status = 'publish'";
        if(!empty($search)) {
            $sql_query .= " AND (post_title LIKE '" . '%' . $this->db->esc_like($search) . '%\'';
            $sql_query .= " OR isbn LIKE '" . '%' . $this->db->esc_like($search) . '%\')';
        }

        return $this->db->get_results($sql_query, ARRAY_A);



    }

    function column_default($item, $column_name)
    {

        return $item[$column_name];

    }

}