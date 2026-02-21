<?php
/**
 * 設定ページビューテンプレート
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
    <h1>
        Product Link Maker
    </h1>
    <p class="description" style="margin-bottom: 20px;">各アフィリエイトサービスのIDを設定してください。設定したサービスのボタンが商品リンクに表示されます。</p>
    
    <!-- タブナビゲーション -->
    <h2 class="nav-tab-wrapper">
        <a href="?page=product-link-maker&tab=api-keys" class="nav-tab <?php echo $current_tab === 'api-keys' ? 'nav-tab-active' : ''; ?>">
            APIキー設定
        </a>
        <a href="?page=product-link-maker&tab=cache" class="nav-tab <?php echo $current_tab === 'cache' ? 'nav-tab-active' : ''; ?>">
            キャッシュ設定
        </a>
        <a href="?page=product-link-maker&tab=error-logs" class="nav-tab <?php echo $current_tab === 'error-logs' ? 'nav-tab-active' : ''; ?>">
            エラーログ
            <?php
            $error_logs = PLM_Error_Logger::get_logs();
            if ( ! empty( $error_logs ) ) :
            ?>
                <span class="plm-tab-badge" style="background: #d63638; color: #fff; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 4px;">
                    <?= count( $error_logs ) ?>
                </span>
            <?php endif; ?>
        </a>
    </h2>
    
    <form method="post" action="options.php">
        <?php settings_fields( 'plm_settings_group' ); ?>

        <style>
            .nav-tab-active {
                background: #fff !important;
                border-bottom-color: #fff !important;
            }
            .plm-card {
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 20px 24px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            }
            .plm-card h2 {
                margin-top: 0;
                padding-bottom: 12px;
                border-bottom: 2px solid #f0f0f0;
                font-size: 18px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .plm-card h2 .dashicons {
                font-size: 24px;
                width: 24px;
                height: 24px;
            }
            .plm-card .form-table th {
                padding: 12px 10px 12px 0;
                font-weight: 600;
            }
            .plm-cache-settings .form-table th {
                width: 280px;
            }
            .plm-card .form-table td {
                padding: 12px 0;
            }
            .plm-card .form-table input[type="text"],
            .plm-card .form-table input[type="password"] {
                width: 100%;
                max-width: 800px;
            }
            .plm-card p.description {
                margin: 8px 0 0 0;
                font-size: 13px;
                color: #666;
            }
            .plm-status-badge {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 600;
                margin-left: 8px;
            }
            .plm-status-active {
                background: #d4edda;
                color: #155724;
            }
            .plm-status-inactive {
                background: #f8d7da;
                color: #721c24;
            }
        </style>

        <?php if ( $current_tab === 'api-keys' ) : ?>
        <!-- APIキー設定タブ: キャッシュ設定を隠しフィールドで保持 -->
        <?php foreach ( array( 'cache_success_hours', 'cache_ratelimit_minutes', 'cache_error_minutes' ) as $field ) : ?>
            <?php if ( isset( $options[ $field ] ) && $options[ $field ] !== '' ) : ?>
                <input type="hidden" name="affiliate_settings[<?= esc_attr( $field ) ?>]" value="<?= esc_attr( $options[ $field ] ) ?>" />
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php elseif ( $current_tab === 'cache' ) : ?>
        <!-- キャッシュタブ: 他のタブの設定を隠しフィールドで保持 -->
        <?php foreach ( array( 'amazon_tracking_id', 'rakuten_app_id', 'rakuten_affiliate_id', 'yahoo_sid', 'yahoo_pid', 'mercari_id', 'dmm_id' ) as $field ) : ?>
            <?php if ( isset( $options[ $field ] ) && $options[ $field ] !== '' ) : ?>
                <input type="hidden" name="affiliate_settings[<?= esc_attr( $field ) ?>]" value="<?= esc_attr( $options[ $field ] ) ?>" />
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php elseif ( $current_tab === 'error-logs' ) : ?>
        <!-- エラーログタブ: 他のタブの設定を隠しフィールドで保持 -->
        <?php foreach ( array( 'amazon_tracking_id', 'rakuten_app_id', 'rakuten_affiliate_id', 'yahoo_sid', 'yahoo_pid', 'mercari_id', 'dmm_id', 'cache_success_hours', 'cache_ratelimit_minutes', 'cache_error_minutes' ) as $field ) : ?>
            <?php if ( isset( $options[ $field ] ) && $options[ $field ] !== '' ) : ?>
                <input type="hidden" name="affiliate_settings[<?= esc_attr( $field ) ?>]" value="<?= esc_attr( $options[ $field ] ) ?>" />
            <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if ( $current_tab === 'api-keys' ) : ?>
            <?php include PLM_PLUGIN_DIR . 'includes/admin/views/tab-api-keys.php'; ?>
        <?php elseif ( $current_tab === 'cache' ) : ?>
            <?php include PLM_PLUGIN_DIR . 'includes/admin/views/tab-cache.php'; ?>
        <?php elseif ( $current_tab === 'error-logs' ) : ?>
            <?php include PLM_PLUGIN_DIR . 'includes/admin/views/tab-error-logs.php'; ?>
        <?php endif; ?>

        <?php if ( $current_tab === 'api-keys' || $current_tab === 'cache' ) : ?>
            <?php submit_button( '設定を保存', 'primary large' ); ?>
        <?php endif; ?>
    </form>

    <?php include PLM_PLUGIN_DIR . 'includes/admin/views/settings-scripts.php'; ?>
</div>
