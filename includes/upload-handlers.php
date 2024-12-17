<?php

function nme_upload_form() {
    ob_start();
    ?>
    <form id="file-upload-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('nme_nonce', 'nme_nonce_field'); ?>
        <input type="file" name="upload_file" id="upload_file" accept=".txt, .csv, .xlsx" />
        <button type="submit" class="button button-primary">Upload File</button>
    </form>
    <div id="upload-message"></div>
    <?php
    return ob_get_clean();
}

?>
