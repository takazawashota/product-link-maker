<?php
/**
 * Plugin Name:       Product Link Maker
 * Plugin URI:        https://sokulabo.com/products/product-link-maker/
 * Description:       商品リンクを生成するためのプラグインです。
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Shota Takazawa
 * Author URI:        https://sokulabo.com/products/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       product-link-maker
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// プラグイン定数
define( 'PLM_VERSION', '0.1.0' );
define( 'PLM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PLM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PLM_OPTION_NAME', 'affiliate_settings' );

// キャッシュ設定のデフォルト値
define( 'PLM_CACHE_SUCCESS_HOURS', 24 );
define( 'PLM_CACHE_RATELIMIT_MINUTES', 60 );
define( 'PLM_CACHE_ERROR_MINUTES', 5 );

// APIエンドポイント
define( 'PLM_RAKUTEN_API_URL', 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20170706' );

/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
/**
 * ブロックタイプを登録
 */
function plm_register_blocks() {
	$build_dir = PLM_PLUGIN_DIR . 'build';
	$manifest_file = $build_dir . '/blocks-manifest.php';

	// WordPress 6.8以降
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( $build_dir, $manifest_file );
		return;
	}

	// WordPress 6.7
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( $build_dir, $manifest_file );
	}

	// それ以前のバージョン
	if ( ! file_exists( $manifest_file ) ) {
		return;
	}

	$manifest_data = require $manifest_file;
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( $build_dir . '/' . $block_type );
	}
}
add_action( 'init', 'plm_register_blocks' );

/**
 * カスタムブロックカテゴリーを追加
 *
 * @param array $categories ブロックカテゴリーの配列
 * @return array 更新されたカテゴリー配列
 */
function plm_add_block_category( $categories ) {
	$custom_category = array(
		'slug'  => 'product-link-maker',
		'title' => __( 'Product Link Maker', 'product-link-maker' ),
		'icon'  => 'cart',
	);

	// ウィジェットカテゴリーの前に挿入
	$widget_index = 0;
	foreach ( $categories as $index => $category ) {
		if ( isset( $category['slug'] ) && 'widgets' === $category['slug'] ) {
			$widget_index = $index;
			break;
		}
	}

	array_splice( $categories, $widget_index, 0, array( $custom_category ) );
	return $categories;
}
add_filter( 'block_categories_all', 'plm_add_block_category' );

/**
 * 設定ページをメニューに追加
 */
function plm_add_settings_page() {
	add_options_page(
		__( 'Product Link Maker', 'product-link-maker' ),
		__( 'Product Link Maker', 'product-link-maker' ),
		'manage_options',
		'product-link-maker',
		'plm_render_settings_page'
	);
}
add_action( 'admin_menu', 'plm_add_settings_page' );

/**
 * 設定を登録
 */
function plm_register_settings() {
	register_setting(
		'plm_settings_group',
		PLM_OPTION_NAME,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'plm_sanitize_settings',
		)
	);
}
add_action( 'admin_init', 'plm_register_settings' );

/**
 * 設定値をサニタイズ
 *
 * @param array $settings 設定値
 * @return array サニタイズされた設定値
 */
function plm_sanitize_settings( $settings ) {
	if ( ! is_array( $settings ) ) {
		return array();
	}

	// 既存の設定を取得してマージ（部分的な更新に対応）
	$existing = get_option( PLM_OPTION_NAME, array() );
	$sanitized = $existing;

	$text_fields = array(
		'amazon_tracking_id', 'amazon_access_key', 'amazon_secret_key',
		'rakuten_app_id', 'rakuten_affiliate_id',
		'yahoo_sid', 'yahoo_pid',
		'mercari_id', 'dmm_id'
	);

	foreach ( $text_fields as $field ) {
		if ( isset( $settings[ $field ] ) ) {
			$sanitized[ $field ] = sanitize_text_field( $settings[ $field ] );
		}
	}

	// 数値フィールド
	if ( isset( $settings['cache_success_hours'] ) ) {
		$sanitized['cache_success_hours'] = absint( $settings['cache_success_hours'] );
	}
	if ( isset( $settings['cache_ratelimit_minutes'] ) ) {
		$sanitized['cache_ratelimit_minutes'] = absint( $settings['cache_ratelimit_minutes'] );
	}
	if ( isset( $settings['cache_error_minutes'] ) ) {
		$sanitized['cache_error_minutes'] = absint( $settings['cache_error_minutes'] );
	}

	return $sanitized;
}
/**
 * 設定ページを表示
 */
