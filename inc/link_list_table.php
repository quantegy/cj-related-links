<?php
if(!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Related_Links_List_Table extends WP_List_Table {

    public function get_columns() {
        $columns = array(
            'id' => 'Post',
            'label' => 'Label',
            'url' => 'URL'
        );

        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    public function get_sortable_columns() {
        return array('label' => array('label', true));
    }

    private function table_data() {
        global $wpdb;

        $data = array();

        $sql = "SELECT a.*
                FROM " . $wpdb->prefix . \CJ_Related_Links\Related_Links_Handler::TABLE_SUFFIX . " AS a";

        if(!empty($_POST['s'])) {
            $sql .= " WHERE a.label LIKE '%" . $_POST['s'] . "%'";
        }

        $data = $wpdb->get_results($sql);

        array_walk($data, function(&$item, $key) {
            $item = (array)$item;
        });

        return $data;
    }

    public function column_id($item) {
        $postId = $item['post_id'];

        $html = '<span style="font-size: larger; font-weight: bold; font-style: italic;"><a href="' . admin_url('post.php?post='.$postId.'&action=edit') . '">' . get_post_field('post_title', $postId, 'display') . '</a></span>';
        $html .= '<br /><br />';
        $html .= '<a href="' . admin_url('admin.php?page=cj_related_links_edit&id=' . $item['id']) . '">Edit</a>';

        return $html;
    }

    public function column_label($item) {
        $label = stripslashes($item['label']);
        $label = htmlspecialchars_decode($label);
        return $label;
    }

    public function column_url($item) {
        return '<a href="' . $item['url'] . '">' . $item['url'] . '</a>';
    }

    private function sort_data($a, $b) {
        $orderby = 'label';
        $order = 'asc';

        if(!empty($_GET['orderby'])) {
            $orderby = $_GET['orderby'];
        }

        if(!empty($_GET['order'])) {
            $order = $_GET['order'];
        }

        $result = strnatcmp($a[$orderby], $b[$orderby]);

        if($order == 'asc') {
            return $result;
        }

        return -$result;
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort($data, array(&$this, 'sort_data'));

        // pagination
        $perPage = 20;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args(array(
            'total_items' => $totalItems,
            'per_page' => $perPage
        ));

        $data = array_slice($data, (($currentPage-1)*$perPage), $perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }
}