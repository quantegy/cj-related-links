<?php
namespace CJ_Related_Links;

class Related_Links_Handler {
    const VERSION = '0.0.2';
    const TABLE_SUFFIX = 'cj_related_links';
    const TABLE_POST_CONNECTOR = 'cj_related_links_posts';

    const RELATED_LINKS_KEY = "related_links";
    const RELATED_LINKS_SLUG = "related-links";

    private static $instance;
    
    public function __construct() {}

    public function init() {
        add_action('wp_ajax_related_link_reorder', array($this, "reorderLink"));
        add_action("wp_ajax_related_links_add", array($this, "addLink"));
        add_action('wp_ajax_related_links_list', array($this, "displayLinks"));
        add_action('wp_ajax_related_links_get_all', array($this, 'getAll'));
        add_action('wp_ajax_related_links_get_one', array($this, "getLinkJson"));
        add_action('wp_ajax_related_links_update_label', array($this, 'updateLabel'));
        add_action('wp_ajax_related_links_update_link', array($this, 'updateLink'));
        add_action('wp_ajax_related_links_remove_link', array($this, 'removeLink'));
        add_action('wp_ajax_related_links_json_search_urls', array($this, 'jsonSearchUrls'));

        add_action("add_meta_boxes", array($this, "metabox"), 11, 2);

        add_action("save_post", array($this, 'save'));

        self::$instance = $this;
    }

    public function jsonSearchUrls() {
        global $wpdb;

        $terms = trim($_POST['query']);

        $links = $wpdb->get_results("SELECT id, label, url FROM " . $wpdb->prefix.self::TABLE_SUFFIX . " WHERE url LIKE '%{$terms}%'");

        echo json_encode($links);

        die();
    }
    
    public function getAll() {
        
        $postId = $_GET['post_id'];
        
        $links = $this->getLinks($postId);
        
        $html = '';
        ob_start();
        include 'templates/features_related_links.php';
        $html .= ob_get_clean();

        echo $html;
        
        die();
    }
    
    public function updateLabel() {
        global $wpdb;
        
        $linkId = $_POST['link_id'];
        $label = $_POST['label'];
        
        $wpdb->update($wpdb->prefix.self::TABLE_SUFFIX, array(
            'label' => $label
        ), array(
            'id' => $linkId
        ), array(
            '%s'
        ), array(
            '%d'
        ));
        
        if(function_exists('json_encode')) {
            echo json_encode($_POST);
        }
        
        die();
    }
    
    public function updateLink() {
        global $wpdb;
        
        $linkId = $_POST['link_id'];
        $url = $_POST['url'];
        
        $wpdb->update($wpdb->prefix.self::TABLE_SUFFIX, array(
            'url' => $url
        ), array(
            'id' => $linkId
        ), array(
            '%s'
        ), array(
            '%d'
        ));
        
        if(function_exists('json_encode')) { echo json_encode($_POST); }

        die();
    }

    public static function getInstance() {
        return self::$instance;
    }

    public function metabox($post_type, $post) {
        $postFormat = get_post_format();
        
        if ($post_type == 'post' && $postFormat !== 'link') {
            add_meta_box(self::RELATED_LINKS_KEY, "Related Links", array($this, "display_admin_panel"), $post_type, "side", "low");
        }
    }

    /* public function adminMenu() {
      add_submenu_page(null, "Related Links Conversion", "RLC", "edit_users", "related_links_conversion", array($this, "conversion"));
      } */

    public function getLinkJson() {
        global $wpdb;
        
        $post_id = $_POST['post_id'];
        $link_id = $_POST['link_id'];

        $link = get_bookmark($link_id);

        die();
    }

    public function displayLinks() {
        
        $postId = $_POST['post_id'];

        $links = $this->getLinks($postId);

        var_dump($links);

        die();
    }

    public function display_admin_panel($post) {
        $links = $this->getLinks($post->ID);
        $html = '<!-- begin admin panel output -->';
        ob_start();
        include 'templates/feature_links.php';
        $html .= ob_get_clean();

        echo $html;
    }