function plm_render_settings_page() {
	$options = get_option( PLM_OPTION_NAME, array() );
	
	// 設定が更新された場合のメッセージ表示
	if ( isset( $_GET['settings-updated'] ) ) {
		add_settings_error(
			'plm_messages',
			'plm_message',
			__( '設定を保存しました。', 'product-link-maker' ),
			'updated'
		);
	}
	
	// デバッグ: 現在の設定を表示
	if ( isset( $_GET['debug'] ) && $_GET['debug'] === '1' ) {
		echo '<div class="notice notice-info"><h3>現在の設定 (デバッグ)</h3><pre>';
		print_r( $options );
		echo '</pre></div>';
	}
	
	// 現在のタブを取得（デフォルトは api-keys）
	$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'api-keys';
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
                $error_logs = plm_get_error_logs();
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
            <?php foreach ( array( 'amazon_tracking_id', 'amazon_access_key', 'amazon_secret_key', 'rakuten_app_id', 'rakuten_affiliate_id', 'yahoo_sid', 'yahoo_pid', 'mercari_id', 'dmm_id' ) as $field ) : ?>
                <?php if ( isset( $options[ $field ] ) && $options[ $field ] !== '' ) : ?>
                    <input type="hidden" name="affiliate_settings[<?= esc_attr( $field ) ?>]" value="<?= esc_attr( $options[ $field ] ) ?>" />
                <?php endif; ?>
            <?php endforeach; ?>
            
            <?php elseif ( $current_tab === 'error-logs' ) : ?>
            <!-- エラーログタブ: 他のタブの設定を隠しフィールドで保持 -->
            <?php foreach ( array( 'amazon_tracking_id', 'amazon_access_key', 'amazon_secret_key', 'rakuten_app_id', 'rakuten_affiliate_id', 'yahoo_sid', 'yahoo_pid', 'mercari_id', 'dmm_id', 'cache_success_hours', 'cache_ratelimit_minutes', 'cache_error_minutes' ) as $field ) : ?>
                <?php if ( isset( $options[ $field ] ) && $options[ $field ] !== '' ) : ?>
                    <input type="hidden" name="affiliate_settings[<?= esc_attr( $field ) ?>]" value="<?= esc_attr( $options[ $field ] ) ?>" />
                <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>

            <?php if ( $current_tab === 'api-keys' ) : ?>
            <div class="plm-card">
                <h2>
                    Amazon
                    <?php if (!empty($options['amazon_tracking_id'])): ?>
                        <span class="plm-status-badge plm-status-active">設定済み</span>
                    <?php else: ?>
                        <span class="plm-status-badge plm-status-inactive">未設定</span>
                    <?php endif; ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">トラッキングID <span style="color: #d63638;">*</span></th>
                        <td>
                            <input type="text" name="affiliate_settings[amazon_tracking_id]" 
                                   value="<?= esc_attr($options['amazon_tracking_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: yourname-22" />
                            <p class="description">
                                <span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
                                <strong>必須:</strong> Amazonアソシエイト・プログラムのトラッキングIDを入力してください。<br>
                                <span class="dashicons dashicons-info" style="color: #2271b1;"></span>
                                Creators API対応。トラッキングIDのみで検索リンクの生成が可能です。<br>
                                <span class="dashicons dashicons-external" style="color: #2271b1;"></span>
                                <a href="https://affiliate.amazon.co.jp/" target="_blank" rel="noopener">Amazonアソシエイト・プログラムに登録する →</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 楽天 -->
            <div class="plm-card">
                <h2>
                    楽天
                    <?php if (!empty($options['rakuten_affiliate_id'])): ?>
                        <span class="plm-status-badge plm-status-active">設定済み</span>
                    <?php else: ?>
                        <span class="plm-status-badge plm-status-inactive">未設定</span>
                    <?php endif; ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">楽天アプリケーションID</th>
                        <td>
                            <input type="text" name="affiliate_settings[rakuten_app_id]" 
                                   value="<?= esc_attr($options['rakuten_app_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: 1234567890123456789" />
                            <p class="description">
                                楽天ウェブサービスで取得したアプリケーションIDを入力してください。<br>
                                <span class="dashicons dashicons-external" style="color: #2271b1;"></span>
                                <a href="https://webservice.rakuten.co.jp/" target="_blank" rel="noopener">楽天ウェブサービスに登録する →</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">楽天アフィリエイトID</th>
                        <td>
                            <input type="text" name="affiliate_settings[rakuten_affiliate_id]" 
                                   value="<?= esc_attr($options['rakuten_affiliate_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: 12345678.90123456.12345678.90123456" />
                            <p class="description">
                                楽天アフィリエイトIDを入力してください。<br>
                                <span class="dashicons dashicons-external" style="color: #2271b1;"></span>
                                <a href="https://affiliate.rakuten.co.jp/" target="_blank" rel="noopener">楽天アフィリエイトに登録する →</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Yahoo!ショッピング -->
            <div class="plm-card">
                <h2>
                    Yahoo!ショッピング
                    <?php if (!empty($options['yahoo_sid']) && !empty($options['yahoo_pid'])): ?>
                        <span class="plm-status-badge plm-status-active">設定済み</span>
                    <?php else: ?>
                        <span class="plm-status-badge plm-status-inactive">未設定</span>
                    <?php endif; ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">バリューコマースSID</th>
                        <td>
                            <input type="text" name="affiliate_settings[yahoo_sid]" 
                                   value="<?= esc_attr($options['yahoo_sid'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: 1234567" />
                            <p class="description">
                                バリューコマースのSID（サイトID）を入力してください。<br>
                                <span class="dashicons dashicons-external" style="color: #2271b1;"></span>
                                <a href="https://www.valuecommerce.ne.jp/" target="_blank" rel="noopener">バリューコマースに登録する →</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">バリューコマースPID</th>
                        <td>
                            <input type="text" name="affiliate_settings[yahoo_pid]" 
                                   value="<?= esc_attr($options['yahoo_pid'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: 890123456" />
                            <p class="description">バリューコマースのPID（プロモーションID）を入力してください。SIDと同じ管理画面で確認できます。</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- メルカリ -->
            <div class="plm-card">
                <h2>
                    メルカリ
                    <?php if (!empty($options['mercari_id'])): ?>
                        <span class="plm-status-badge plm-status-active">設定済み</span>
                    <?php else: ?>
                        <span class="plm-status-badge plm-status-inactive">未設定</span>
                    <?php endif; ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">メルカリアンバサダーID</th>
                        <td>
                            <input type="text" name="affiliate_settings[mercari_id]" 
                                   value="<?= esc_attr($options['mercari_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: your_ambassador_id" />
                            <p class="description">
                                メルカリアンバサダープログラムのIDを入力してください。<br>
                                <span class="dashicons dashicons-external" style="color: #2271b1;"></span>
                                <a href="https://ambassador.mercari.com/" target="_blank" rel="noopener">メルカリアンバサダーに登録する →</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- DMM -->
            <div class="plm-card">
                <h2>
                    DMM
                    <?php if (!empty($options['dmm_id'])): ?>
                        <span class="plm-status-badge plm-status-active">設定済み</span>
                    <?php else: ?>
                        <span class="plm-status-badge plm-status-inactive">未設定</span>
                    <?php endif; ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">DMMアフィリエイトID</th>
                        <td>
                            <input type="text" name="affiliate_settings[dmm_id]" 
                                   value="<?= esc_attr($options['dmm_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: yourname-001" />
                            <p class="description">
                                DMMアフィリエイトIDを入力してください。<br>
                                <span class="dashicons dashicons-external" style="color: #2271b1;"></span>
                                <a href="https://www.dmm.com/digital/affiliate/-/guide/" target="_blank" rel="noopener">DMMアフィリエイトに登録する →</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <?php elseif ( $current_tab === 'cache' ) : ?>
            <!-- キャッシュ設定タブ -->
            
            <!-- キャッシュ設定 -->
            <div class="plm-card plm-cache-settings">
                <h2>
                    キャッシュ設定
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">キャッシュ状況</th>
                        <td>
                            <?php
                            global $wpdb;
                            // キャッシュ件数（タイムアウトレコードを除いた実データのみ）
                            $cache_count = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name NOT LIKE %s",
                                    $wpdb->esc_like('_transient_rakuten_item_') . '%',
                                    $wpdb->esc_like('_transient_timeout_') . '%'
                                )
                            );
                            
                            // キャッシュデータのサイズを概算（実データ＋タイムアウトレコード）
                            $cache_size = $wpdb->get_var(
                                $wpdb->prepare(
                                    "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                                    $wpdb->esc_like('_transient_rakuten_item_') . '%',
                                    $wpdb->esc_like('_transient_timeout_rakuten_item_') . '%'
                                )
                            );
                            $cache_size_mb = $cache_size ? round($cache_size / 1024 / 1024, 2) : 0;
                            $cache_size_kb = $cache_size ? round($cache_size / 1024, 2) : 0;
                            
                            // 表示用の単位を決定
                            if ($cache_size_mb >= 1) {
                                $display_size = $cache_size_mb;
                                $display_unit = 'MB';
                            } elseif ($cache_size_kb >= 1) {
                                $display_size = $cache_size_kb;
                                $display_unit = 'KB';
                            } else {
                                $display_size = $cache_size ? $cache_size : 0;
                                $display_unit = 'bytes';
                            }
                            ?>
                            <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                                <div style="flex: 1; padding: 20px; background: #667eea; border-radius: 8px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                        <span class="dashicons dashicons-database-view" style="font-size: 28px; width: 28px; height: 28px;"></span>
                                        <span style="font-size: 13px; opacity: 0.9; font-weight: 500;">キャッシュ件数</span>
                                    </div>
                                    <div style="font-size: 32px; font-weight: bold; line-height: 1;">
                                        <?= number_format($cache_count) ?>
                                    </div>
                                    <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">件</div>
                                </div>
                                <div style="flex: 1; padding: 20px; background: #f5576c; border-radius: 8px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                        <span class="dashicons dashicons-chart-area" style="font-size: 28px; width: 28px; height: 28px;"></span>
                                        <span style="font-size: 13px; opacity: 0.9; font-weight: 500;">データサイズ</span>
                                    </div>
                                    <div style="font-size: 32px; font-weight: bold; line-height: 1;">
                                        <?= number_format($display_size, 2) ?>
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
                                   value="<?= esc_attr($options['cache_success_hours'] ?? '24') ?>" 
                                   min="1" max="168"
                                   class="small-text" /> 時間
                            <p class="description">API取得成功時のキャッシュ保存時間（1〜168時間）デフォルト: 24時間</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">レート制限エラー時のキャッシュ時間</th>
                        <td>
                            <input type="number" name="affiliate_settings[cache_ratelimit_minutes]" 
                                   value="<?= esc_attr($options['cache_ratelimit_minutes'] ?? '60') ?>" 
                                   min="5" max="1440"
                                   class="small-text" /> 分
                            <p class="description">レート制限エラー時のキャッシュ保存時間（5〜1440分）デフォルト: 60分</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">その他エラー時のキャッシュ時間</th>
                        <td>
                            <input type="number" name="affiliate_settings[cache_error_minutes]" 
                                   value="<?= esc_attr($options['cache_error_minutes'] ?? '5') ?>" 
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

            <?php elseif ( $current_tab === 'error-logs' ) : ?>
            <!-- エラーログタブ -->
            
            <!-- エラーログ -->
            <div class="plm-card">
                <h2>
                    エラーログ
                    <?php
                    $error_logs = plm_get_error_logs();
                    if ( ! empty( $error_logs ) ) :
                    ?>
                        <span class="plm-status-badge" style="background: #d63638; color: #fff;">
                            <?= count( $error_logs ) ?>件
                        </span>
                    <?php else : ?>
                        <span class="plm-status-badge plm-status-inactive">0件</span>
                    <?php endif; ?>
                </h2>
                
                <div style="margin-bottom: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <?php if ( ! empty( $error_logs ) ) : ?>
                        <button type="button" id="plm-clear-error-logs" class="button">
                            エラーログをクリア
                        </button>
                    <?php endif; ?>
                    <button type="button" id="plm-clear-error-cache" class="button">
                        エラーキャッシュをクリア
                    </button>
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
                                    <?php if ( $log['post_id'] ) : ?>
                                        <a href="<?= get_edit_post_link( $log['post_id'] ) ?>" target="_blank">
                                            <?= esc_html( $log['post_title'] ?: '(タイトルなし)' ) ?>
                                            <span class="dashicons dashicons-external" style="font-size: 14px;"></span>
                                        </a>
                                        <br><small style="color: #666;">ID: <?= esc_html( $log['post_id'] ) ?></small>
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

            <?php endif; ?>

            <?php if ( $current_tab === 'api-keys' || $current_tab === 'cache' ) : ?>
                <?php submit_button('設定を保存', 'primary large'); ?>
            <?php endif; ?>
        </form>

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
    </div>
    <?php
}


