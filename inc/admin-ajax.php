<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_save_timeline_data', 'plugin_mentoria_save_data');

function plugin_mentoria_save_data() {
    check_ajax_referer('plugin_mentoria_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Sem permissão.');
    }

    $items = isset($_POST['items']) ? $_POST['items'] : array();
    
    // Sanitize data
    $sanitized_items = array();
    if (is_array($items)) {
        foreach ($items as $item) {
            if (empty($item['title']) && empty($item['youtube_link'])) continue;
            
            $sanitized_items[] = array(
                'youtube_link' => esc_url_raw($item['youtube_link']),
                'title'        => sanitize_text_field($item['title']),
                'description'  => sanitize_textarea_field($item['description']),
            );
        }
    }

    update_option('plugin_mentoria_data', $sanitized_items);
    wp_send_json_success('Dados salvos com sucesso!');
}

add_action('wp_ajax_get_timeline_data', 'plugin_mentoria_get_data');

function plugin_mentoria_get_data() {
    check_ajax_referer('plugin_mentoria_nonce', 'nonce');
    
    $data = get_option('plugin_mentoria_data', array());
    wp_send_json_success($data);
}