    /**
     * 
     * @global wpdb $wpdb
     * @param integer $post_id
     * @return array|mixed
     */
    public function getLinks($post_id) {
        global $wpdb;
        
        /*$links = $wpdb->get_results(""
                . "SELECT a.id, a.post_id, a.ordinal, a.label, a.url "
                . "FROM " . $wpdb->prefix . self::TABLE_SUFFIX . " AS a "
                . "WHERE a.post_id = $post_id "
                . "ORDER BY a.ordinal "
                . "ASC");*/
        $sql = ""
            . "SELECT a.id, b.post_id, b.ordinal, a.label, a.url "
            . "FROM " . $wpdb->prefix . self::TABLE_POST_CONNECTOR . " AS b "
            . "LEFT JOIN " . $wpdb->prefix . self::TABLE_SUFFIX . " AS a ON (a.id = b.link_id) "
            . "WHERE b.post_id = $post_id "
            . "ORDER BY b.ordinal "
            . "ASC";

        $links = $wpdb->get_results($sql);
        
        return $links;
    }

    public function save($post_id) {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return $post_id;
        }
    }

    public function reorderLink() {
        global $wpdb;
        
        $order = $_POST['order'];
        $linkId = $_POST['link_id'];
        $postId = $_POST['post_id'];
        
        $wpdb->update($wpdb->prefix.self::TABLE_POST_CONNECTOR, array(
            'ordinal' => $order
        ), array(
            'link_id' => $linkId,
            'post_id' => $postId
        ), array('%d'), array('%d', '%d'));
        
        if(function_exists('json_encode')) echo json_encode($_POST);

        die();
    }

    public function deleteLink() {
        global $wpdb;

        $post_id = $_POST['post_id'];
        $link_id = $_POST['link_id'];

        //delete_post_meta($post_id, self::RELATED_LINKS_KEY, $link_id);
        $wpdb->delete($wpdb->prefix.self::TABLE_POST_CONNECTOR, array(
            'post_id' => $post_id,
            'link_id' => $link_id
        ), array('%d', '%d'));

        die();
    }

    public function addLink() {
        global $wpdb;
        
        $tableName = $wpdb->prefix . self::TABLE_SUFFIX;
        
        $label = trim($_POST['label']);
        $label = htmlentities($label, ENT_QUOTES, get_option('blog_charset'));
        $href = trim($_POST['href']);
        $post_id = $_POST['post_id'];

        $curLink = $wpdb->get_results("SELECT a.id, b.post_id, b.ordinal, a.label, a.url
                                        FROM " . $wpdb->prefix.self::TABLE_SUFFIX . " AS a
                                        WHERE a.url = '{$href}'
                                        LIMIT 0,1");

        if(empty($curLink)) { // create new link
            $wpdb->insert($tableName, array(
                'label' => $label,
                'url' => $href
            ));

            $linkId = $wpdb->insert_id;
        } else { // get existing link
            $linkId = $curLink->id;
        }

        // assign link to post
        $wpdb->insert($wpdb->prefix.self::TABLE_POST_CONNECTOR, array(
            'post_id' => $post_id,
            'link_id' => $linkId
        ));
        
        if(function_exists('json_encode')) {
            echo json_encode($_POST);
        }
        
        die();
    }

    public static function sidebar() {
        global $post;

        $links = self::getInstance()->getLinks($post->ID);

        if (!empty($links)) {
            self::getViewInstance()->assign("links", $links);

            echo self::getViewInstance()->fetch("features_related_links.php");

            return;
        }

        echo "";
    }

    public function removeLink() {
        global $wpdb;
        
        $linkId = intval($_POST['link_id']);
        $postId = intval($_POST['post_id']);
        
        $wpdb->delete($wpdb->prefix.self::TABLE_POST_CONNECTOR, array(
            'post_id' => $postId,
            'link_id' => $linkId
        ), array('%d', '%d'));
        
        if(function_exists('json_encode')) { echo json_encode($_POST); }
        
        die();
    }
}