/**
 * エディター用アセットを読み込み
 */
function plm_enqueue_editor_assets() {
	$asset_file = PLM_PLUGIN_DIR . 'build/index.asset.php';
	$asset_data = file_exists( $asset_file ) ? require $asset_file : array(
		'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data' ),
		'version'      => PLM_VERSION,
	);

	wp_enqueue_script(
		'product-link-maker-editor',
		PLM_PLUGIN_URL . 'build/index.js',
		$asset_data['dependencies'],
		$asset_data['version'],
		true
	);

	// 設定をJSに渡す
	$settings = plm_get_affiliate_settings();
	wp_localize_script(
		'product-link-maker-editor',
		'ProductLinkMakerSettings',
		array(
			'amazon'  => ! empty( $settings['amazon_tracking_id'] ),
			'rakuten' => ! empty( $settings['rakuten_affiliate_id'] ),
			'yahoo'   => ! empty( $settings['yahoo_sid'] ) && ! empty( $settings['yahoo_pid'] ),
			'mercari' => ! empty( $settings['mercari_id'] ),
			'dmm'     => ! empty( $settings['dmm_id'] ),
			// アフィリエイトID（エディタプレビュー用）
			'amazonTrackingId' => $settings['amazon_tracking_id'] ?? '',
			'rakutenAffiliateId' => $settings['rakuten_affiliate_id'] ?? '',
			'yahooSid' => $settings['yahoo_sid'] ?? '',
			'yahooPid' => $settings['yahoo_pid'] ?? '',
			'mercariId' => $settings['mercari_id'] ?? '',
			'dmmId' => $settings['dmm_id'] ?? '',
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'plm_enqueue_editor_assets' );

/**
 * アフィリエイト設定を取得
 *
 * @return array 設定値の配列
 */
function plm_get_affiliate_settings() {
	return get_option( PLM_OPTION_NAME, array() );
}

/**
 * キャッシュ設定を取得
 *
 * @return array キャッシュ設定の配列
 */
function plm_get_cache_settings() {
	$settings = plm_get_affiliate_settings();
	return array(
		'success_hours'     => isset( $settings['cache_success_hours'] ) ? intval( $settings['cache_success_hours'] ) : PLM_CACHE_SUCCESS_HOURS,
		'ratelimit_minutes' => isset( $settings['cache_ratelimit_minutes'] ) ? intval( $settings['cache_ratelimit_minutes'] ) : PLM_CACHE_RATELIMIT_MINUTES,
		'error_minutes'     => isset( $settings['cache_error_minutes'] ) ? intval( $settings['cache_error_minutes'] ) : PLM_CACHE_ERROR_MINUTES,
	);
}




/**
 * 楽天API用のリクエストURLを構築
 *
 * @param string      $app_id アプリケーションID
 * @param string      $affiliate_id アフィリエイトID
 * @param string|null $item_id 商品ID
 * @param string|null $keyword キーワード
 * @return string リクエストURL
 */
function plm_build_rakuten_api_url( $app_id, $affiliate_id, $item_id = null, $keyword = null ) {
	$params = array(
		'applicationId' => $app_id,
		'affiliateId'   => $affiliate_id,
		'imageFlag'     => '1',
		'hits'          => '1',
	);

	if ( ! empty( $item_id ) ) {
		$params['itemCode'] = $item_id;
	} elseif ( ! empty( $keyword ) ) {
		$params['keyword'] = $keyword;
	}

	return add_query_arg( $params, PLM_RAKUTEN_API_URL );
}

/**
 * APIエラーレスポンスを生成
 *
 * @param string $error_code エラーコード
 * @param string $error_message エラーメッセージ
 * @return string JSON文字列
 */
function plm_create_error_response( $error_code, $error_message ) {
	return wp_json_encode(
		array(
			'error'             => $error_code,
			'error_description' => $error_message,
		)
	);
}

/**
 * 楽天APIから商品情報を取得
 *
 * @param string      $app_id アプリケーションID
 * @param string      $affiliate_id アフィリエイトID
 * @param string|null $item_id 商品ID
 * @param string|null $keyword キーワード
 * @return string JSON文字列
 */
function plm_fetch_rakuten_item( $app_id, $affiliate_id, $item_id = null, $keyword = null ) {
	// パラメータ検証
	if ( empty( $app_id ) || empty( $affiliate_id ) ) {
		return plm_create_error_response(
			'missing_credentials',
			__( '楽天アプリケーションIDまたはアフィリエイトIDがありません。', 'product-link-maker' )
		);
	}

	if ( empty( $item_id ) && empty( $keyword ) ) {
		return plm_create_error_response(
			'missing_params',
			__( '商品IDまたはキーワードを指定してください。', 'product-link-maker' )
		);
	}

	// APIリクエスト
	$request_url = plm_build_rakuten_api_url( $app_id, $affiliate_id, $item_id, $keyword );
	$response    = wp_remote_get(
		$request_url,
		array(
			'timeout'   => 15,
			'sslverify' => true,
		)
	);

	// エラーハンドリング
	if ( is_wp_error( $response ) ) {
		return plm_create_error_response(
			'request_failed',
			/* translators: %s: エラーメッセージ */
			sprintf( __( '楽天APIへの接続に失敗しました: %s', 'product-link-maker' ), $response->get_error_message() )
		);
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	$body          = wp_remote_retrieve_body( $response );

	// レート制限エラー
	if ( 429 === $response_code ) {
		return plm_create_error_response(
			'rate_limit',
			__( 'APIリクエスト制限に達しました。しばらく待ってから再度お試しください。', 'product-link-maker' )
		);
	}

	// その他のHTTPエラー
	if ( 200 !== $response_code || empty( $body ) ) {
		return plm_create_error_response(
			'api_error',
			/* translators: %d: HTTPステータスコード */
			sprintf( __( '楽天APIからデータを取得できませんでした。(HTTP %d)', 'product-link-maker' ), $response_code )
		);
	}

	return $body;
}

/**
 * キャッシュキーを生成
 *
 * @param string $prefix プレフィックス
 * @param array  $params パラメータ配列
 * @return string キャッシュキー
 */
function plm_generate_cache_key( $prefix, $params ) {
	return $prefix . '_' . md5( wp_json_encode( $params ) );
}

/**
 * エラータイプに応じたキャッシュ期間を取得
 *
 * @param string $error_type エラータイプ
 * @param array  $cache_settings キャッシュ設定
 * @return int キャッシュ期間（秒）
 */
function plm_get_cache_expiration( $error_type, $cache_settings ) {
	if ( 'rate_limit' === $error_type ) {
		return $cache_settings['ratelimit_minutes'] * MINUTE_IN_SECONDS;
	}

	if ( 'success' === $error_type ) {
		return $cache_settings['success_hours'] * HOUR_IN_SECONDS;
	}

	return $cache_settings['error_minutes'] * MINUTE_IN_SECONDS;
}

/**
 * データをキャッシュに保存
 *
 * @param string $cache_key キャッシュキー
 * @param string $data データ
 * @param array  $cache_settings キャッシュ設定
 */
function plm_cache_data( $cache_key, $data, $cache_settings ) {
	$response_data = json_decode( $data, true );
	$error_type    = 'success';

	if ( isset( $response_data['error'] ) ) {
		$error_type = $response_data['error'];
	}

	// レート制限以外のエラーはキャッシュしない（一時的な障害の可能性があるため）
	if ( 'success' !== $error_type && 'rate_limit' !== $error_type ) {
		return;
	}

	$expiration = plm_get_cache_expiration( $error_type, $cache_settings );
	set_transient( $cache_key, $data, $expiration );
}

/**
 * キャッシュ付き楽天API取得
 *
 * @param string      $app_id アプリケーションID
 * @param string      $affiliate_id アフィリエイトID
 * @param string|null $item_id 商品ID
 * @param string|null $keyword キーワード
 * @param string|null $no 商品番号
 * @return string JSON文字列
 */
function plm_get_rakuten_item_cached( $app_id, $affiliate_id, $item_id = null, $keyword = null, $no = null ) {
	// 商品番号があればキーワードとして優先
	$use_keyword = ! empty( $no ) ? $no : $keyword;

	// キャッシュキーを生成
	$cache_key = plm_generate_cache_key(
		'rakuten_item',
		array( $app_id, $affiliate_id, $item_id, $use_keyword )
	);

	// キャッシュチェック
	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	// API呼び出し
	$json = plm_fetch_rakuten_item( $app_id, $affiliate_id, $item_id, $use_keyword );

	// キャッシュに保存
	$cache_settings = plm_get_cache_settings();
	plm_cache_data( $cache_key, $json, $cache_settings );

	return $json;
}


/**
 * エラーログを記録
 *
 * @param string $error_type エラータイプ
 * @param string $error_message エラーメッセージ
 * @param array  $context コンテキスト情報
 */
function plm_log_error( $error_type, $error_message, $context = array() ) {
	// オプションが存在しない場合は初期化
	$logs = get_option( 'plm_error_logs', false );
	if ( false === $logs ) {
		$logs = array();
		add_option( 'plm_error_logs', $logs, '', 'no' );
	} elseif ( ! is_array( $logs ) ) {
		$logs = array();
	}

	$log_entry = array(
		'timestamp'     => current_time( 'mysql' ),
		'error_type'    => $error_type,
		'error_message' => $error_message,
		'post_id'       => $context['post_id'] ?? null,
		'post_title'    => $context['post_title'] ?? null,
		'item_id'       => $context['item_id'] ?? null,
		'keyword'       => $context['keyword'] ?? null,
	);

	// 同じpost_id, item_id, error_typeの既存エントリを検索
	$existing_index = -1;
	foreach ( $logs as $index => $existing_log ) {
		if ( 
			$existing_log['post_id'] === $log_entry['post_id'] &&
			$existing_log['item_id'] === $log_entry['item_id'] &&
			$existing_log['error_type'] === $log_entry['error_type']
		) {
			$existing_index = $index;
			break;
		}
	}

	// 既存のエントリがある場合は削除（後で先頭に追加するため）
	if ( $existing_index !== -1 ) {
		array_splice( $logs, $existing_index, 1 );
	}

	// 最新100件まで保持
	array_unshift( $logs, $log_entry );
	$logs = array_slice( $logs, 0, 100 );

	// 直接データベースに書き込む（update_optionのキャッシュ問題を回避）
	global $wpdb;
	$serialized_data = maybe_serialize( $logs );
	
	$result = $wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) 
			VALUES (%s, %s, 'no') 
			ON DUPLICATE KEY UPDATE option_value = %s",
			'plm_error_logs',
			$serialized_data,
			$serialized_data
		)
	);
	
	// キャッシュをクリア
	wp_cache_delete( 'plm_error_logs', 'options' );
	
	return $result !== false;
}

