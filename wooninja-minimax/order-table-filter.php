<?php
// Check if HPOS is active
$high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');

// Register and display custom column for both storage systems
if ($high_performance_order_storage == 'yes') {
    // HPOS: Add a custom column to the WooCommerce Orders page
    add_filter('manage_woocommerce_page_wc-orders_columns', 'register_minimax_column_for_hpos');
    add_action('manage_woocommerce_page_wc-orders_custom_column', 'display_minimax_column_content_for_hpos', 10, 2);
    // HPOS: Add a custom filter checkbox above the Orders table
    add_action('woocommerce_order_list_table_restrict_manage_orders', 'minimax_orders_filter_checkbox_for_hpos');
    // HPOS: Filter orders in the admin based on custom criteria
    add_filter('woocommerce_order_query_args', 'filter_orders_by_minimax_status_for_hpos');
} else {
    // Default storage: Add a custom column to the orders list
    add_filter('manage_edit-shop_order_columns', 'register_minimax_column');
    add_action('manage_shop_order_posts_custom_column', 'display_minimax_column_content', 10, 1);
    // Default storage: Add a custom filter checkbox above the Orders table
    add_action('restrict_manage_posts', 'minimax_orders_filter_checkbox');
    // Default storage: Filter orders based on checkbox status
    add_filter('pre_get_posts', 'filter_orders_by_minimax_status');
}

// Functions for both storage systems follow

// HPOS: Register the custom column
function register_minimax_column_for_hpos($columns) {
    $columns['minimax_delivery_order_id'] = 'MiniMax';
    return $columns;
}

// HPOS: Display content in the custom column
function display_minimax_column_content_for_hpos($column, $order) {
    if ('minimax_delivery_order_id' === $column) {
        // Get the meta value using the provided $order object
        $value = $order->get_meta('minimax_delivery_order_id');
        if (!empty($value)) {
            echo "<mark class='order-status' style='background: green; color: white;'><span>Izdani račun</span></mark>";
        }
    }
}

// HPOS: Add custom filter checkbox
function minimax_orders_filter_checkbox_for_hpos() {
    ?>
 <label>
        Prikaži izdane račune
        <input type="checkbox" style="margin: -4px 5px 0 0; height: 1rem;" name="minimax_delivery_order_id_checked" value="1" <?php checked( isset( $_GET['minimax_delivery_order_id_checked'] ) && '1' === $_GET['minimax_delivery_order_id_checked'] ); ?>>
    </label>
    <?php
}

// HPOS: Modify the query based on checkbox status
function filter_orders_by_minimax_status_for_hpos($query_args) {
    if (isset($_GET['minimax_delivery_order_id_checked']) && '1' === $_GET['minimax_delivery_order_id_checked']) {
        $query_args['meta_query'] = [
            [
                'key' => 'minimax_delivery_order_id',
                'value' => '1',
                'compare' => '='
            ]
        ];
    }
    return $query_args;
}

// Default storage: Register the custom column
function register_minimax_column($columns) {
    $columns['minimax_delivery_order_id'] = 'MiniMax';
    return $columns;
}

// Default storage: Display content in the custom column
function display_minimax_column_content($column) {
    global $post;
    if ('minimax_delivery_order_id' === $column) {
        $order = wc_get_order($post->ID);
        $value = $order ? $order->get_meta('minimax_delivery_order_id') : '';
        if (!empty($value)) {
            echo "<mark class='order-status' style='background: green; color: white;'><span>Izdani račun</span></mark>";
        }
    }
}

// Default storage: Add custom filter checkbox
function minimax_orders_filter_checkbox() {
    ?>
    <label>
        Prikaži izdane račune
        <input type="checkbox" style="margin-left: 5px;" name="minimax_delivery_order_id_checked" value="1" <?php checked( isset( $_GET['minimax_delivery_order_id_checked'] ) && '1' === $_GET['minimax_delivery_order_id_checked'] ); ?>>
    </label>
    <?php
}

// Default storage: Modify the query based on checkbox status
function filter_orders_by_minimax_status($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $screen = get_current_screen();
    if ($screen && 'edit-shop_order' === $screen->id && isset($_GET['minimax_delivery_order_id_checked']) && '1' === $_GET['minimax_delivery_order_id_checked']) {
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = [
            'key' => 'minimax_delivery_order_id',
            'value' => '1',
            'compare' => '='
        ];
        $query->set('meta_query', $meta_query);
    }
}
