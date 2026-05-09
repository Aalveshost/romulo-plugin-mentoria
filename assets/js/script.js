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
                description: $(this).find('textarea[name="description"]').val()
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
                            $itemsList.append(createItemForm(item.youtube_link, item.title, item.description, index));
                        });
                    } else {
                        $itemsList.append(createItemForm('', '', '', 0));
                    }
                }
            }
        });
    }

    function updateItemNumbers() {
        $('.timeline-item-form').each(function(index) {
            $(this).find('.item-number-badge').text(index + 1);
        });
    }

    function createItemForm(link, title, desc, index) {
        return `
            <div class="timeline-item-form">
                <div class="item-number-badge">${index + 1}</div>
                <button type="button" class="remove-item">Excluir</button>
                <div class="form-group" style="padding-left: 45px;">
                    <label>Link YouTube</label>
                    <input type="text" name="youtube_link" value="${link}" placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div class="form-group" style="padding-left: 45px;">
                    <label>Título</label>
                    <input type="text" name="title" value="${title}" placeholder="Título do Passo">
                </div>
                <div class="form-group" style="padding-left: 45px;">
                    <label>Descrição</label>
                    <textarea name="description" rows="3" placeholder="Descrição curta sobre este processo">${desc}</textarea>
                </div>
            </div>
        `;
    }
});
