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

	$sanitized = array();
	$text_fields = array(
		'amazon_tracking_id', 'amazon_access_key', 'amazon_secret_key',
		'rakuten_app_id', 'rakuten_affiliate_id',
		'yahoo_sid', 'yahoo_pid',
		'mercari_id', 'dmm_id'
	);

	foreach ( $text_fields as $field ) {
		$sanitized[ $field ] = isset( $settings[ $field ] ) ? sanitize_text_field( $settings[ $field ] ) : '';
	}

	// 数値フィールド
	$sanitized['cache_success_hours']     = isset( $settings['cache_success_hours'] ) ? absint( $settings['cache_success_hours'] ) : PLM_CACHE_SUCCESS_HOURS;
	$sanitized['cache_ratelimit_minutes'] = isset( $settings['cache_ratelimit_minutes'] ) ? absint( $settings['cache_ratelimit_minutes'] ) : PLM_CACHE_RATELIMIT_MINUTES;
	$sanitized['cache_error_minutes']     = isset( $settings['cache_error_minutes'] ) ? absint( $settings['cache_error_minutes'] ) : PLM_CACHE_ERROR_MINUTES;

	return $sanitized;
}
/**
 * 設定ページを表示
 */
function plm_render_settings_page() {
	$options = get_option( PLM_OPTION_NAME, array() );
    ?>
    <div class="wrap">
        <h1>
            Product Link Maker
        </h1>
        <p class="description" style="margin-bottom: 20px;">各アフィリエイトサービスのIDを設定してください。設定したサービスのボタンが商品リンクに表示されます。</p>
        
        <form method="post" action="options.php">
            <?php settings_fields( 'plm_settings_group' ); ?>

            <style>
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
                .plm-card .form-table td {
                    padding: 12px 0;
                }
                .plm-card .form-table input[type="text"] {
                    width: 100%;
                    max-width: 500px;
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

            <!-- Amazon -->
            <div class="plm-card">
                <h2>
                    <span class="dashicons dashicons-cart" style="color: #ff9900;"></span>
                    Amazon
                    <?php if (!empty($options['amazon_tracking_id'])): ?>
                        <span class="plm-status-badge plm-status-active">設定済み</span>
                    <?php else: ?>
                        <span class="plm-status-badge plm-status-inactive">未設定</span>
                    <?php endif; ?>
                </h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">トラッキングID</th>
                        <td>
                            <input type="text" name="affiliate_settings[amazon_tracking_id]" 
                                   value="<?= esc_attr($options['amazon_tracking_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: yourname-22" />
                            <p class="description">Amazon アソシエイトのトラッキングIDを入力してください。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">アクセスキーID</th>
                        <td>
                            <input type="text" name="affiliate_settings[amazon_access_key]" 
                                   value="<?= esc_attr($options['amazon_access_key'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="（オプション）" />
                            <p class="description">Product Advertising API用（オプション）</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">シークレットキー</th>
                        <td>
                            <input type="password" name="affiliate_settings[amazon_secret_key]" 
                                   value="<?= esc_attr($options['amazon_secret_key'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="（オプション）" />
                            <p class="description">Product Advertising API用（オプション）</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 楽天 -->
            <div class="plm-card">
                <h2>
                    <span class="dashicons dashicons-store" style="color: #bf0000;"></span>
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
                            <p class="description">楽天ウェブサービスで取得したアプリケーションIDを入力してください。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">楽天アフィリエイトID</th>
                        <td>
                            <input type="text" name="affiliate_settings[rakuten_affiliate_id]" 
                                   value="<?= esc_attr($options['rakuten_affiliate_id'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: 12345678.90123456.12345678.90123456" />
                            <p class="description">楽天アフィリエイトIDを入力してください。</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Yahoo!ショッピング -->
            <div class="plm-card">
                <h2>
                    <span class="dashicons dashicons-tag" style="color: #ff0033;"></span>
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
                            <p class="description">バリューコマースのSID（サイトID）を入力してください。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">バリューコマースPID</th>
                        <td>
                            <input type="text" name="affiliate_settings[yahoo_pid]" 
                                   value="<?= esc_attr($options['yahoo_pid'] ?? '') ?>" 
                                   class="regular-text" 
                                   placeholder="例: 890123456" />
                            <p class="description">バリューコマースのPID（プロモーションID）を入力してください。</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- メルカリ -->
            <div class="plm-card">
                <h2>
                    <span class="dashicons dashicons-smartphone" style="color: #4dc9ff;"></span>
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
                            <p class="description">メルカリアンバサダープログラムのIDを入力してください。</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- DMM -->
            <div class="plm-card">
                <h2>
                    <span class="dashicons dashicons-video-alt3" style="color: #00bcd4;"></span>
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
                            <p class="description">DMMアフィリエイトIDを入力してください。</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- キャッシュ設定 -->
            <div class="plm-card">
                <h2>
                    <span class="dashicons dashicons-database" style="color: #9b59b6;"></span>
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
                                <div style="flex: 1; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                        <span class="dashicons dashicons-database-view" style="font-size: 28px; width: 28px; height: 28px;"></span>
                                        <span style="font-size: 13px; opacity: 0.9; font-weight: 500;">キャッシュ件数</span>
                                    </div>
                                    <div style="font-size: 32px; font-weight: bold; line-height: 1;">
                                        <?= number_format($cache_count) ?>
                                    </div>
                                    <div style="font-size: 12px; opacity: 0.8; margin-top: 4px;">件</div>
                                </div>
                                <div style="flex: 1; padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 8px; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
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
                                   class="small-text" /> 分
                            <p class="description">その他エラー時のキャッシュ保存時間（1〜60分）デフォルト: 5分</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">キャッシュ削除</th>
                        <td>
                            <button type="button" id="plm-clear-cache" class="button" style="
                                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

            <?php submit_button('設定を保存', 'primary large'); ?>
        </form>

        <script>
        jQuery(document).ready(function($) {
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
 * 楽天商品情報を取得するREST APIハンドラ
 *
 * @param WP_REST_Request $request リクエストオブジェクト
 * @return WP_REST_Response レスポンス
 */
function plm_rest_get_rakuten_item( $request ) {
	$settings = plm_get_affiliate_settings();

	$json = plm_get_rakuten_item_cached(
		$settings['rakuten_app_id'] ?? '',
		$settings['rakuten_affiliate_id'] ?? '',
		$request->get_param( 'id' ),
		$request->get_param( 'kw' ),
		$request->get_param( 'no' )
	);

	$result                  = json_decode( $json, true );
	$result['affiliate_ids'] = array(
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
		'_transient_rakuten_item_%',
		'_transient_timeout_rakuten_item_%',
		'_transient_amazon_item_%',
		'_transient_timeout_amazon_item_%',
	);

	$placeholders = implode( ' OR ', array_fill( 0, count( $patterns ), 'option_name LIKE %s' ) );
	$query        = "DELETE FROM {$wpdb->options} WHERE {$placeholders}";

	$escaped_patterns = array_map( array( $wpdb, 'esc_like' ), $patterns );
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->query( $wpdb->prepare( $query, $escaped_patterns ) );

	return rest_ensure_response(
		array(
			'success' => true,
			'message' => __( 'すべてのキャッシュを削除しました。', 'product-link-maker' ),
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
}
add_action( 'rest_api_init', 'plm_register_rest_routes' );
