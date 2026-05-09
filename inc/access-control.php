<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. Controle de Acesso: Redireciona usuários que não têm a mentoria comprada
 * O campo 'mentoria' é gerenciado externamente pelo site.
 */
add_action('template_redirect', 'plugin_mentoria_access_check');
function plugin_mentoria_access_check() {
    global $post;
    
    // Verifica se o post existe e contém o shortcode [timeline]
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'timeline')) {
        
        // Administradores e Usuário ID 5 têm acesso liberado sempre
        if (current_user_can('manage_options') || get_current_user_id() === 5) {
            return;
        }

        // Verifica o campo de mentoria (gerenciado externamente)
        $mentoria = (int) get_user_meta(get_current_user_id(), 'mentoria', true);
        if ($mentoria !== 1) {
            wp_redirect(home_url());
            exit;
        }
    }
}

/**
 * 2. Expõe status de mentoria no footer e controla visibilidade de elementos UI (_acessar / _assinar)
 */
add_action('wp_footer', 'plugin_mentoria_expose_status');
function plugin_mentoria_expose_status() {
    $mentoria = 0;
    if (is_user_logged_in()) {
        $mentoria = (int) get_user_meta(get_current_user_id(), 'mentoria', true);
    }
    
    $js_flag = ($mentoria === 1 ? 'true' : 'false');
    ?>
    <script>
        var mentoriastatus = <?php echo $js_flag; ?>;
        var comprastatus = mentoriastatus;

        document.addEventListener('DOMContentLoaded', function() {
            var acessarEl = document.getElementById('_acessar');
            var assinarEl = document.getElementById('_assinar');

            if (mentoriastatus) {
                if (acessarEl) acessarEl.style.display = 'flex';
                if (assinarEl) assinarEl.style.display = 'none';
            } else {
                if (assinarEl) assinarEl.style.display = 'flex';
                if (acessarEl) acessarEl.style.display = 'none';
            }
        });
    </script>
    <?php
}

/**
 * 3. AJAX — Auxiliar para adicionar produto ao carrinho (opcional, mantido se precisar dos botões de compra)
 */
add_action('wp_ajax_rm_set_produto', 'plugin_mentoria_ajax_set_product');
add_action('wp_ajax_nopriv_rm_set_produto', 'plugin_mentoria_ajax_set_product');
function plugin_mentoria_ajax_set_product() {
    $product_id = intval($_GET['product_id'] ?? 0);
    $allowed = [554, 4448];
    if (!in_array($product_id, $allowed)) {
        wp_send_json_error('Produto inválido.');
    }
    if (function_exists('WC')) {
        WC()->cart->empty_cart();
        WC()->cart->add_to_cart($product_id);
        wp_send_json_success(wc_get_checkout_url());
    } else {
        wp_send_json_error('WooCommerce não está ativo.');
    }
}
