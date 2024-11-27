<?php

namespace VerBooks\Classes;

class Books
{
    private $table_name;


    private $posttype_name;

    private $ID;
    private $post_id;
    private $isbn;

    private $db;
    private static $instance = null;
    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * @param mixed $ID
     */
    public function setID($ID): void
    {
        $this->ID = $ID;
    }

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->post_id;
    }

    /**
     * @param mixed $post_id
     */
    public function setPostId($post_id): void
    {
        $this->post_id = $post_id;
    }

    /**
     * @return mixed
     */
    public function getIsbn()
    {
        return $this->isbn;
    }

    /**
     * @param mixed $isbn
     */
    public function setIsbn($isbn): void
    {
        $this->isbn = $isbn;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table_name;
    }

    /**
     * @param $db
     */
    public function __construct($db=null)
    {
        global $wpdb;
        $this->db = $db ?? $wpdb;
        $this->table_name = 'books_info';
        $this->posttype_name = 'Book';
        $this->registerActionsAndFilters();
    }

    public static function getInstance()
    {

        if ( !self::$instance ) {
            self::$instance = new Books();
        }
        return self::$instance;

    }

    private function registerActionsAndFilters()
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('add_meta_boxes', [$this, 'initMetaBoxes']);
        add_action('save_post', [$this, 'savePostAction']);
        add_action('init', [$this,'registerTaxonomies']);
        add_action('before_delete_post', [$this,'delete_books_info_on_post_delete']);
    }

    public function createBooksTable(){

        if(!$this->checkTableExistence()){

            $table_name = $this->db->prefix . $this->table_name;

            // SQL to create the table
            $charset_collate = $this->db->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                        ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                        post_id bigint(20) unsigned NOT NULL,
                        isbn varchar(20) NOT NULL,
                        PRIMARY KEY  (ID),
                        KEY post_id (post_id)
                    ) $charset_collate;";

            // Include the WordPress upgrade script to handle table creation
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            // Create the table
            dbDelta($sql);


        }


    }

    public function checkTableExistence(){
        $table_name = $this->db->prefix . $this->table_name;
        // Check if the table exists
        $table_exists = $this->db->get_var(
            $this->db->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
        );

        return $table_name == $table_exists;
    }

    public function registerPostType(){

        register_post_type(
            $this->posttype_name,
            array(
                'labels'      => array(
                    'name'          => __('Books', VB_TEXT_DOMAIN),
                    'singular_name' => __('Book', VB_TEXT_DOMAIN),
                ),
                'public'      => true,
                'has_archive' => true,
                'rewrite'     => array( 'slug' => 'book' ),
                'menu_position'=>5,
                'map_meta_cap'=>true,
                'menu_icon'          => 'dashicons-book',
                'supports'           => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
            )
        );

        //$this->metaBoxes->render();

    }

    public function initMetaBoxes(){
        add_meta_box(
            'verbooks_metabox',
            __('ISBN', VB_TEXT_DOMAIN),
            [$this, 'renderMetaBoxesCallback'],
            $this->posttype_name,
            'advanced',
            'high'
        );
    }

    public function renderMetaBoxesCallback(){

        $postID = sanitize_text_field( $_GET['post'] ?? 0 );

        /*
         * Get metabox data from `books_info` table that belong to current post
         * The data will be shown as html default values.
         */

        $result = $this->getMetaBoxByPostID($postID);

        $isbn = '';

        if(isset( $result )) {
            $isbn = $result->isbn;
        }
        ?>
        <?php echo wp_nonce_field('verbooks_nonce', 'verbooks_nonce_field'); ?>
        <table>
            <tr>
                <td style="width: 50%">ISBN</td>
                <td><input type="text" size="40" name="isbn" value="<?php echo esc_html( $isbn ) ?>" /></td>
            </tr>

        </table>
        <?php
    }

    public function getMetaBoxByPostID($postID){
        global $wpdb;

        $sql_query = $wpdb->prepare(
            'SELECT * FROM ' .
            $wpdb->base_prefix . $this->table_name .
            " WHERE post_id = '%d'",
            $postID);

        return $wpdb->get_row($sql_query);
    }

    public function savePostAction($postID){


        $isbn = '';

        if( !isset($_POST['verbooks_nonce_field']) )
            return $postID;

        $nonce = $_POST['verbooks_nonce_field'];

        if( !wp_verify_nonce($nonce , 'verbooks_nonce' ) )
            return $postID;

        if( isset($_POST['isbn']) ){
            $isbn = sanitize_text_field( strtoupper( $_POST['isbn'] ));
        }



        if( !$this->isPostMetaExists($postID) ){

            $this->insertPostMeta($postID, $isbn);

        }
        else{

            $this->updatePostMeta($postID, $isbn);

        }


    }

    public function isPostMetaExists($postID)
    {


        $sql_query = $this->db->prepare(
            'SELECT id,post_id FROM ' .
            $this->db->base_prefix . $this->table_name .
            " WHERE post_id = '%d'",
            $postID);

        return !empty($this->db->get_row($sql_query));
    }

    public function insertPostMeta($post_id, $isbn)
    {


        $sql_query = $this->db->prepare(
            'INSERT INTO ' .
            $this->db->base_prefix . $this->table_name .
            ' (`post_id`, `isbn`) ' .
            ' VALUES (%d, %s)', [$post_id, $isbn]
        );

        $this->db->query($sql_query);
    }

    public function updatePostMeta($post_id, $isbn )
    {

        $sql_query = $this->db->prepare(
            'UPDATE  `' .
            $this->db->base_prefix . $this->table_name .
            "` SET `isbn`='%s' 
            WHERE `post_id`='%s'" ,
            [$isbn, $post_id]
        );

        $this->db->query($sql_query);
    }

    function registerTaxonomies() {
        // Labels for Publishers
        $publisher_labels = array(
            'name'              => _x('Publishers', 'taxonomy general name', VB_TEXT_DOMAIN),
            'singular_name'     => _x('Publisher', 'taxonomy singular name', VB_TEXT_DOMAIN),
            'search_items'      => __('Search Publishers', VB_TEXT_DOMAIN),
            'all_items'         => __('All Publishers', VB_TEXT_DOMAIN),
            'parent_item'       => __('Parent Publisher', VB_TEXT_DOMAIN),
            'parent_item_colon' => __('Parent Publisher:', VB_TEXT_DOMAIN),
            'edit_item'         => __('Edit Publisher', VB_TEXT_DOMAIN),
            'update_item'       => __('Update Publisher', VB_TEXT_DOMAIN),
            'add_new_item'      => __('Add New Publisher', VB_TEXT_DOMAIN),
            'new_item_name'     => __('New Publisher Name', VB_TEXT_DOMAIN),
            'menu_name'         => __('Publishers', VB_TEXT_DOMAIN),
        );

        // Args for Publishers
        $publisher_args = array(
            'hierarchical'      => true, // شبیه دسته‌بندی
            'labels'            => $publisher_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'publisher'),
        );

        // Register Publisher Taxonomy
        register_taxonomy('publisher', array('book'), $publisher_args);

        // Labels for Authors
        $author_labels = array(
            'name'              => _x('Authors', 'taxonomy general name', VB_TEXT_DOMAIN),
            'singular_name'     => _x('Author', 'taxonomy singular name', VB_TEXT_DOMAIN),
            'search_items'      => __('Search Authors', VB_TEXT_DOMAIN),
            'all_items'         => __('All Authors', VB_TEXT_DOMAIN),
            'parent_item'       => __('Parent Author', VB_TEXT_DOMAIN),
            'parent_item_colon' => __('Parent Author:', VB_TEXT_DOMAIN),
            'edit_item'         => __('Edit Author', VB_TEXT_DOMAIN),
            'update_item'       => __('Update Author', VB_TEXT_DOMAIN),
            'add_new_item'      => __('Add New Author', VB_TEXT_DOMAIN),
            'new_item_name'     => __('New Author Name', VB_TEXT_DOMAIN),
            'menu_name'         => __('Authors', VB_TEXT_DOMAIN),
        );

        // Args for Authors
        $author_args = array(
            'hierarchical'      => false, // شبیه برچسب‌ها
            'labels'            => $author_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'author'),
        );

        // Register Author Taxonomy
        register_taxonomy('author', array('book'), $author_args);
    }

    function delete_books_info_on_post_delete($post_id) {

        $post_type = get_post_type($post_id);
        if ($post_type !== 'Book') {
            return;
        }


        $table_name = $this->db->base_prefix . 'books_info';


        $this->db->delete($table_name, ['post_id' => $post_id], ['%d']);
    }

}