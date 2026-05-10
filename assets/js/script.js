jQuery(document).ready(function($) {
    const $popup = $('#mentoria-editor-popup');
    const $itemsList = $('#timeline-items-list');

    // Open Popup
    $('#mentoria-edit-trigger').on('click', function() {
        loadTimelineData();
        $popup.css('display', 'flex').hide().fadeIn(300);
    });

    // Close Popup
    $('.close-popup').on('click', function() {
        $popup.fadeOut(300);
    });

    $(window).on('click', function(e) {
        if ($(e.target).is($popup)) {
            $popup.fadeOut(300);
        }
    });

    // Add Item
    $('#add-timeline-item').on('click', function() {
        const nextIndex = $('.timeline-item-form').length;
        const itemHtml = createItemForm('', '', '', nextIndex);
        $itemsList.append(itemHtml);
        showToast('Novo passo adicionado!');
    });

    // Remove Item
    $(document).on('click', '.remove-item', function() {
        if (confirm('Deseja realmente excluir este passo?')) {
            $(this).closest('.timeline-item-form').fadeOut(300, function() {
                $(this).remove();
                updateItemNumbers();
                showToast('Passo removido.');
            });
        }
    });

    // Save Data
    $('#save-timeline-data').on('click', function() {
        const items = [];
        const $btn = $(this);
        
        $btn.prop('disabled', true).text('Salvando...');

        $('.timeline-item-form').each(function() {
            items.push({
                youtube_link: $(this).find('input[name="youtube_link"]').val(),
                title: $(this).find('input[name="title"]').val(),
                description: $(this).find('textarea[name="description"]').val(),
                file_url: $(this).find('input[name="file_url"]').val(),
                file_title: $(this).find('input[name="file_title"]').val()
            });
        });

        $.ajax({
            url: pluginMentoria.ajax_url,
            type: 'POST',
            data: {
                action: 'save_timeline_data',
                nonce: pluginMentoria.nonce,
                items: items
            },
            success: function(response) {
                if (response.success) {
                    showToast('Alterações salvas com sucesso!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Erro ao salvar: ' + response.data);
                }
            },
            complete: function() {
                $btn.prop('disabled', false).text('Salvar Alterações');
            }
        });
    });

    function showToast(message) {
        const $toast = $('#mentoria-toast');
        $toast.text(message).fadeIn(300);
        setTimeout(() => $toast.fadeOut(300), 3000);
    }

    function loadTimelineData() {
        $itemsList.html('<p style="text-align:center">Carregando...</p>');
        
        $.ajax({
            url: pluginMentoria.ajax_url,
            type: 'POST',
            data: {
                action: 'get_timeline_data',
                nonce: pluginMentoria.nonce
            },
            success: function(response) {
                if (response.success) {
                    $itemsList.empty();
                    const data = response.data;
                    if (data && data.length > 0) {
                        data.forEach(function(item, index) {
                            $itemsList.append(createItemForm(index, item));
                        });
                    } else {
                        $itemsList.append(createItemForm(0));
                    }
                }
            }
        });
    }

    function updateItemNumbers() {
        $('.timeline-item-form').each(function(index) {
            $(this).find('.item-number-circle').text(index + 1);
        });
    }

    function createItemForm(index, data = {}) {
        const itemHtml = `
            <div class="timeline-item-form" data-index="${index}">
                <div class="item-number-circle">${index + 1}</div>
                <button type="button" class="remove-item">Excluir</button>
                
                <div class="form-group">
                    <label>Link YouTube</label>
                    <input type="text" name="youtube_link" value="${data.youtube_link || ''}" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                
                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="title" value="${data.title || ''}" placeholder="Ex: Passo 1 - Introdução">
                </div>
                
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="description" placeholder="Descreva brevemente este passo...">${data.description || ''}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group half">
                        <label>Título do Botão de Download</label>
                        <input type="text" name="file_title" value="${data.file_title || ''}" placeholder="Ex: Baixar PDF">
                    </div>
                    <div class="form-group half">
                        <label>Arquivo de Apoio</label>
                        <div class="file-upload-wrapper">
                            <input type="text" name="file_url" value="${data.file_url || ''}" placeholder="Link do arquivo...">
                            <button type="button" class="select-file-btn btn-secondary">Selecionar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        return itemHtml;
    }

    // Media Library Logic
    $(document).on('click', '.select-file-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const wrapper = btn.closest('.file-upload-wrapper');
        const input = wrapper.find('input[name="file_url"]');

        const fileFrame = wp.media({
            title: 'Selecionar Arquivo de Apoio',
            button: { text: 'Usar este arquivo' },
            multiple: false
        });

        fileFrame.on('select', function() {
            const attachment = fileFrame.state().get('selection').first().toJSON();
            input.val(attachment.url);
        });

        fileFrame.open();
    });
});
