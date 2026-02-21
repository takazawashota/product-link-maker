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

// クラスファイルを読み込み
require_once PLM_PLUGIN_DIR . 'includes/api/class-rakuten-api.php';
require_once PLM_PLUGIN_DIR . 'includes/cache/class-cache-manager.php';
require_once PLM_PLUGIN_DIR . 'includes/logger/class-error-logger.php';
require_once PLM_PLUGIN_DIR . 'includes/rest/class-rest-api.php';
require_once PLM_PLUGIN_DIR . 'includes/admin/class-settings.php';

// 各クラスを初期化
PLM_REST_API::init();
PLM_Settings::init();

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
