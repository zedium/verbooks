<?php



/*
 * This file will be called when plugin is uninstalling.
 * It will drop table and purge the custom `book_info` data
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}


if (!file_exists(__DIR__ . '/vendor/autoload.php'))
    die('autoload.php not found');


require_once __DIR__ . '/vendor/autoload.php';

global $wpdb;

$sql_query = 'DROP TABLE ' . $wpdb->base_prefix. 'books_info;';

$wpdb->query($sql_query);