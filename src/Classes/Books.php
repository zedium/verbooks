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
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        $this->table_name = 'books_info';
        $this->posttype_name = 'Book';
        $this->registerActionsAndFilters();
    }

    private function registerActionsAndFilters()
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('add_meta_boxes', [$this, 'initMetaBoxes']);
        add_action('save_post', [$this, 'savePostAction']);
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
}