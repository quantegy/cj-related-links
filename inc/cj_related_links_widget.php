<?php
class CJ_Related_Links_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct('cj_related_links_widget', __("CJ Related Links", 'wp_widget_domain'), array(
            'description' => __('Related Links for posts in the sidebar', 'wp_widget_domain')
        ));
    }
    
    public function form($instance) {
        if($instance) {
            $title = esc_attr($instance['title']);
            /*$text = esc_attr($instance['text']);
            $textarea = esc_attr($instance['textarea']);*/
        } else {
            $title = '';
            /*$text = '';
            $textarea = '';*/
        }
        
        $titleId = $this->get_field_id('title');
        $titleName = $this->get_field_name('title');
        
        /*$textId = $this->get_field_id('text');
        $textName = $this->get_field_name('text');
        
        $textareaId = $this->get_field_id('textarea');
        $textareaName = $this->get_field_name('textarea');*/
        
        $html = '';
        ob_start();
        include 'templates/admin_widget.php';
        $html .= ob_get_clean();
        
        echo $html;
    }
    
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        
        // fields
        $instance['title'] = strip_tags($new_instance['title']);
        /*$instance['text'] = strip_tags($new_instance['text']);
        $instance['textarea'] = strip_tags($new_instance['textarea']);*/
        
        return $instance;
    }
    
    public function widget($args, $instance) {
        global $post;
        
        wp_enqueue_style('cj-rl-front', plugins_url() . '/' . dirname(dirname(plugin_basename(__FILE__))) . '/css/front.css');
        
        // widget options
        $title = apply_filters('widget_title', $instance['title']);
        
        $links = \CJ_Related_Links\Related_Links_Handler::getInstance()->getLinks($post->ID);
        
        $html = '';
        ob_start();
        include 'templates/front_widget.php';
        $html .= ob_get_clean();
        
        echo $html;
        
        
    }
}
