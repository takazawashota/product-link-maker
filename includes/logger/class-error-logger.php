<?php
/**
 * エラーログ管理クラス
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PLM_Error_Logger
 */
class PLM_Error_Logger {

	/**
	 * エラーログを記録
	 *
	 * @param string $error_type エラータイプ
	 * @param string $error_message エラーメッセージ
	 * @param array  $context コンテキスト情報
	 * @return bool 成功したかどうか
	 */
	public static function log( $error_type, $error_message, $context = array() ) {
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
	public static function get_logs() {
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
	 *
	 * @return bool 成功したかどうか
	 */
	public static function clear() {
		return delete_option( 'plm_error_logs' );
	}
}
