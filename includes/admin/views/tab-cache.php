<?php
/**
 * キャッシュ設定タブ
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
// キャッシュ件数（タイムアウトレコードを除いた実データのみ）
$cache_count = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s",
		$wpdb->esc_like( '_transient_rakuten_item_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_' ) . '%'
	)
);

// キャッシュデータのサイズを概算（実データ＋タイムアウトレコード）
$cache_size = $wpdb->get_var(
	$wpdb->prepare(
		"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_rakuten_item_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_rakuten_item_' ) . '%'
	)
);
$cache_size_mb = $cache_size ? round( $cache_size / 1024 / 1024, 2 ) : 0;
$cache_size_kb = $cache_size ? round( $cache_size / 1024, 2 ) : 0;

// 表示用の単位を決定
if ( $cache_size_mb >= 1 ) {
	$display_size = $cache_size_mb;
	$display_unit = 'MB';
} elseif ( $cache_size_kb >= 1 ) {
	$display_size = $cache_size_kb;
	$display_unit = 'KB';
} else {
	$display_size = $cache_size ? $cache_size : 0;
	$display_unit = 'bytes';
}
?>
<div class="plm-card plm-cache-settings">
    <h2>キャッシュ設定</h2>
    <table class="form-table">
        <tr>
            <th scope="row">キャッシュ状況</th>
            <td>
                <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                    <div style="flex: 1; padding: 20px; background: #667eea; border-radius: 8px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <span class="dashicons dashicons-database-view" style="font-size: 28px; width: 28px; height: 28px;"></span>
                            <span style="font-size: 13px; opacity: 0.9; font-weight: 500;">キャッシュ件数</span>
                        </div>
                        <div style="font-size: 32px; font-weight: bold; line-height: 1;">
                            <?= number_format( $cache_count ) ?>
                        </div>
                        <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">件</div>
                    </div>
                    <div style="flex: 1; padding: 20px; background: #f5576c; border-radius: 8px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <span class="dashicons dashicons-chart-area" style="font-size: 28px; width: 28px; height: 28px;"></span>
                            <span style="font-size: 13px; opacity: 0.9; font-weight: 500;">データサイズ</span>
                        </div>
                        <div style="font-size: 32px; font-weight: bold; line-height: 1;">
                            <?= number_format( $display_size, 2 ) ?>
                        </div>
                        <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">
                            <?= $display_unit ?> (概算)
                        </div>
                    </div>
                </div>
                <p class="description">
                    <span class="dashicons dashicons-info" style="color: #2271b1;"></span>
                    楽天APIから取得したデータをキャッシュとして保存しています。キャッシュを利用することでAPI呼び出し回数を削減できます。
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">成功時のキャッシュ時間</th>
            <td>
                <input type="number" name="affiliate_settings[cache_success_hours]" 
                       value="<?= esc_attr( $options['cache_success_hours'] ?? '24' ) ?>" 
                       min="1" max="168"
                       class="small-text" /> 時間
                <p class="description">API取得成功時のキャッシュ保存時間（1〜168時間）デフォルト: 24時間</p>
            </td>
        </tr>
        <tr>
            <th scope="row">レート制限エラー時のキャッシュ時間</th>
            <td>
                <input type="number" name="affiliate_settings[cache_ratelimit_minutes]" 
                       value="<?= esc_attr( $options['cache_ratelimit_minutes'] ?? '60' ) ?>" 
                       min="5" max="1440"
                       class="small-text" /> 分
                <p class="description">レート制限エラー時のキャッシュ保存時間（5〜1440分）デフォルト: 60分</p>
            </td>
        </tr>
        <tr>
            <th scope="row">その他エラー時のキャッシュ時間</th>
            <td>
                <input type="number" name="affiliate_settings[cache_error_minutes]" 
                       value="<?= esc_attr( $options['cache_error_minutes'] ?? '5' ) ?>" 
                       min="1" max="60"
                       class="small-text" 
                       disabled /> 分
                <p class="description">
                    <span class="dashicons dashicons-info" style="color: #2271b1;"></span>
                    その他エラー（ネットワーク障害等）はキャッシュ<strong>されません</strong>。次回アクセス時に再試行します。
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">キャッシュ削除</th>
            <td>
                <button type="button" id="plm-clear-cache" class="button" style="
                    background: #667eea;
                    color: #fff;
                    border: none;
                    padding: 8px 20px;
                    font-weight: 600;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                    cursor: pointer;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';">
                    <span class="dashicons dashicons-trash" style="margin-top: 3px;"></span>
                    すべてのキャッシュを削除
                </button>
                <p class="description" style="margin-top: 12px;">
                    <span class="dashicons dashicons-warning" style="color: #d63638;"></span>
                    <strong>注意:</strong> すべての楽天API取得データのキャッシュが削除されます。削除後は次回のアクセス時に再度APIが呼ばれます。
                </p>
                <div id="plm-cache-message" style="margin-top: 10px;"></div>
            </td>
        </tr>
    </table>
</div>
