<?php
/*
Plugin Name: Netmotors Events Plugin
Description: A plugin to upload txt, xlsx, and csv files and store data in a custom table.
Version: 1.1
Author: Jose Lopez
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Load Includes
$includes = [
    'includes/admin-page.php',
    'includes/ajax-handlers.php',
    'includes/upload-handlers.php',
    'includes/custom-post-types.php',
    'includes/meta-boxes.php'
];
foreach ($includes as $file) {
    require_once plugin_dir_path(__FILE__) . $file;
}

// Enqueue Scripts and Styles
function nme_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('nme-custom-js', plugins_url('/js/custom.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('nme-custom-js', 'nme_ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'nme_nonce' => wp_create_nonce('nme_nonce')));
    wp_enqueue_style('nme-custom-css', plugins_url('/css/custom.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'nme_enqueue_scripts');

function nme_enqueue_eventos_js() {
    global $post;
    if (is_singular() && strpos($post->post_name, 'evento') !== false) {
        wp_enqueue_script('nme-eventos-js', plugins_url('/js/eventos.js', __FILE__), array('jquery'), '1.0', true);
        // Retrieve the associated post IDs
        $associated_posts = get_post_meta($post->ID, 'associated_posts', true);

        // Ensure it's an array
        if (!is_array($associated_posts)) {
            $associated_posts = array();
        }

        // Pass the associated post IDs to JavaScript
        wp_localize_script('nme-eventos-js', 'myScriptData', array(
            'associatedPosts' => $associated_posts,
        ));
    }
}
add_action('wp_enqueue_scripts', 'nme_enqueue_eventos_js');


// Add Menu Page
add_action('admin_menu', 'nme_register_menu_page');

// Add action links on the Plugins page
function nme_plugin_action_links($links) {
    $links[] = '<a href="' . admin_url('admin.php?page=nme-db-manager') . '">Settings</a>';
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'nme_plugin_action_links');

// Handle file uploads and display data
add_action('wp_ajax_nme_handle_file_upload_ajax', 'nme_handle_file_upload_ajax');
add_action('wp_ajax_nme_delete_selected_posts', 'nme_delete_selected_posts');
add_shortcode('netmotors_events_upload_form', 'nme_upload_form');

?>

