<?php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('timeline', 'plugin_mentoria_shortcode');

function plugin_mentoria_shortcode($atts) {
    $data = get_option('plugin_mentoria_data', array());
    
    ob_start();
    ?>
    <div class="mentoria-timeline-container">
        <div class="mentoria-timeline">
            <?php if (!empty($data)) : ?>
                <?php foreach ($data as $index => $item) : 
                    $side = ($index % 2 === 0) ? 'left' : 'right';
                    $youtube_id = plugin_mentoria_get_youtube_id($item['youtube_link']);
                    ?>
                    <div class="timeline-item <?php echo $side; ?>" data-index="<?php echo $index; ?>">
                        <div class="timeline-dot">
                            <span><?php echo $index + 1; ?></span>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-text">
                                <h3 class="timeline-title"><?php echo esc_html($item['title']); ?></h3>
                                <p class="timeline-description"><?php echo nl2br(esc_html($item['description'])); ?></p>
                            </div>
                            <div class="timeline-video">
                                <?php if ($youtube_id) : ?>
                                    <div class="video-wrapper">
                                        <div class="video-protection-layer"></div>
                                        <iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>?modestbranding=1&rel=0&iv_load_policy=3&showinfo=0" frameborder="0" allowfullscreen></iframe>
                                    </div>
                                    <?php if (!empty($item['file_url'])) : ?>
                                        <a href="<?php echo esc_url($item['file_url']); ?>" class="timeline-download-btn" target="_blank">
                                            <svg aria-hidden="true" class="e-font-icon-svg e-fas-chevron-right" viewBox="0 0 320 512" xmlns="http://www.w3.org/2000/svg" width="12" height="12" style="fill: white;"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
                                            <span><?php echo !empty($item['file_title']) ? esc_html($item['file_title']) : 'Baixar Material'; ?></span>
                                        </a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <div class="video-placeholder">Vídeo indisponível</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="timeline-empty">
                    <?php if (current_user_can('manage_options')) : ?>
                        <p>Nenhum item na timeline. Clique no ícone de edição para adicionar.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    <?php
    return ob_get_clean();
}

add_action('wp_footer', 'plugin_mentoria_render_editor');
function plugin_mentoria_render_editor() {
    if (!current_user_can('manage_options') && get_current_user_id() !== 5) return;
    
    global $post;
    if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'timeline')) return;
    
    ?>
    <button id="mentoria-edit-trigger" title="Editar Timeline">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
    </button>

    <!-- Editor Popup -->
    <div id="mentoria-editor-popup" class="mentoria-popup-overlay">
        <div class="mentoria-popup-content">
            <div class="popup-header">
                <h2>Gerenciar Timeline</h2>
                <button class="close-popup">&times;</button>
            </div>
            <div class="popup-body">
                <div id="mentoria-toast" class="mentoria-notification"></div>
                <div id="timeline-items-list">
                    <!-- Items will be loaded here -->
                </div>
                <button type="button" id="add-timeline-item" class="btn-secondary">+ Adicionar Passo</button>
            </div>
            <div class="popup-footer">
                <button type="button" id="save-timeline-data" class="btn-primary">Salvar Alterações</button>
            </div>
        </div>
    </div>
    <?php
}

function plugin_mentoria_get_youtube_id($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? $match[1] : false;
}