/**
 * エラーログを取得
 *
 * @return array エラーログの配列
 */
function plm_get_error_logs() {
	// キャッシュをクリアして最新データを取得
	wp_cache_delete( 'plm_error_logs', 'options' );
	
	$logs = get_option( 'plm_error_logs', array() );
	
	// 配列でない場合は空配列を返す
	if ( ! is_array( $logs ) ) {
		return array();
	}
	
	return $logs;
}

/**
 * エラーログをクリア
 */
function plm_clear_error_logs() {
	delete_option( 'plm_error_logs' );
}

/**
 * 楽天商品情報を取得するREST APIハンドラ
 *
 * @param WP_REST_Request $request リクエストオブジェクト
 * @return WP_REST_Response レスポンス
 */
function plm_rest_get_rakuten_item( $request ) {
	$settings = plm_get_affiliate_settings();

	$item_id = $request->get_param( 'id' );
	$keyword = $request->get_param( 'kw' );
	$no      = $request->get_param( 'no' );
	$post_id = $request->get_param( 'post_id' );

	$json = plm_get_rakuten_item_cached(
		$settings['rakuten_app_id'] ?? '',
		$settings['rakuten_affiliate_id'] ?? '',
		$item_id,
		$keyword,
		$no
	);

	$result = json_decode( $json, true );

	// エラーが発生した場合はログに記録（レート制限以外）
	if ( isset( $result['error'] ) && 'rate_limit' !== $result['error'] ) {
		$post_title = '';
		if ( $post_id ) {
			$post = get_post( $post_id );
			if ( $post ) {
				$post_title = $post->post_title;
			}
		}

		plm_log_error(
			$result['error'],
			$result['error_description'] ?? '',
			array(
				'post_id'    => $post_id,
				'post_title' => $post_title,
				'item_id'    => $item_id,
				'keyword'    => $keyword ?: $no,
			)
		);
	}

	// affiliate_idsを追加
	$result['affiliate_ids'] = array(
		'amazon_tracking_id'   => $settings['amazon_tracking_id'] ?? '',
		'rakuten_app_id'       => $settings['rakuten_app_id'] ?? '',
		'rakuten_affiliate_id' => $settings['rakuten_affiliate_id'] ?? '',
		'yahoo_sid'            => $settings['yahoo_sid'] ?? '',
		'yahoo_pid'            => $settings['yahoo_pid'] ?? '',
		'mercari_id'           => $settings['mercari_id'] ?? '',
		'dmm_id'               => $settings['dmm_id'] ?? '',
	);

	return rest_ensure_response( $result );
}

