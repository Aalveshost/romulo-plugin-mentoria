<?php

if (!defined('ABSPATH')) {
    exit;
}

// 1. Redireciona usuários que não têm a mentoria comprada
add_action('template_redirect', 'plugin_mentoria_access_check');
function plugin_mentoria_access_check() {
    global $post;
    
    // Verifica se o post existe e contém o shortcode [timeline]
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'timeline')) {
        
        // Se for admin ou usuário ID 5, sempre deixa acessar
        if (current_user_can('manage_options') || get_current_user_id() === 5) {
            return;
        }

        // Se não estiver logado ou não tiver mentoria = 1, redireciona
        $mentoria = (int) get_user_meta(get_current_user_id(), 'mentoria', true);
        if ($mentoria !== 1) {
            wp_redirect(home_url());
            exit;
        }
    }
}

// 2. Redireciona checkout vazio para home
add_action('template_redirect', 'plugin_mentoria_redirect_empty_checkout');
function plugin_mentoria_redirect_empty_checkout() {
    if (function_exists('is_checkout') && is_checkout() && WC()->cart && WC()->cart->get_cart_contents_count() === 0) {
        wp_redirect(home_url());
        exit;
    }
}

// 3. Cria campo mentoria vazio ao registrar novo usuário
add_action('user_register', 'plugin_mentoria_create_meta_on_register');
function plugin_mentoria_create_meta_on_register($user_id) {
    add_user_meta($user_id, 'mentoria', '', true);
}

// 4. Garante campo mentoria para usuários existentes
add_action('init', 'plugin_mentoria_ensure_meta_existing');
function plugin_mentoria_ensure_meta_existing() {
    if (!is_user_logged_in()) return;
    $user_id = get_current_user_id();
    if (!metadata_exists('user', $user_id, 'mentoria')) {
        add_user_meta($user_id, 'mentoria', '', true);
    }
}

// 5. Grava mentoria=1 na compra do produto 4448
add_action('woocommerce_order_status_processing', 'plugin_mentoria_record_purchase');
add_action('woocommerce_order_status_completed', 'plugin_mentoria_record_purchase');
function plugin_mentoria_record_purchase($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;
    $user_id = $order->get_user_id();
    if (!$user_id) return;
    foreach ($order->get_items() as $item) {
        if ((int)$item->get_product_id() === 4448) {
            update_user_meta($user_id, 'mentoria', 1);
            break;
        }
    }
}

// 6. AJAX — limpa carrinho e adiciona produto
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

// 7. Expõe status de mentoria no footer e controla visibilidade de elementos UI
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
        var comprastatus = mentoriastatus; // Compatibilidade com o seu código anterior

        document.addEventListener('DOMContentLoaded', function() {
            if (mentoriastatus) {
                var acessarEl = document.getElementById('_acessar');
                if (acessarEl) acessarEl.style.display = 'flex';
                
                var assinarEl = document.getElementById('_assinar');
                if (assinarEl) assinarEl.style.display = 'none';
            } else {
                var assinarEl = document.getElementById('_assinar');
                if (assinarEl) assinarEl.style.display = 'flex';
                
                var acessarEl = document.getElementById('_acessar');
                if (acessarEl) acessarEl.style.display = 'none';
            }
        });
    </script>
    <?php
}

// 8. Exibe campo mentoria no wp-admin
add_action('show_user_profile', 'plugin_mentoria_admin_user_field');
add_action('edit_user_profile', 'plugin_mentoria_admin_user_field');
function plugin_mentoria_admin_user_field($user) {
    $mentoria = get_user_meta($user->ID, 'mentoria', true);
    ?>
    <h3>RM O Meu Mentor</h3>
    <table class="form-table">
        <tr>
            <th><label for="mentoria">Mentoria</label></th>
            <td>
                <input type="text" name="mentoria" id="mentoria" value="<?php echo esc_attr($mentoria); ?>" class="regular-text">
                <p class="description">1 = comprou a mentoria &nbsp;|&nbsp; vazio ou 0 = não comprou</p>
            </td>
        </tr>
    </table>
    <?php
}

// 9. Salva campo mentoria no wp-admin
add_action('personal_options_update', 'plugin_mentoria_save_admin_field');
add_action('edit_user_profile_update', 'plugin_mentoria_save_admin_field');
function plugin_mentoria_save_admin_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) return;
    update_user_meta($user_id, 'mentoria', sanitize_text_field($_POST['mentoria'] ?? ''));
}
