<?php
/**
 * キャッシュ管理クラス
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PLM_Cache_Manager
 */
class PLM_Cache_Manager {

	/**
	 * キャッシュキーを生成
	 *
	 * @param string $prefix プレフィックス
	 * @param array  $params パラメータ配列
	 * @return string キャッシュキー
	 */
	public static function generate_key( $prefix, $params ) {
		return $prefix . '_' . md5( wp_json_encode( $params ) );
	}

	/**
	 * エラータイプに応じたキャッシュ期間を取得
	 *
	 * @param string $error_type エラータイプ
	 * @param array  $cache_settings キャッシュ設定
	 * @return int キャッシュ期間（秒）
	 */
	public static function get_expiration( $error_type, $cache_settings ) {
		if ( 'rate_limit' === $error_type ) {
			return $cache_settings['ratelimit_minutes'] * MINUTE_IN_SECONDS;
		}

		// 'success'のみ（それ以外はcache_dataで早期リターンされるため到達しない）
		return $cache_settings['success_hours'] * HOUR_IN_SECONDS;
	}

	/**
	 * データをキャッシュに保存
	 *
	 * @param string $cache_key キャッシュキー
	 * @param string $data データ
	 * @param array  $cache_settings キャッシュ設定
	 */
	public static function cache_data( $cache_key, $data, $cache_settings ) {
		$response_data = json_decode( $data, true );
		$error_type    = 'success';

		if ( isset( $response_data['error'] ) ) {
			$error_type = $response_data['error'];
		}

		// レート制限以外のエラーはキャッシュしない（一時的な障害の可能性があるため）
		if ( 'success' !== $error_type && 'rate_limit' !== $error_type ) {
			return;
		}

		$expiration = self::get_expiration( $error_type, $cache_settings );
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
	public static function get_rakuten_item_cached( $app_id, $affiliate_id, $item_id = null, $keyword = null, $no = null ) {
		// 商品番号があればキーワードとして優先
		$use_keyword = ! empty( $no ) ? $no : $keyword;

		// キャッシュキーを生成
		$cache_key = self::generate_key(
			'rakuten_item',
			array( $app_id, $affiliate_id, $item_id, $use_keyword )
		);

		// キャッシュチェック
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// API呼び出し
		$json = PLM_Rakuten_API::fetch_item( $app_id, $affiliate_id, $item_id, $use_keyword );

		// キャッシュに保存
		$settings       = get_option( PLM_OPTION_NAME, array() );
		$cache_settings = array(
			'success_hours'     => isset( $settings['cache_success_hours'] ) ? intval( $settings['cache_success_hours'] ) : PLM_CACHE_SUCCESS_HOURS,
			'ratelimit_minutes' => isset( $settings['cache_ratelimit_minutes'] ) ? intval( $settings['cache_ratelimit_minutes'] ) : PLM_CACHE_RATELIMIT_MINUTES,
			'error_minutes'     => isset( $settings['cache_error_minutes'] ) ? intval( $settings['cache_error_minutes'] ) : PLM_CACHE_ERROR_MINUTES,
		);
		self::cache_data( $cache_key, $json, $cache_settings );

		return $json;
	}

	/**
	 * すべてのキャッシュを削除
	 *
	 * @return int 削除されたキャッシュの数
	 */
	public static function clear_all() {
		global $wpdb;

		$patterns = array(
			$wpdb->esc_like( '_transient_rakuten_item_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_rakuten_item_' ) . '%',
			$wpdb->esc_like( '_transient_amazon_item_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_amazon_item_' ) . '%',
		);

		return $wpdb->query(
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
	}

	/**
	 * エラーキャッシュをクリア
	 *
	 * @return int 削除されたキャッシュの数
	 */
	public static function clear_error_cache() {
		global $wpdb;

		return $wpdb->query(
			"DELETE FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_rakuten_item_%' 
			OR option_name LIKE '_transient_timeout_rakuten_item_%'"
		);
	}
}
