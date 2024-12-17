jQuery(document).ready(function($) {
    function showNotice(message, success) {
        var noticeClass = success ? 'notice-success' : 'notice-error';
        var noticeHtml = '<div class="notice ' + noticeClass + ' is-dismissible">';
        noticeHtml += '<p>' + message + '</p>';
        noticeHtml += '</div>';
        
        $('#upload-message').html(noticeHtml);

        // Automatically dismiss the notice after a few seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    $('#file-upload-form').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('action', 'nme_handle_file_upload_ajax');
        formData.append('security', nme_ajax_object.nme_nonce);

        $.ajax({
            url: nme_ajax_object.ajax_url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                showNotice(response.data.message, response.success);
                if (response.success) {
                    $('#uploaded-data-table').html(response.data.updated_table);
                }
            },
            error: function(xhr, status, error) {
                showNotice('AJAX error: ' + error, false);
            }
        });
    });

    $('#select-all-posts').change(function() {
        $('input[name="selected_posts[]"]').prop('checked', $(this).prop('checked'));
    });

    $('#delete-posts-form').submit(function(e) {
        e.preventDefault();
        var selectedPosts = $('input[name="selected_posts[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedPosts.length > 0) {
            var confirmDelete = confirm('Are you sure you want to delete the selected posts?');
            if (confirmDelete) {
                $.ajax({
                    url: nme_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'nme_delete_selected_posts',
                        selected_posts: selectedPosts,
                        security: nme_ajax_object.nme_nonce
                    },
                    success: function(response) {
                        showNotice('Selected posts deleted successfully.', true);
                        location.reload(); // Reload the page after deletion
                    },
                    error: function(xhr, status, error) {
                        showNotice('Error deleting selected posts: ' + error, false);
                    }
                });
            }
        } else {
            alert('Please select at least one post to delete.');
        }
    });
});
