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

class Related_Links_Handler {
	const RELATED_LINKS_KEY = "related_links";
	const RELATED_LINKS_SLUG = "related-links";
	
	private $view;
	private static $static_view;
	
	private static $instance;
	
	public function init() {
		//$this->installTables();
		
		$this->view = new Savant3();
		$this->view->setPath("template", TEMPLATEPATH . "/plugins/related-links/templates");
		
		self::$static_view = $this->view;
		
		//add_action("wp_ajax_related_links_reorder", array($this, "reorderLinks"));
		
		add_action('wp_ajax_related_link_reorder', array($this, "reorderLink"));
		
		add_action("wp_ajax_related_links_delete", array($this, "deleteLink"));
		
		add_action("wp_ajax_related_links_add", array($this, "addLink"));
		
		add_action('wp_ajax_related_links_list', array($this, "displayLinks"));
		
		add_action('wp_ajax_related_links_get_one', array($this, "getLinkJson"));
		
		add_action('wp_ajax_related_links_update', array($this, "updateLink"));
		
		add_action("add_meta_boxes", array($this, "metabox"), 11, 2);
		
		add_action("save_post", array($this, 'save'));
		
		//add_action("admin_menu", array($this, "adminMenu"));
		
		self::$instance = $this;
	}
	
	public static function getViewInstance() {
		return self::$static_view;
	}
	
	public static function getInstance() {
		return self::$instance;
	}
	
	public function metabox($post_type, $post) {
		if(post_type_supports($post_type, self::RELATED_LINKS_KEY)) {
			add_meta_box(self::RELATED_LINKS_KEY, "Related Links", array($this, "display_admin_panel"), $post_type, "side", "low");
		}
	}
	
	/*public function adminMenu() {
		add_submenu_page(null, "Related Links Conversion", "RLC", "edit_users", "related_links_conversion", array($this, "conversion"));
	}*/
	
	public function updateLink() {
		$post_id = $_POST['post_id'];
		$link_id = $_POST['link_id'];
		$label = trim($_POST['label']);
		$label = htmlentities($label, ENT_QUOTES, get_option('blog_charset'));
		$href = trim($_POST['href']);
		
		$term = get_term_by('slug', self::RELATED_LINKS_SLUG, 'link_category');
		$updated = wp_insert_link(array(
			'link_id' => $link_id,
			'link_name' => $label,
			'link_url' => $href,
			'link_category' => $term->term_id
		));
		
		Util::json($_POST);
		die();
	}
	
	public function getLinkJson() {
		$post_id = $_POST['post_id'];
		$link_id = $_POST['link_id'];
		
		$link = get_bookmark($link_id);
		
		Util::json($link);
		die();
	}
	
	public function displayLinks() {
		$json = new Services_JSON();
		
		$postId = $_POST['post_id'];
		
		$links = $this->getLinks($postId);
		
		$this->view->assign("links", $links);
		$this->view->assign("post", $post);
		
		echo $this->view->fetch("ajax/feature_link_list.tpl.php");
		
		die();
	}
	
	public function display_admin_panel($post) {
		wp_enqueue_script('related-links-admin', get_bloginfo('stylesheet_directory') . "/plugins/related-links/js/admin.js", array('jquery'));
		
		$links = $this->getLinks($post->ID);
		
		$this->view->assign("links", $links);
		$this->view->assign("post", $post);
		
		echo $this->view->fetch("feature_links.tpl.php");
	}
	
