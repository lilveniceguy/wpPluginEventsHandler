<?php

function nme_eventos_associated_posts_meta_box() {
    // Add metabox for 'eventos' custom post type
    add_meta_box(
        'nme_eventos_associated_posts',
        'Bases de datos asociadas',
        'nme_render_eventos_associated_posts_meta_box',
        'eventos',
        'side',
        'default'
    );
    
    // Add metabox for 'page' post type
    add_meta_box(
        'nme_eventos_associated_posts_page',
        'Bases de datos asociadas',
        'nme_render_eventos_associated_posts_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'nme_eventos_associated_posts_meta_box');

//add_action('add_meta_boxes_eventos', 'nme_eventos_associated_posts_meta_box');

function nme_render_eventos_associated_posts_meta_box($post) {
    $associated_posts = get_post_meta($post->ID, 'associated_posts', true);
    if (!is_array($associated_posts)) {
        $associated_posts = array($associated_posts);
    }

    $events_users_posts = get_posts(array(
        'post_type' => 'events-users-db',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    echo '<table class="wp-list-table widefat">';
    echo '<thead><tr><th>ID</th><th>Title</th><th>Associate</th></tr></thead>';
    echo '<tbody>';
    if ($events_users_posts) {
        foreach ($events_users_posts as $event_users_post) {
            $checked = in_array($event_users_post->ID, $associated_posts) ? 'checked' : '';
            echo '<tr>';
            echo '<td>' . esc_html($event_users_post->ID) . '</td>';
            echo '<td>' . esc_html($event_users_post->post_title) . '</td>';
            echo '<td><input type="checkbox" name="associated_posts[]" value="' . esc_attr($event_users_post->ID) . '" ' . $checked . '></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">No posts found</td></tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

function nme_save_eventos_associated_posts($post_id) {
    if (isset($_POST['associated_posts'])) {
        $associated_posts = $_POST['associated_posts'];
        update_post_meta($post_id, 'associated_posts', $associated_posts);
    }
}
add_action('save_post', 'nme_save_eventos_associated_posts');

function nme_eventos_add_meta_boxes() {
    // Metaboxes for 'eventos' custom post type
    add_meta_box(
        'nme_region_meta_box',
        'Select Region',
        'nme_render_region_meta_box',
        'eventos',
        'side',
        'default'
    );

    add_meta_box(
        'nme_comuna_meta_box',
        'Select Comuna',
        'nme_render_comuna_meta_box',
        'eventos',
        'side',
        'default'
    );

    // Metaboxes for 'page' post type
    add_meta_box(
        'nme_region_meta_box_page',
        'Select Region',
        'nme_render_region_meta_box',
        'page',
        'side',
        'default'
    );

    add_meta_box(
        'nme_comuna_meta_box_page',
        'Select Comuna',
        'nme_render_comuna_meta_box',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'nme_eventos_add_meta_boxes');

function nme_render_region_meta_box($post) {
    wp_nonce_field('nme_save_meta_boxes', 'nme_meta_boxes_nonce');

    $selected_region = get_post_meta($post->ID, 'nme_region', true);
    $regiones_comunas_data = file_get_contents(plugin_dir_url(__FILE__) . '../js/comunas-regiones.json');
    $regiones_comunas = json_decode($regiones_comunas_data, true);

    echo '<select id="nme_region" name="nme_region">';
    echo '<option value="">Select Region</option>';
    foreach ($regiones_comunas['regiones'] as $region) {
        $selected = ($selected_region == $region['region']) ? 'selected="selected"' : '';
        echo '<option value="' . esc_attr($region['region']) . '" ' . $selected . '>' . esc_html($region['region']) . '</option>';
    }
    echo '</select>';

    // JavaScript to handle region change event
    echo '<script>
        document.getElementById("nme_region").addEventListener("change", function() {
            var region = this.value;
            var comunas = ' . json_encode($regiones_comunas['regiones']) . ';
            var comunaSelect = document.getElementById("nme_comuna");
            comunaSelect.innerHTML = "<option value=\'\'>Select Comuna</option>";
            for (var i = 0; i < comunas.length; i++) {
                if (comunas[i].region === region) {
                    comunas[i].comunas.forEach(function(comuna) {
                        var option = document.createElement("option");
                        option.value = comuna;
                        option.text = comuna;
                        comunaSelect.appendChild(option);
                    });
                    break;
                }
            }
        });
    </script>';
}


function nme_render_comuna_meta_box($post) {
    $selected_comuna = get_post_meta($post->ID, 'nme_comuna', true);
    echo '<select id="nme_comuna" name="nme_comuna">';
    echo '<option value="">Select Comuna</option>';
    if (!empty($selected_comuna)) {
        echo '<option value="' . esc_attr($selected_comuna) . '" selected="selected">' . esc_html($selected_comuna) . '</option>';
    }
    echo '</select>';
}



function nme_save_meta_boxes($post_id) {
    if (!isset($_POST['nme_meta_boxes_nonce']) || !wp_verify_nonce($_POST['nme_meta_boxes_nonce'], 'nme_save_meta_boxes')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['post_type'])) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    if (isset($_POST['nme_region'])) {
        update_post_meta($post_id, 'nme_region', sanitize_text_field($_POST['nme_region']));
    }

    if (isset($_POST['nme_comuna'])) {
        update_post_meta($post_id, 'nme_comuna', sanitize_text_field($_POST['nme_comuna']));
    }
}
add_action('save_post', 'nme_save_meta_boxes');

function display_metaboxes_in_elementor_editor() {
    global $post;
    
    // Check if the current post type is 'page' or 'eventos'
    if ($post->post_type === 'page' || $post->post_type === 'eventos') {
        // Output the metabox HTML
        nme_render_eventos_associated_posts_meta_box($post);
        nme_render_region_meta_box($post);
        nme_render_comuna_meta_box($post);
        // Output other metaboxes if needed
    }
}
add_action('elementor/frontend/after_content', 'display_metaboxes_in_elementor_editor');



?>