/**
 * 楽天商品キャッシュを削除するREST APIハンドラ
 *
 * @param WP_REST_Request $request リクエストオブジェクト
 * @return WP_REST_Response レスポンス
 */
function plm_rest_clear_rakuten_cache( $request ) {
	$settings  = plm_get_affiliate_settings();
	$cache_key = plm_generate_cache_key(
		'rakuten_item',
		array(
			$settings['rakuten_app_id'] ?? '',
			$settings['rakuten_affiliate_id'] ?? '',
			$request->get_param( 'id' ),
			$request->get_param( 'kw' ) ?? $request->get_param( 'no' ),
		)
	);

	delete_transient( $cache_key );
	return rest_ensure_response( array( 'deleted' => true ) );
}

/**
 * すべてのキャッシュを削除するREST APIハンドラ
 *
 * @return WP_REST_Response レスポンス
 */
function plm_rest_clear_all_cache() {
	global $wpdb;

	$patterns = array(
		$wpdb->esc_like( '_transient_rakuten_item_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_rakuten_item_' ) . '%',
		$wpdb->esc_like( '_transient_amazon_item_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_amazon_item_' ) . '%',
	);

	$deleted = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE %s 
			OR option_name LIKE %s 
			OR option_name LIKE %s 
			OR option_name LIKE %s",
			$patterns[0],
			$patterns[1],
			$patterns[2],
			$patterns[3]
		)
	);

	return rest_ensure_response(
		array(
			'success' => true,
			'message' => sprintf( __( '%d 件のキャッシュを削除しました。', 'product-link-maker' ), $deleted ),
		)
	);
}

