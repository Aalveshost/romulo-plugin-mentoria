<?php
/**
 * Plugin Name: Romulo - Plugin Pag Mentoria
 * Plugin URI: https://aalves.dev
 * Description: Plugin para exibição de timeline interativa com vídeos do YouTube.
 * Version: 1.0.0
 * Author: Antigravity (Google Deepmind) & Aalves.dev
 * Author URI: https://aalves.dev
 * Text Domain: plugin-mentoria
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('PLUGIN_MENTORIA_PATH', plugin_dir_path(__FILE__));
define('PLUGIN_MENTORIA_URL', plugin_dir_url(__FILE__));

// Include files
require_once PLUGIN_MENTORIA_PATH . 'inc/shortcode.php';
require_once PLUGIN_MENTORIA_PATH . 'inc/admin-ajax.php';

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', 'plugin_mentoria_enqueue_assets');
function plugin_mentoria_enqueue_assets() {
    wp_enqueue_style('plugin-mentoria-style', PLUGIN_MENTORIA_URL . 'assets/css/style.css', array(), '1.0.0');
    wp_enqueue_script('plugin-mentoria-script', PLUGIN_MENTORIA_URL . 'assets/js/script.js', array('jquery'), '1.0.0', true);

    wp_localize_script('plugin-mentoria-script', 'pluginMentoria', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('plugin_mentoria_nonce'),
        'can_edit' => current_user_can('manage_options')
    ));
}
