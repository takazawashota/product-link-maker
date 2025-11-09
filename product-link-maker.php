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
/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function create_block_rakuten_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'create_block_rakuten_block_init' );

// カスタムブロックカテゴリーを追加
function product_link_maker_block_categories( $categories ) {
	// ウィジェットカテゴリーの位置を探す
	$widget_index = 0;
	foreach ( $categories as $index => $category ) {
		if ( isset( $category['slug'] ) && $category['slug'] === 'widgets' ) {
			$widget_index = $index;
			break;
		}
	}
	
	// ウィジェットの直前に挿入
	$custom_category = [
		'slug'  => 'product-link-maker',
		'title' => 'Product Link Maker',
		'icon'  => 'cart',
	];
	
	array_splice( $categories, $widget_index, 0, [ $custom_category ] );
	
	return $categories;
}
add_filter( 'block_categories_all', 'product_link_maker_block_categories' );

// 以下独自のコード

// アフィリエイト設定ページを追加
add_action('admin_menu', 'affiliate_settings_add_admin_menu');
add_action('admin_init', 'affiliate_settings_init');
function affiliate_settings_add_admin_menu() {
    add_options_page(
        'Product Link Maker',      // ページタイトル
        'Product Link Maker',      // メニュータイトル
        'manage_options',          // 必要な権限
        'product-link-maker',      // メニューのスラッグ
        'affiliate_settings_options_page'  // 表示する関数
    );
}
function affiliate_settings_init() {
    register_setting('affiliateSettings', 'affiliate_settings');
}
function affiliate_settings_options_page() {
    $options = get_option('affiliate_settings');
    ?>
    <div class="wrap">
        <h1>
            Product Link Maker
        </h1>
        <p class="description" style="margin-bottom: 20px;">各アフィリエイトサービスのIDを設定してください。設定したサービスのボタンが商品リンクに表示されます。</p>
        
        <form method="post" action="options.php">
            <?php settings_fields('affiliateSettings'); ?>

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

            <?php submit_button('設定を保存', 'primary large'); ?>
        </form>
    </div>
    <?php
}


// エディター用のスクリプトを登録
function myplugin_enqueue_editor_assets() {
    wp_enqueue_script(
        'my-custom-block-js',
        plugins_url('build/index.js', __FILE__),
        ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'],
        filemtime(plugin_dir_path(__FILE__) . 'build/index.js')
    );

    // 設定をJSに渡す
    $affiliate_settings = get_option('affiliate_settings', []);
    wp_localize_script('my-custom-block-js', 'MyAffiliateSettings', [
        'amazon'  => !empty($affiliate_settings['amazon_tracking_id']),
        'rakuten' => !empty($affiliate_settings['rakuten_affiliate_id']),
        'yahoo'   => !empty($affiliate_settings['yahoo_sid']) && !empty($affiliate_settings['yahoo_pid']),
        'mercari' => !empty($affiliate_settings['mercari_id']),
        'dmm'     => !empty($affiliate_settings['dmm_id']),
    ]);
}
add_action('enqueue_block_editor_assets', 'myplugin_enqueue_editor_assets');





// 楽天APIリクエストURLを生成し、商品情報を取得する最小限のサンプル
function get_rakuten_item_json($rakuten_application_id, $rakuten_affiliate_id, $item_id = null, $keyword = null) {
  if (empty($rakuten_application_id) || empty($rakuten_affiliate_id)) {
    return json_encode(['error' => 'missing_credentials', 'error_description' => '楽天アプリケーションIDまたはアフィリエイトIDがありません。']);
  }
  if (empty($item_id) && empty($keyword)) {
    return json_encode(['error' => 'missing_params', 'error_description' => '商品IDまたはキーワードを指定してください。']);
  }

  $itemCode = $item_id ? '&itemCode=' . urlencode($item_id) : '';
  $searchkw = (!$item_id && $keyword) ? '&keyword=' . urlencode($keyword) : '';
  $request_url = 'https://app.rakuten.co.jp/services/api/IchibaItem/Search/20170706?applicationId='
    . $rakuten_application_id
    . '&affiliateId=' . $rakuten_affiliate_id
    . '&imageFlag=1&hits=1'
    . $searchkw
    . $itemCode;

  $response = wp_remote_get($request_url, ['sslverify' => true, 'timeout' => 15]);
  
  if (is_wp_error($response)) {
    return json_encode(['error' => 'request_failed', 'error_description' => '楽天APIへの接続に失敗しました: ' . $response->get_error_message()]);
  }
  
  $response_code = wp_remote_retrieve_response_code($response);
  $body = wp_remote_retrieve_body($response);
  
  // 429エラー（Too Many Requests）の場合
  if ($response_code === 429) {
    return json_encode(['error' => 'rate_limit', 'error_description' => 'APIリクエスト制限に達しました。しばらく待ってから再度お試しください。']);
  }
  
  // その他のエラーレスポンス
  if ($response_code !== 200 || empty($body)) {
    return json_encode(['error' => 'api_error', 'error_description' => '楽天APIからデータを取得できませんでした。(HTTP ' . $response_code . ')']);
  }
  
  return $body; // JSON文字列
}


