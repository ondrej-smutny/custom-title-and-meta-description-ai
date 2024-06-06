<?php
/*
Plugin Name: Custom Title and Meta Description
Plugin URI: https://www.smuton.cz
Description: Adds "Title and Meta Description" to post editor and character counter pages.
Version: 1.0
Author: Smuton.cz
Author URI: https://www.smuton.cz
*/

function add_combined_custom_meta_box()
{
    add_meta_box('combined_custom_meta', 'Title and Meta Description', 'combined_custom_meta_callback', array('post', 'page'), 'side', 'high');
}
function combined_custom_meta_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'custom_meta_box_nonce');

    $custom_title = get_post_meta($post->ID, '_custom_title', true);
    $custom_meta_description = get_post_meta($post->ID, '_custom_meta_description', true);

    echo '<p><strong>Title</strong></p>';
    echo '<input type="text" id="custom_title" name="custom_title" value="' . esc_attr($custom_title) . '" size="25" style="width: 100%;" />';
    echo '<div id="title_progress_container" style="width: 100%; background-color: #e0e0e0; border-radius: 2px; margin-top: 5px;">';
    echo '<div id="title_progress_bar" style="height: 5px; width: 0%; background-color: #4CAF50; border-radius: 2px;"></div>';
    echo '</div>';
    echo '<div id="title_char_count">0 characters</div>'; 

    echo '<p><strong>Meta Description</strong></p>';
    echo '<textarea id="custom_meta_description" name="custom_meta_description" rows="4" style="width: 100%;">' . esc_textarea($custom_meta_description) . '</textarea>';
    echo '<div id="meta_description_progress_container" style="width: 100%; background-color: #e0e0e0; border-radius: 2px; margin-top: 5px;">';
    echo '<div id="meta_description_progress_bar" style="height: 5px; width: 0%; background-color: #4CAF50; border-radius: 2px;"></div>';
    echo '</div>';
    echo '<div id="meta_description_char_count">0 characters</div>'; 
}

function save_combined_custom_meta_box_data($post_id)
{
    if (array_key_exists('custom_title', $_POST)) {
        update_post_meta($post_id, '_custom_title', sanitize_text_field($_POST['custom_title']));
    }
    if (array_key_exists('custom_meta_description', $_POST)) {
        update_post_meta($post_id, '_custom_meta_description', sanitize_textarea_field($_POST['custom_meta_description']));
    }
    if (!isset($_POST['custom_meta_box_nonce']) || !wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
}

add_filter('pre_get_document_title', 'custom_override_title_tag');
function custom_override_title_tag($title)
{
    if (is_singular()) {
        global $post;
        $custom_title = get_post_meta($post->ID, '_custom_title', true);
        if (!empty($custom_title)) {
            return $custom_title;
        }
    }
    return $title;
}

function custom_meta_description_tag()
{
    if (is_singular()) {
        global $post;
        $custom_meta_description = get_post_meta($post->ID, '_custom_meta_description', true);
        if (!empty($custom_meta_description)) {
            echo '<meta name="description" content="' . esc_attr($custom_meta_description) . '">' . "\n";
        }
    }
}

add_action('wp_head', 'custom_meta_description_tag');

function add_combined_meta_box_scripts()
{
    ?>
        <style type="text/css">
            #title_char_count, #meta_description_char_count {
                margin-bottom: 2em; 
            }
            #title_progress_container, #meta_description_progress_container {
                width: 100%;
                background-color: #e0e0e0;
                border-radius: 2px;
                margin-top: 5px;
            }
            #title_progress_bar, #meta_description_progress_bar {
                height: 5px;
                width: 0%;
                background-color: #4CAF50;
                border-radius: 2px;
            }
        </style>

        <script type="text/javascript">
jQuery(document).ready(function($) {
    function updateCharCount(input, counter, limit, warningLimit, progressBar) {
        var count = $(input).val().length;
        var percentage = Math.min((count / limit) * 100, 100); 
        var color = 'black';
        var barColor = '#4CAF50';

        if (count > limit) {
            color = 'red';
            barColor = 'red';
        } else if (count > warningLimit) {
            color = 'orange';
            barColor = 'orange';
        }

        $(counter).text(count + ' characters').css('color', color);
        $(progressBar).css('width', percentage + '%').css('background-color', barColor);
    }

    $('#custom_title').keyup(function() {
        updateCharCount(this, '#title_char_count', 60, 50, '#title_progress_bar');
    }).keyup();

    $('#custom_meta_description').keyup(function() {
        updateCharCount(this, '#meta_description_char_count', 160, 120, '#meta_description_progress_bar');
    }).keyup();
});
</script>
    <?php
}

add_action('add_meta_boxes', 'add_combined_custom_meta_box');
add_action('save_post', 'save_combined_custom_meta_box_data');
add_action('admin_footer-posts.php', 'add_combined_meta_box_scripts'); 
add_action('admin_footer-post.php', 'add_combined_meta_box_scripts');