	private function getLinks($post_id) {
		global $wpdb;
		
		return Ode_DBO::getInstance()->query("
			SELECT b.*
			FROM " . $wpdb->postmeta . " AS a
			LEFT JOIN " . $wpdb->links . " AS b ON (b.link_id = a.meta_value)
			LEFT JOIN " . $wpdb->term_relationships . " AS c ON (c.object_id = b.link_id)
			LEFT JOIN " . $wpdb->term_taxonomy . " AS d ON (d.term_taxonomy_id = c.term_taxonomy_id)
			LEFT JOIN " . $wpdb->terms . " AS e ON (e.term_id = d.term_id)
			WHERE a.post_id = " . Ode_DBO::getInstance()->quote($post_id, PDO::PARAM_INT) . "
			AND a.meta_key = " . Ode_DBO::getInstance()->quote(self::RELATED_LINKS_KEY, PDO::PARAM_STR) . "
			AND e.slug= " . Ode_DBO::getInstance()->quote(self::RELATED_LINKS_SLUG, PDO::PARAM_STR) . "
			ORDER BY c.term_order ASC
		")->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function save($post_id) {
		if(wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return $post_id;
		}
	}
	
	/*public function reorderLinks() {
		$post_id = $_POST['post'];
		
		$json = new Services_JSON();
		
		$update = update_post_meta($post_id, self::RELATED_LINKS_KEY, $json->_encode($_POST['data']));
		
		Util::json($_POST);
		die();
	}*/
	
	public function reorderLink() {
		global $wpdb;
		
		$post_id = $_POST['post_id'];
		$link_id = $_POST['link_id'];
		$order = $_POST['order'];
		
		$sth = Ode_DBO::getInstance()->prepare("
			UPDATE " . $wpdb->term_relationships . "
			SET
				term_order = :order
			WHERE object_id = :link_id
		");
		
		$sth->bindParam(":order", $order, PDO::PARAM_INT, 11);
		$sth->bindParam(":link_id", $link_id, PDO::PARAM_INT, 11);
		
		try {
			$sth->execute();
		} catch(PDOException $e) {
			error_log($e->getMessage(), 0);
		} catch(Exception $e) {
			error_log($e->getMessage(), 0);
		}
		
		Util::json($_POST);
		die();
	}
	
	public function deleteLink() {		
		$post_id = $_POST['post_id'];
		$link_id = $_POST['link_id'];
		
		delete_post_meta($post_id, self::RELATED_LINKS_KEY, $link_id);
		
		Util::json($_POST);
		
		die();
	}
	
	public function addLink() {			
		$label = trim($_POST['label']);
		$label = htmlentities($label, ENT_QUOTES, get_option('blog_charset'));
		$href = trim($_POST['href']);
		$post_id = $_POST['post_id'];
		
		$term = get_term_by('slug', self::RELATED_LINKS_SLUG, 'link_category');
		$link_id = wp_insert_link(array('link_name' => $label, 'link_url' => $href, 'link_category' => $term->term_id));
		
		if($link_id != false) {
			$add = add_post_meta($post_id, self::RELATED_LINKS_KEY, $link_id);
		}
		
		Util::json(array("postdata" => $_POST, "link_id" => $link_id));
		
		die();
	}
	
	public static function sidebar() {		
		global $post;
		
		$links = self::getInstance()->getLinks($post->ID);
		
		if(!empty($links)) {
			self::getViewInstance()->assign("links", $links);
		
			echo self::getViewInstance()->fetch("features_related_links.php");
			
			return;
		}
		
		echo "";
	}
	
	/*public function conversion() {
		global $wpdb;
		
		$rls = Ode_DBO::getInstance()->query("
			SELECT a.*
			FROM " . $wpdb->postmeta . " AS a
			WHERE a.meta_key = " . Ode_DBO::getInstance()->quote(self::RELATED_LINKS_KEY, PDO::PARAM_STR) . "
		")->fetchAll(PDO::FETCH_OBJ);
		
		$key = self::RELATED_LINKS_KEY;
		
		$sth = Ode_DBO::getInstance()->prepare("DELETE FROM " . $wpdb->postmeta . " WHERE meta_key = :key");
		$sth->bindParam(":key", $key, PDO::PARAM_STR);
		
		$sth->execute();
		
		//Util::debug($rls);
		$json = new Services_JSON();
		foreach($rls as $rl) {
			$rlAry = $json->decode($rl->meta_value);
			if(is_array($rlAry)) {
				foreach($rlAry as $rLink) {
					$link_id = wp_insert_link(array(
						'link_name' => $rLink->label,
						'link_url' => $rLink->href,
						'link_category' => 1937		
					));
					
					if($link_id != false) {
						$add = add_post_meta($rl->post_id, self::RELATED_LINKS_KEY, $link_id);
					}
				}
			}
		}
	}*/
}

add_action("init", array(new Related_Links_Handler(), "init"));
?>