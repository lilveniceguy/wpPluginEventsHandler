<?php

function nme_handle_file_upload_ajax() {
    check_ajax_referer('nme_nonce', 'security');

    if (isset($_FILES['upload_file'])) {
        $uploaded_file = $_FILES['upload_file'];
        $file_extension = pathinfo($uploaded_file['name'], PATHINFO_EXTENSION);

        $file_content = '';

        if ($file_extension === 'txt' || $file_extension === 'csv') {
            $file_content = file_get_contents($uploaded_file['tmp_name']);
        } elseif ($file_extension === 'xlsx') {
            require_once plugin_dir_path(__FILE__) . '../libs/PhpSpreadsheet/vendor/autoload.php';

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploaded_file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            foreach ($sheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $file_content .= $cell->getValue() . ' ';
                }
                $file_content .= "\n";
            }
        } else {
            wp_send_json_error(array('message' => 'Unsupported file format. Only .txt, .csv, and .xlsx files are allowed.'));
            return;
        }

        if (!empty($file_content)) {
            $upload_identifier = basename($uploaded_file['name']);
            $post_data = array(
                'post_title' => wp_strip_all_tags($upload_identifier),
                'post_content' => sanitize_textarea_field($file_content),
                'post_type' => 'events-users-db',
                'post_status' => 'publish'
            );

            $post_id = wp_insert_post($post_data);

            if ($post_id) {
                if (file_exists($uploaded_file['tmp_name'])) {
                    unlink($uploaded_file['tmp_name']);
                }
                ob_start();
                nme_display_uploaded_data();
                $updated_table = ob_get_clean();
                wp_send_json_success(array(
                    'message' => 'File uploaded successfully.',
                    'updated_table' => $updated_table
                ));
            } else {
                wp_send_json_error(array('message' => 'Failed to create post.'));
            }
        } else {
            wp_send_json_error(array('message' => 'No content found in the uploaded file.'));
        }
    } else {
        wp_send_json_error(array('message' => 'No file uploaded.'));
    }
    wp_die();
}

function nme_delete_selected_posts() {
    if (isset($_POST['selected_posts'])) {
        $selected_posts = $_POST['selected_posts'];
        foreach ($selected_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        wp_send_json_success('Selected posts deleted successfully.');
    } else {
        wp_send_json_error('No posts selected for deletion.');
    }
    wp_die();
}

?>
