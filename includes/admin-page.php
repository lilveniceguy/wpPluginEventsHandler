<?php

function nme_register_menu_page() {
    add_menu_page(
        'Events Users DB Manager',
        'Events Users DB Admin',
        'manage_options',
        'nme-db-manager',
        'nme_display_admin_page',
        'dashicons-upload',
        6
    );
}

function nme_display_admin_page() {
    ?>
    <div class="wrap">
        <h1>Netmotors Events Plugin</h1>
        <div class="postbox">
            <div class="inside">
                En esta pantalla se cargan las bases de datos de ruts para posteriormente ser asociadas en las paginas de los eventos<br/>
                <a href="<?php echo admin_url('edit.php?post_type=eventos'); ?>">Asociar bases de datos a eventos</a>
            </div>
        </div>
        <?php echo do_shortcode('[netmotors_events_upload_form]'); ?>
        <h2>Bases de datos cargadas:</h2>
        <div id="uploaded-data-table">
            <?php nme_display_uploaded_data(); ?>
        </div>
    </div>
    <?php
}

function nme_display_uploaded_data() {
    $uploaded_data = get_posts(array(
        'post_type' => 'events-users-db',
        'posts_per_page' => -1
    ));

    echo '<form id="delete-posts-form" method="post">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox" id="select-all-posts"></th>';
    echo '<th scope="col" id="id" class="manage-column column-id">ID</th>';
    echo '<th scope="col" id="upload_identifier" class="manage-column column-upload_identifier">Upload Identifier <pre style="font-size:8px">(Click para editar RUTs)</pre></th>';
    echo '<th scope="col" id="uploaded_data" class="manage-column column-uploaded_data">Uploaded Data</th>';
    echo '<th scope="col" id="associated_eventos" class="manage-column column-associated_eventos">Eventos asociados</th>'; // New column
    echo '</tr>';
    echo '</thead>';

    echo '<tbody id="the-list">';
    if (!empty($uploaded_data)) {
        foreach ($uploaded_data as $data) {
            $post_edit_link = get_edit_post_link($data->ID);
            $post_content_trimmed = wp_trim_words($data->post_content, 20, '...');

            // Get associated "eventos" posts
            $associated_eventos = get_posts(array(
                'post_type' => array('eventos','page'),
                'meta_query' => array(
                    array(
                        'key' => 'associated_posts',
                        'value' => '"' . $data->ID . '"',
                        'compare' => 'LIKE'
                    )
                )
            ));

            echo '<tr>';
            echo '<td><input type="checkbox" name="selected_posts[]" value="' . esc_attr($data->ID) . '"></td>';
            echo '<td>' . esc_html($data->ID) . '</td>';
            echo '<td><a href="' . esc_url($post_edit_link) . '">' . esc_html($data->post_title) . '</a></td>';
            echo '<td>' . esc_html($post_content_trimmed) . '</td>';

            // Display associated "eventos" post titles
            echo '<td>';
            if (!empty($associated_eventos)) {
                foreach ($associated_eventos as $evento) {
                    $evento_edit_link = get_edit_post_link($evento->ID);
                    echo '<a href="' . esc_url($evento_edit_link) . '">' . esc_html($evento->post_title) . '</a><br>';
                }
            } else {
                echo '0 eventos asociados<br/><a href="'.admin_url("edit.php?post_type=eventos").'">Ver eventos disponibles</a>';
            }
            echo '</td>';

            echo '</tr>';
        }
    } else {
        echo '<tr class="no-items">';
        echo '<td class="colspanchange" colspan="5">No data found.</td>';
        echo '</tr>';
    }
    echo '</tbody>';

    echo '<tfoot>';
    echo '<tr>';
    echo '<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" id="select-all-posts"></th>';
    echo '<th scope="col" id="id" class="manage-column column-id">ID</th>';
    echo '<th scope="col" id="upload_identifier" class="manage-column column-upload_identifier">Upload Identifier</th>';
    echo '<th scope="col" id="uploaded_data" class="manage-column column-uploaded_data">Uploaded Data</th>';
    echo '<th scope="col" id="associated_eventos" class="manage-column column-associated_eventos">Associated Eventos</th>'; // New column
    echo '</tr>';
    echo '</tfoot>';

    echo '</table>';
    echo '<button type="submit" class="button button-primary" id="delete-selected-posts">Delete Selected Posts</button>';
    echo '</form>';
}


?>
