jQuery(document).ready(function($) {
    // Логируем загрузку скрипта
    console.log('bed-admin-script loaded');

    // Ищем кнопку по возможным ID
    var $syncButton = $('#bed-sync-button, #sync-github, .bed-sync-github');
    if ($syncButton.length === 0) {
        console.error('Sync button not found. Expected ID: bed-sync-button, sync-github, or class: bed-sync-github');
        return;
    }

    // Привязываем обработчик к кнопке синхронизации
    $syncButton.on('click', function(e) {
        e.preventDefault();
        console.log('Sync button clicked');

        // Проверяем, определена ли переменная bedAjax
        if (typeof bedAjax === 'undefined' || !bedAjax.ajaxurl || !bedAjax.nonce) {
            console.error('bedAjax is not defined or missing ajaxurl/nonce');
            alert('Ошибка: Не удалось выполнить синхронизацию. Переменная bedAjax не определена.');
            return;
        }

        // Отключаем кнопку, чтобы предотвратить множественные клики
        var $button = $(this);
        $button.prop('disabled', true).text('Синхронизация...');

        // Отправляем AJAX-запрос
        $.ajax({
            url: bedAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'bed_manual_sync',
                nonce: bedAjax.nonce
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    alert('Синхронизация успешно завершена: ' + response.data.message);
                } else {
                    console.error('Sync failed:', response.data.message);
                    alert('Ошибка синхронизации: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error, xhr.responseText);
                alert('Ошибка AJAX-запроса: ' + error);
            },
            complete: function() {
                // Восстанавливаем кнопку
                $button.prop('disabled', false).text('Синхронизировать с GitHub');
            }
        });
    });
});