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

    // 全エラーチェックボタン
    $('#plm-check-all-errors').on('click', function() {
        if (!confirm('全ての公開済み投稿の楽天ブロックをチェックします。\n投稿数によっては時間がかかる場合があります。\n実行しますか？')) {
            return;
        }
        
        var button = $(this);
        var progressDiv = $('#plm-check-progress');
        var progressBar = $('#plm-progress-bar');
        var progressPercent = $('#plm-progress-percent');
        var progressText = $('#plm-progress-text');
        var progressDetail = $('#plm-progress-detail');
        var errorFound = $('#plm-error-found');
        var blocksFound = $('#plm-blocks-found');
        var messageDiv = $('#plm-error-log-message');
        
        // 初期化
        button.prop('disabled', true);
        progressDiv.show();
        messageDiv.html('');
        
        var offset = 0;
        var limit = 5;
        var totalErrors = 0;
        var totalBlocks = 0;
        var totalCount = 0;
        
        function checkBatch() {
            progressText.text('チェック中...');
            
            $.ajax({
                url: '<?php echo rest_url('product-link-maker/v1/check-all-errors'); ?>',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                },
                data: {
                    offset: offset,
                    limit: limit
                },
                success: function(response) {
                    totalErrors += response.error_count;
                    totalBlocks += response.blocks_found;
                    totalCount = response.total_count;
                    
                    var percent = Math.round((response.processed / totalCount) * 100);
                    progressBar.css('width', percent + '%');
                    progressPercent.text(percent + '%');
                    progressDetail.text('処理中: ' + response.processed + ' / ' + totalCount + ' 投稿');
                    errorFound.text(totalErrors);
                    blocksFound.text(totalBlocks);
                    
                    if (response.has_more) {
                        offset = response.next_offset;
                        setTimeout(checkBatch, 300); // 300ms待機してから次のバッチ
                    } else {
                        // 完了
                        progressText.html('<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span> チェック完了');
                        button.prop('disabled', false);
                        
                        var resultMessage = '<div class="notice notice-success inline"><p>';
                        resultMessage += '<strong>チェック完了！</strong><br>';
                        resultMessage += '処理した投稿: ' + totalCount + '件<br>';
                        resultMessage += '楽天ブロック: ' + totalBlocks + '個<br>';
                        resultMessage += '発見したエラー: <strong style="color: #d63638;">' + totalErrors + '</strong>件';
                        resultMessage += '</p></div>';
                        
                        messageDiv.html(resultMessage);
                        
                        // 3秒後にページをリロード
                        setTimeout(function() {
                            location.reload();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    progressText.html('<span class="dashicons dashicons-warning" style="color: #d63638;"></span> エラーが発生しました');
                    messageDiv.html('<div class="notice notice-error inline"><p>チェック中にエラーが発生しました。</p></div>');
                    button.prop('disabled', false);
                    console.error('Error checking posts:', status, error);
                }
            });
        }
        
        // 最初のバッチを開始
        checkBatch();
    });
});
</script>
