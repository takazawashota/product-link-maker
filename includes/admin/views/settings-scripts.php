<?php
/**
 * 設定ページ用JavaScript
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<script>
jQuery(document).ready(function($) {
    // キャッシュクリアボタン
    $('#plm-clear-cache').on('click', function() {
        var button = $(this);
        var messageDiv = $('#plm-cache-message');
        
        button.prop('disabled', true).text('削除中...');
        messageDiv.html('');
        
        $.ajax({
            url: '<?php echo rest_url('product-link-maker/v1/clear-cache'); ?>',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
            },
            success: function(response) {
                messageDiv.html('<div class="notice notice-success inline"><p>' + response.message + '</p></div>');
                button.prop('disabled', false).text('すべてのキャッシュを削除');
                // キャッシュ状況を更新するためにページをリロード
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function() {
                messageDiv.html('<div class="notice notice-error inline"><p>キャッシュの削除に失敗しました。</p></div>');
                button.prop('disabled', false).text('すべてのキャッシュを削除');
            }
        });
    });

    // エラーログクリアボタン
    $('#plm-clear-error-logs').on('click', function() {
        if (!confirm('すべてのエラーログを削除しますか？')) {
            return;
        }
        
        var button = $(this);
        var messageDiv = $('#plm-error-log-message');
        
        button.prop('disabled', true).text('削除中...');
        messageDiv.html('');
        
        $.ajax({
            url: '<?php echo rest_url('product-link-maker/v1/clear-error-logs'); ?>',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
            },
            success: function(response) {
                messageDiv.html('<div class="notice notice-success inline"><p>' + response.message + '</p></div>');
                button.prop('disabled', false).text('エラーログをクリア');
                // エラーログセクションを更新するためにページをリロード
                setTimeout(function() {
                    location.reload();
                }, 1000);
            },
            error: function() {
                messageDiv.html('<div class="notice notice-error inline"><p>エラーログのクリアに失敗しました。</p></div>');
                button.prop('disabled', false).text('エラーログをクリア');
            }
        });
    });

    // エラーキャッシュクリアボタン
    $('#plm-clear-error-cache').on('click', function() {
        if (!confirm('エラーに関する全てのキャッシュをクリアしますか？\nこれにより、エラーが再度API経由で確認され、ログに記録されます。')) {
            return;
        }
        
        console.log('[PLM Clear] Clearing error cache');
        var button = $(this);
        var messageDiv = $('#plm-error-log-message');
        
        button.prop('disabled', true).text('削除中...');
        messageDiv.html('');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            method: 'POST',
            data: {
                action: 'plm_clear_error_cache',
                nonce: '<?php echo wp_create_nonce('plm_clear_error_cache'); ?>'
            },
            success: function(response) {
                console.log('[PLM Clear] Response:', response);
                
                var messageClass = response.success ? 'notice-success' : 'notice-error';
                var message = response.data || response.message || 'エラーキャッシュをクリアしました';
                
                messageDiv.html('<div class="notice ' + messageClass + ' inline"><p>' + message + '</p></div>');
                button.prop('disabled', false).text('エラーキャッシュをクリア');
                
                if (response.success) {
                    setTimeout(function() {
                        messageDiv.html('<div class="notice notice-info inline"><p>キャッシュをクリアしました。エディタでエラーブロックを再読み込みしてください。</p></div>');
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('[PLM Clear] Error:', status, error);
                messageDiv.html('<div class="notice notice-error inline"><p>クリアに失敗しました。</p></div>');
                button.prop('disabled', false).text('エラーキャッシュをクリア');
            }
        });
    });
});
</script>
