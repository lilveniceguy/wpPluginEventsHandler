<?php

function nme_register_custom_post_type() {
    $args = array(
        'public' => true,
        'exclude_from_search' => true,
        'supports' => array('title', 'editor'),
        'labels' => array(
            'name' => 'Events Users DB',
            'singular_name' => 'Event User',
        ),
        'rewrite' => false,
        'show_in_rest' => true,
        'show_in_menu' => false
    );
    register_post_type('events-users-db', $args);
}
add_action('init', 'nme_register_custom_post_type');

function nme_register_eventos_post_type() {
    $labels = array(
        'name' => 'Eventos',
        'singular_name' => 'Evento',
        'menu_name' => 'Eventos',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Evento',
        'edit_item' => 'Edit Evento',
        'new_item' => 'New Evento',
        'view_item' => 'View Evento',
        'search_items' => 'Search Eventos',
        'not_found' => 'No eventos found',
        'not_found_in_trash' => 'No eventos found in Trash',
        'all_items' => 'All Eventos',
        'archives' => 'Evento Archives',
        'insert_into_item' => 'Insert into evento',
        'uploaded_to_this_item' => 'Uploaded to this evento',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'filter_items_list' => 'Filter eventos list',
        'items_list_navigation' => 'Eventos list navigation',
        'items_list' => 'Eventos list',
        'item_published' => 'Evento published.',
        'item_published_privately' => 'Evento published privately.',
        'item_reverted_to_draft' => 'Evento reverted to draft.',
        'item_scheduled' => 'Evento scheduled.',
        'item_updated' => 'Evento updated.',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'eventos'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 5,
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'show_in_rest' => true,
        'rest_base' => 'eventos',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('eventos', $args);
}
add_action('init', 'nme_register_eventos_post_type');


// Add custom columns to Eventos post type list table
function nme_add_custom_columns($columns) {
    $columns['region'] = 'Región';
    $columns['comuna'] = 'Comuna';
    return $columns;
}
add_filter('manage_eventos_posts_columns', 'nme_add_custom_columns');

// Display data in custom columns
function nme_custom_column_content($column, $post_id) {
    switch ($column) {
        case 'region':
            $region = get_post_meta($post_id, 'nme_region', true);
            echo $region;
            break;
        case 'comuna':
            $comuna = get_post_meta($post_id, 'nme_comuna', true);
            echo $comuna;
            break;
    }
}
add_action('manage_eventos_posts_custom_column', 'nme_custom_column_content', 10, 2);


// Add custom filters to Eventos post type list table
function nme_add_custom_filters() {
    global $wpdb;

    // Region filter
    $regiones = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = 'nme_region'");
    $current_region = isset($_GET['region']) ? $_GET['region'] : '';
    echo '<select name="region">';
    echo '<option value="">All Regiones</option>';
    foreach ($regiones as $region) {
        $selected = ($current_region == $region) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($region) . '" ' . $selected . '>' . esc_html($region) . '</option>';
    }
    echo '</select>';

    // Comuna filter
    $comunas = $wpdb->get_col("SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = 'nme_comuna'");
    $current_comuna = isset($_GET['comuna']) ? $_GET['comuna'] : '';
    echo '<select name="comuna">';
    echo '<option value="">All Comunas</option>';
    foreach ($comunas as $comuna) {
        $selected = ($current_comuna == $comuna) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($comuna) . '" ' . $selected . '>' . esc_html($comuna) . '</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'nme_add_custom_filters');

// Filter posts based on custom filters
function nme_filter_posts($query) {
    global $pagenow, $post_type;
    if ($pagenow == 'edit.php' && $post_type == 'eventos' && isset($_GET['region']) && $_GET['region'] != '') {
        $query->query_vars['meta_query'][] = array(
            'key' => 'nme_region',
            'value' => $_GET['region'],
            'compare' => '='
        );
    }
    if ($pagenow == 'edit.php' && $post_type == 'eventos' && isset($_GET['comuna']) && $_GET['comuna'] != '') {
        $query->query_vars['meta_query'][] = array(
            'key' => 'nme_comuna',
            'value' => $_GET['comuna'],
            'compare' => '='
        );
    }
}
add_action('parse_query', 'nme_filter_posts');

?>
