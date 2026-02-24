<?php
/**
 * REST API管理クラス
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PLM_REST_API
 */
class PLM_REST_API {

	/**
	 * 初期化
	 */
	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'wp_ajax_plm_clear_error_cache', array( __CLASS__, 'ajax_clear_error_cache' ) );
	}

	/**
	 * REST APIエンドポイントを登録
	 */
	public static function register_routes() {
		// 楽天商品情報取得
		register_rest_route(
			'product-link-maker/v1',
			'/rakuten/',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_rakuten_item' ),
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
				'callback'            => array( __CLASS__, 'clear_rakuten_cache' ),
				'permission_callback' => '__return_true',
			)
		);

		// 全キャッシュ削除
		register_rest_route(
			'product-link-maker/v1',
			'/clear-cache/',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'clear_all_cache' ),
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
				'callback'            => array( __CLASS__, 'clear_error_logs' ),
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
				'callback'            => array( __CLASS__, 'test_error_log' ),
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
				'callback'            => array( __CLASS__, 'check_db' ),
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
				'callback'            => array( __CLASS__, 'force_insert' ),
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
				'callback'            => array( __CLASS__, 'log_client_error' ),
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

	/**
	 * 楽天商品情報を取得するREST APIハンドラ
	 *
	 * @param WP_REST_Request $request リクエストオブジェクト
	 * @return WP_REST_Response レスポンス
	 */
	public static function get_rakuten_item( $request ) {
		$settings = get_option( PLM_OPTION_NAME, array() );

		$item_id = $request->get_param( 'id' );
		$keyword = $request->get_param( 'kw' );
		$no      = $request->get_param( 'no' );
		$post_id = $request->get_param( 'post_id' );

		$json = PLM_Cache_Manager::get_rakuten_item_cached(
			$settings['rakuten_app_id'] ?? '',
			$settings['rakuten_affiliate_id'] ?? '',
			$item_id,
			$keyword,
			$no
		);

		$result = json_decode( $json, true );

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
	public static function clear_rakuten_cache( $request ) {
		$settings  = get_option( PLM_OPTION_NAME, array() );
		$cache_key = PLM_Cache_Manager::generate_key(
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
	public static function clear_all_cache() {
		$deleted = PLM_Cache_Manager::clear_all();

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => sprintf( __( '%d 件のキャッシュを削除しました。', 'product-link-maker' ), $deleted ),
			)
		);
	}

	/**
	 * エラーログをクリアするREST APIハンドラ
	 *
	 * @return WP_REST_Response レスポンス
	 */
	public static function clear_error_logs() {
		PLM_Error_Logger::clear();
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'エラーログをクリアしました。', 'product-link-maker' ),
			)
		);
	}

	/**
	 * テストエラーログを追加するREST APIハンドラ
	 *
	 * @return WP_REST_Response レスポンス
	 */
	public static function test_error_log() {
		$result = PLM_Error_Logger::log(
			'test_error',
			'これはテストエラーです。エラーログ機能が正常に動作しています。',
			array(
				'post_id'    => null,
				'post_title' => null,
				'item_id'    => 'test-12345',
			)
		);
		
		// 最新のログを取得して確認
		$logs = PLM_Error_Logger::get_logs();
		
		return rest_ensure_response(
			array(
				'success'   => $result,
				'message'   => $result 
					? __( 'テストログを追加しました。', 'product-link-maker' )
					: __( 'テストログの追加に失敗しました。', 'product-link-maker' ),
				'log_count' => count( $logs ),
			)
		);
	}

	/**
	 * データベース直接確認するREST APIハンドラ
	 *
	 * @return WP_REST_Response レスポンス
	 */
	public static function check_db() {
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
				'success'               => true,
				'db_exists'             => (bool) $db_exists,
				'db_value_length'       => $db_value ? strlen( $db_value ) : 0,
				'db_unserialized_count' => is_array( $unserialized ) ? count( $unserialized ) : 0,
				'get_option_count'      => is_array( $option_value ) ? count( $option_value ) : 0,
				'db_raw'                => substr( $db_value, 0, 500 ),
				'unserialized_sample'   => is_array( $unserialized ) && ! empty( $unserialized ) 
					? array_slice( $unserialized, 0, 2 ) 
					: $unserialized,
			)
		);
	}

	/**
	 * 強制的にログを追加するREST APIハンドラ
	 *
	 * @return WP_REST_Response レスポンス
	 */
	public static function force_insert() {
		global $wpdb;
		
		$log_entry = array(
			'timestamp'     => current_time( 'mysql' ),
			'error_type'    => 'force_test',
			'error_message' => '強制追加テスト - ' . time(),
			'post_id'       => null,
			'post_title'    => null,
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
		$verify = PLM_Error_Logger::get_logs();
		
		return rest_ensure_response(
			array(
				'success'      => $result !== false,
				'db_result'    => $result,
				'log_count'    => count( $logs ),
				'verify_count' => count( $verify ),
				'latest'       => ! empty( $verify ) ? $verify[0] : null,
			)
		);
	}

	/**
	 * クライアント側からのエラーログ記録
	 *
	 * @param WP_REST_Request $request リクエストオブジェクト
	 * @return WP_REST_Response レスポンス
	 */
	public static function log_client_error( $request ) {
		$error_type    = $request->get_param( 'error_type' );
		$error_message = $request->get_param( 'error_message' );
		$post_id       = $request->get_param( 'post_id' );
		$item_id       = $request->get_param( 'item_id' );
		$keyword       = $request->get_param( 'keyword' );
		
		// post_idを検証（有効な投稿IDでない場合はnullにする）
		$post_id = ( $post_id && is_numeric( $post_id ) && $post_id > 0 ) ? (int) $post_id : null;
		
		// レート制限以外のエラーをログに記録
		if ( 'rate_limit' !== $error_type ) {
			$post_title = '';
			if ( $post_id ) {
				$post = get_post( $post_id );
				if ( $post ) {
					$post_title = $post->post_title;
				} else {
					// 投稿が存在しない場合はpost_idをnullに
					$post_id = null;
				}
			}
			
			$result = PLM_Error_Logger::log(
				$error_type,
				$error_message,
				array(
					'post_id'    => $post_id,
					'post_title' => $post_title,
					'item_id'    => $item_id,
					'keyword'    => $keyword,
				)
			);

			return rest_ensure_response(
				array(
					'success' => $result,
					'message' => 'Error logged successfully',
				)
			);
		}
		
		return rest_ensure_response(
			array(
				'success' => false,
				'message' => 'Rate limit error not logged',
			)
		);
	}

	/**
	 * エラーキャッシュをクリアするAJAXハンドラー
	 */
	public static function ajax_clear_error_cache() {
		check_ajax_referer( 'plm_clear_error_cache', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}
		
		$deleted = PLM_Cache_Manager::clear_error_cache();
		
		wp_send_json_success( 
			sprintf( '%d 件のキャッシュをクリアしました。', $deleted ),
			200
		);
	}
}
