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

require_once 'inc/link_list_table.php';

register_activation_hook(__FILE__, 'cj_related_links_install');

add_action('init', array(new \CJ_Related_Links\Related_Links_Handler(), "init"));

add_action('plugins_loaded', 'cj_related_links_check');

add_action('widgets_init', 'cj_related_links_load_widget');

add_action('admin_menu', 'cj_related_links_admin_menu');

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

function cj_related_links_admin_menu() {
    add_menu_page('Related Links', 'CJ Related Links', 'delete_posts', 'cj_related_links_admin', 'cj_related_links_admin_page');
    add_submenu_page(null, 'Edit related Link', null, 'delete_posts', 'cj_related_links_edit', 'cj_related_links_edit_page');
}

function cj_related_links_admin_page() {
    $listTable = new Related_Links_List_Table();
    $listTable->prepare_items();

    ?>
    <div class="wrap">
        <div id="icon-users" class="icon32"></div>
        <h2>Related Links</h2>
        <form method="post">
            <input type="hidden" name="page" value="cj_related_links_admin" />
            <?php $listTable->search_box('search', 'search_id'); ?>
        </form>
        <?php $listTable->display(); ?>
    </div>
    <?php
}

function cj_related_links_edit_page() {
    global $wpdb;

    if(empty($_REQUEST['id'])) {
        die('Sorry');
    }

    $linkId = preg_replace("/[^-a-zA-Z0-9_]/", '', $_REQUEST['id']);

    $valid = true;
    if(empty($_POST['linkLabel']) || empty($_POST['linkUrl'])) {
        $valid = false;
    }

    if(!preg_match("/(ftp|http|https)://.*$/", $_POST['linkUrl'])) {
        $valid = false;
    }

    if(isset($_POST['isUpdate'])) {
        if($valid === true) {
            $update = $wpdb->update($wpdb->prefix . \CJ_Related_Links\Related_Links_Handler::TABLE_SUFFIX, array(
                'label' => $_POST['linkLabel'],
                'url' => $_POST['linkUrl']
            ), array('id' => $_POST['id']), array(
                '%s',
                '%s'
            ), array('%d'));
        }
    }

    $sql = "SELECT *
            FROM " . $wpdb->prefix . \CJ_Related_Links\Related_Links_Handler::TABLE_SUFFIX . "
            WHERE id = '{$linkId}'
            LIMIT 0,1";

    $link = $wpdb->get_row($sql);

    $label = htmlspecialchars_decode($link->label);
    $label = stripslashes($label);
    ?>
    <div class="wrap">
        <h2>Edit Link <em><?php echo $label; ?></em></h2>
        <?php if(isset($_POST['isUpdate']) && $valid === true): ?>
        <div id="message" class="updated below-h2">
            Link updated.
        </div>
        <?php endif; ?>
        <div class="postbox">
            <div class="inside">
                <form name="link" method="post" id="link" autocomplete="off">
                    <input type="hidden" name="page" value="cj_related_links_edit" />
                    <input type="hidden" name="id" value="<?php echo $link->id; ?>" />
                    <input type="hidden" name="isUpdate" value="1" />
                    <div>
                        <label for="linkLabel">Label</label>
                        <input type="text" class="input" name="linkLabel" id="linkLabel" style="width: 98%;" value="<?php echo $label; ?>" />
                    </div>
                    <div>
                        <label for="linUrl">URL</label>
                        <input type="text" class="input" id="linkUrl" name="linkUrl" style="width: 98%;" value="<?php echo $link->url; ?>" />
                    </div>
                    <div style="margin: 10px 0 10px 0;">
                        <button class="button" type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}