/**
 * REST APIエンドポイントを登録
 */
function plm_register_rest_routes() {
	// 楽天商品情報取得
	register_rest_route(
		'product-link-maker/v1',
		'/rakuten/',
		array(
			'methods'             => 'GET',
			'callback'            => 'plm_rest_get_rakuten_item',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'kw' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'no' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'post_id' => array(
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	// 楽天キャッシュ削除
	register_rest_route(
		'product-link-maker/v1',
		'/rakuten-cache/',
		array(
			'methods'             => 'POST',
			'callback'            => 'plm_rest_clear_rakuten_cache',
			'permission_callback' => '__return_true',
		)
	);

	// 全キャッシュ削除
	register_rest_route(
		'product-link-maker/v1',
		'/clear-cache/',
		array(
			'methods'             => 'POST',
			'callback'            => 'plm_rest_clear_all_cache',
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);

	// エラーログクリア
	register_rest_route(
		'product-link-maker/v1',
		'/clear-error-logs/',
		array(
			'methods'             => 'POST',
			'callback'            => function() {
				plm_clear_error_logs();
				return rest_ensure_response(
					array(
						'success' => true,
						'message' => __( 'エラーログをクリアしました。', 'product-link-maker' ),
					)
				);
			},
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);

	// テストエラーログ追加
	register_rest_route(
		'product-link-maker/v1',
		'/test-error-log/',
		array(
			'methods'             => 'POST',
			'callback'            => function() {
				$result = plm_log_error(
					'test_error',
					'これはテストエラーです。エラーログ機能が正常に動作しています。',
					array(
						'post_id'   => 0,
						'post_title' => 'テスト',
						'item_id'   => 'test-12345',
					)
				);
				
				// 最新のログを取得して確認
				$logs = plm_get_error_logs();
				
				return rest_ensure_response(
					array(
						'success' => $result,
						'message' => $result 
							? __( 'テストログを追加しました。', 'product-link-maker' )
							: __( 'テストログの追加に失敗しました。', 'product-link-maker' ),
						'log_count' => count( $logs ),
					)
				);
			},
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);

	// データベース直接確認
	register_rest_route(
		'product-link-maker/v1',
		'/check-db/',
		array(
			'methods'             => 'POST',
			'callback'            => function() {
				global $wpdb;
				
				// データベースから直接取得
				$db_value = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
						'plm_error_logs'
					)
				);
				
				$db_exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name = %s",
						'plm_error_logs'
					)
				);
				
				$unserialized = $db_value ? maybe_unserialize( $db_value ) : null;
				
				// get_option経由でも取得
				$option_value = get_option( 'plm_error_logs', null );
				
				return rest_ensure_response(
					array(
						'success' => true,
						'db_exists' => (bool) $db_exists,
						'db_value_length' => $db_value ? strlen( $db_value ) : 0,
						'db_unserialized_count' => is_array( $unserialized ) ? count( $unserialized ) : 0,
						'get_option_count' => is_array( $option_value ) ? count( $option_value ) : 0,
						'db_raw' => substr( $db_value, 0, 500 ),
						'unserialized_sample' => is_array( $unserialized ) && ! empty( $unserialized ) 
							? array_slice( $unserialized, 0, 2 ) 
							: $unserialized,
					)
				);
			},
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);

	// 強制的にログを追加
	register_rest_route(
		'product-link-maker/v1',
		'/force-insert/',
		array(
			'methods'             => 'POST',
			'callback'            => function() {
				global $wpdb;
				
				$log_entry = array(
					'timestamp'     => current_time( 'mysql' ),
					'error_type'    => 'force_test',
					'error_message' => '強制追加テスト - ' . time(),
					'post_id'       => 999,
					'post_title'    => '強制テスト投稿',
					'item_id'       => 'force-test-' . time(),
					'keyword'       => null,
				);
				
				// まず現在の値を取得
				$current = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
						'plm_error_logs'
					)
				);
				
				$logs = $current ? maybe_unserialize( $current ) : array();
				if ( ! is_array( $logs ) ) {
					$logs = array();
				}
				
				// 新しいログを追加
				array_unshift( $logs, $log_entry );
				$logs = array_slice( $logs, 0, 100 );
				
				$serialized = maybe_serialize( $logs );
				
				// データベースに直接書き込み
				if ( $current === false || $current === null ) {
					// オプションが存在しない場合は INSERT
					$result = $wpdb->insert(
						$wpdb->options,
						array(
							'option_name'  => 'plm_error_logs',
							'option_value' => $serialized,
							'autoload'     => 'no',
						),
						array( '%s', '%s', '%s' )
					);
				} else {
					// 既存の場合は UPDATE
					$result = $wpdb->update(
						$wpdb->options,
						array( 'option_value' => $serialized ),
						array( 'option_name' => 'plm_error_logs' ),
						array( '%s' ),
						array( '%s' )
					);
				}
				
				// キャッシュクリア
				wp_cache_delete( 'plm_error_logs', 'options' );
				
				// 確認
				$verify = plm_get_error_logs();
				
				return rest_ensure_response(
					array(
						'success' => $result !== false,
						'db_result' => $result,
						'log_count' => count( $logs ),
						'verify_count' => count( $verify ),
						'latest' => ! empty( $verify ) ? $verify[0] : null,
					)
				);
			},
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		)
	);

	// クライアント側からのエラーログ記録
	register_rest_route(
		'product-link-maker/v1',
		'/log-client-error/',
		array(
			'methods'             => 'POST',
			'callback'            => function( $request ) {
				$error_type    = $request->get_param( 'error_type' );
				$error_message = $request->get_param( 'error_message' );
				$post_id       = $request->get_param( 'post_id' );
				$item_id       = $request->get_param( 'item_id' );
				$keyword       = $request->get_param( 'keyword' );
				
			// レート制限以外のエラーをログに記録
			if ( 'rate_limit' !== $error_type ) {
				$post_title = '';
				if ( $post_id ) {
					$post = get_post( $post_id );
					if ( $post ) {
						$post_title = $post->post_title;
					}
				}
				
				$result = plm_log_error(
					$error_type,
					$error_message,
					array(
						'post_id'    => $post_id,
						'post_title' => $post_title,
						'item_id'    => $item_id,
						'keyword'    => $keyword,
					)
				);
				}
				
				return rest_ensure_response(
					array(
						'success' => false,
						'message' => 'Rate limit error not logged',
					)
				);
			},
			'permission_callback' => '__return_true',
			'args'                => array(
				'error_type' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'error_message' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'post_id' => array(
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
				'item_id' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'keyword' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'plm_register_rest_routes' );

/**
 * エラーキャッシュをクリアするAJAXハンドラー
 */
function plm_ajax_clear_error_cache() {
	check_ajax_referer( 'plm_clear_error_cache', 'nonce' );
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Permission denied' );
	}
	
	global $wpdb;
	
	// エラーがキャッシュされている可能性のあるトランジェントを削除
	// rate_limit以外のエラーは本来キャッシュされないはずだが、念のため全てクリア
	$deleted = $wpdb->query(
		"DELETE FROM {$wpdb->options} 
		WHERE option_name LIKE '_transient_rakuten_item_%' 
		OR option_name LIKE '_transient_timeout_rakuten_item_%'"
	);
	
	wp_send_json_success( 
		sprintf( '%d 件のキャッシュをクリアしました。', $deleted ),
		200
	);
}
add_action( 'wp_ajax_plm_clear_error_cache', 'plm_ajax_clear_error_cache' );
