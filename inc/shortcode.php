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
                                        <iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" frameborder="0" allowfullscreen></iframe>
                                    </div>
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

        <?php if (current_user_can('manage_options')) : ?>
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
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function plugin_mentoria_get_youtube_id($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? $match[1] : false;
}
