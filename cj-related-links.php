<?php
/**
 * Plugin Name: CJ Related Posts
 * Plugin URI: http://sites.uci.edu/cwalsh/
 * Description: Add related posts to a single post.
 * Version: 0.0.1
 * Author: Christopher J. Walsh
 * Author URI: http://sites.uci.edu/cwalsh/
 * Text Domain: cjrelatedposts
 * License: GPL3
 */

defined('ABSPATH') or die("No script kiddies please!");

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));

@define('CJ_RELATED_LINKS_VERSION_OPTION', 'cj_related_links_version');

require_once 'inc/cj_related_links_handler.php';
require_once 'inc/cj_related_links_widget.php';

register_activation_hook(__FILE__, 'cj_related_links_install');

add_action('init', array(new \CJ_Related_Links\Related_Links_Handler(), "init"));

add_action('plugins_loaded', 'cj_related_links_check');

add_action('widgets_init', 'cj_related_links_load_widget');

// FUNCTIONS

/**
 * Load up widget
 */
function cj_related_links_load_widget() {
    register_widget('CJ_Related_Links_Widget');
}

/**
 * this will run update checks
 */
function cj_related_links_check() {
    if(get_site_option(CJ_RELATED_LINKS_VERSION_OPTION) != \CJ_Related_Links\Related_Links_Handler::VERSION) {
        cj_related_links_install();
    }
}

/**
 * Initial install of plugin
 * @global type $wpdb
 */
function cj_related_links_install() {
    global $wpdb;
    
    $tableName = $wpdb->prefix . \CJ_Related_Links\Related_Links_Handler::TABLE_SUFFIX;
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $tableName ("
            . "id INT(11) NOT NULL AUTO_INCREMENT,"
            . "post_id INT(11) NOT NULL,"
            . "ordinal SMALLINT(2) NOT NULL DEFAULT 0,"
            . "label VARCHAR(255) NOT NULL DEFAULT '',"
            . "url VARCHAR(255) NOT NULL DEFAULT '',"
            . "UNIQUE KEY id (id)) $charsetCollate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    
    add_option(CJ_RELATED_LINKS_VERSION_OPTION, \CJ_Related_Links\Related_Links_Handler::VERSION);
}
?>