<?php
/**
 * エラーログタブ
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$error_logs = PLM_Error_Logger::get_logs();
?>
<div class="plm-card">
    <h2>
        エラーログ
        <?php if ( ! empty( $error_logs ) ) : ?>
            <span class="plm-status-badge" style="background: #d63638; color: #fff;">
                <?= count( $error_logs ) ?>件
            </span>
        <?php else : ?>
            <span class="plm-status-badge plm-status-inactive">0件</span>
        <?php endif; ?>
    </h2>
    
    <div style="margin-bottom: 12px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        <button type="button" id="plm-check-all-errors" class="button button-primary">
            全エラーをチェック
        </button>
        <?php if ( ! empty( $error_logs ) ) : ?>
            <button type="button" id="plm-clear-error-logs" class="button">
                エラーログをクリア
            </button>
        <?php endif; ?>
        <button type="button" id="plm-clear-error-cache" class="button">
            エラーキャッシュをクリア
        </button>
    </div>
    
    <!-- プログレスバー -->
    <div id="plm-check-progress" style="display: none; margin-bottom: 12px;">
        <div style="background: #f0f0f1; border-radius: 4px; padding: 12px;">
            <div style="margin-bottom: 8px;">
                <strong id="plm-progress-text">チェック中...</strong>
            </div>
            <div style="background: #fff; height: 24px; border-radius: 4px; overflow: hidden; position: relative;">
                <div id="plm-progress-bar" style="background: linear-gradient(90deg, #2271b1, #135e96); height: 100%; width: 0%; transition: width 0.3s;">
                    <span id="plm-progress-percent" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); color: #fff; font-weight: bold; font-size: 12px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">0%</span>
                </div>
            </div>
            <div style="margin-top: 8px; font-size: 13px; color: #666;">
                <span id="plm-progress-detail">処理中: 0 / 0 投稿</span>
                <span style="margin-left: 12px;">エラー発見: <strong id="plm-error-found" style="color: #d63638;">0</strong>件</span>
                <span style="margin-left: 12px;">ブロック: <strong id="plm-blocks-found">0</strong>個</span>
            </div>
        </div>
    </div>
    
    <div id="plm-error-log-message" style="margin-top: 10px;"></div>
    
    <?php if ( ! empty( $error_logs ) ) : ?>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 12px;">
            <thead>
                <tr>
                    <th style="width: 150px;">日時</th>
                    <th style="width: 120px;">エラータイプ</th>
                    <th>エラー内容</th>
                    <th style="width: 200px;">投稿</th>
                    <th style="width: 150px;">商品ID/キーワード</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( array_slice( $error_logs, 0, 20 ) as $log ) : ?>
                <tr>
                    <td><?= esc_html( $log['timestamp'] ) ?></td>
                    <td>
                        <code style="color: #d63638; font-size: 11px;">
                            <?= esc_html( $log['error_type'] ) ?>
                        </code>
                    </td>
                    <td><?= esc_html( $log['error_message'] ) ?></td>
                    <td>
                        <?php if ( ! empty( $log['post_id'] ) && is_numeric( $log['post_id'] ) && $log['post_id'] > 0 ) : ?>
                            <?php $edit_link = get_edit_post_link( $log['post_id'] ); ?>
                            <?php if ( $edit_link ) : ?>
                                <a href="<?= esc_url( $edit_link ) ?>" target="_blank">
                                    <?= esc_html( $log['post_title'] ?: '(タイトルなし)' ) ?>
                                    <span class="dashicons dashicons-external" style="font-size: 14px;"></span>
                                </a>
                                <br><small style="color: #666;">ID: <?= esc_html( $log['post_id'] ) ?></small>
                            <?php else : ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        <?php else : ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ( $log['item_id'] ) : ?>
                            <strong>ID:</strong> <?= esc_html( $log['item_id'] ) ?>
                        <?php elseif ( $log['keyword'] ) : ?>
                            <strong>KW:</strong> <?= esc_html( $log['keyword'] ) ?>
                        <?php else : ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ( count( $error_logs ) > 20 ) : ?>
            <p class="description" style="margin-top: 12px;">
                <span class="dashicons dashicons-info" style="color: #2271b1;"></span>
                最新20件を表示しています（全<?= count( $error_logs ) ?>件）
            </p>
        <?php endif; ?>
    <?php else : ?>
        <p style="padding: 20px; background: #f9f9f9; border-radius: 4px; color: #666;">
            <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
            現在エラーログはありません。楽天APIでエラーが発生すると、ここに自動的に記録されます。
        </p>
    <?php endif; ?>
</div>