// キャッシュ付き楽天API取得関数を追加
function get_rakuten_item_json_with_cache($rakuten_application_id, $rakuten_affiliate_id, $item_id = null, $keyword = null, $no = null) {
    // 商品番号(no)があればキーワードとして優先
    $cache_key = 'rakuten_item_' . md5($rakuten_application_id . $rakuten_affiliate_id . $item_id . $keyword . $no);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }
    // noがあればキーワードとして使う
    $use_kw = $no ? $no : $keyword;
    $json = get_rakuten_item_json($rakuten_application_id, $rakuten_affiliate_id, $item_id, $use_kw);
    
    // レスポンスをチェック
    $response_data = json_decode($json, true);
    if (isset($response_data['error'])) {
        // エラーの種類によってキャッシュ期間を変える
        if ($response_data['error'] === 'rate_limit') {
            // レート制限エラーの場合は1時間キャッシュ
            set_transient($cache_key, $json, 60 * 60);
        } else {
            // その他のエラーは5分キャッシュ
            set_transient($cache_key, $json, 5 * 60);
        }
    } else {
        // 成功した場合は24時間キャッシュ
        set_transient($cache_key, $json, 24 * 60 * 60);
    }
    
    return $json;
}


// REST APIエンドポイントを登録
add_action('rest_api_init', function () {
    register_rest_route('product-link-maker/v1', '/rakuten/', [
        'methods'  => 'GET',
        'callback' => function ($request) {
            // 必要なパラメータを取得
            $id = $request->get_param('id');
            $kw = $request->get_param('kw');
            $no = $request->get_param('no');
            $affiliate_settings = get_option('affiliate_settings', []);
            $amazon_tracking_id = $affiliate_settings['amazon_tracking_id'] ?? '';
            $rakuten_application_id = $affiliate_settings['rakuten_app_id'] ?? '';
            $rakuten_affiliate_id = $affiliate_settings['rakuten_affiliate_id'] ?? '';
            $vc_sid = $affiliate_settings['yahoo_sid'] ?? '';
            $vc_pid = $affiliate_settings['yahoo_pid'] ?? '';
            $mercari_ambassador_id = $affiliate_settings['mercari_id'] ?? '';
            $dmm_affiliate_id = $affiliate_settings['dmm_id'] ?? '';
            // キャッシュ付き関数を使う（noがあればキーワードとして優先）
            $json = get_rakuten_item_json_with_cache($rakuten_application_id, $rakuten_affiliate_id, $id, $kw, $no);
            $result = json_decode($json, true);
            // 追加でID情報も返す
            $result['affiliate_ids'] = [
                'rakuten_app_id' => $rakuten_application_id,
                'rakuten_affiliate_id' => $rakuten_affiliate_id,
                'yahoo_sid' => $vc_sid,
                'yahoo_pid' => $vc_pid,
                'mercari_id' => $mercari_ambassador_id,
                'dmm_id' => $dmm_affiliate_id,
            ];
            return rest_ensure_response($result);
        },
        'permission_callback' => '__return_true',
    ]);
});

// キャッシュ削除用REST APIエンドポイント
add_action('rest_api_init', function () {
    register_rest_route('product-link-maker/v1', '/rakuten-cache/', [
        'methods'  => 'POST',
        'callback' => function ($request) {
            $id = $request->get_param('id');
            $kw = $request->get_param('kw');
            $no = $request->get_param('no');
            $affiliate_settings = get_option('affiliate_settings', []);
            $rakuten_application_id = $affiliate_settings['rakuten_app_id'];
            $rakuten_affiliate_id = $affiliate_settings['rakuten_affiliate_id'];
            $cache_key = 'rakuten_item_' . md5($rakuten_application_id . $rakuten_affiliate_id . $id . $kw . $no);
            delete_transient($cache_key);
            return rest_ensure_response(['deleted' => true]);
        },
        'permission_callback' => '__return_true',
    ]);
});



// //楽天アフィリエイト検索用のURL生成
// if ( !function_exists( 'get_rakuten_affiliate_search_url' ) ):
// function get_rakuten_affiliate_search_url($keyword, $rakuten_affiliate_id, $ng_keywords = null){
//   $nitem = null;
//   if (!empty($ng_keywords)) {
//     $nitem = '%3Fnitem='.implode('%2B', $ng_keywords);
//   }
//   $decoded_url = 'https%3A%2F%2Fsearch.rakuten.co.jp%2Fsearch%2Fmall%2F'.urlencode($keyword).'%2F'.$nitem;
//   return 'https://hb.afl.rakuten.co.jp/hgc/'.trim($rakuten_affiliate_id).'/?pc='.$decoded_url.'&m='.$decoded_url;
// }
// endif;