<?php
/**
 * 管理画面設定クラス
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PLM_Settings
 */
class PLM_Settings {

	/**
	 * 初期化
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
	}

	/**
	 * 設定ページを追加
	 */
	public static function add_settings_page() {
		add_options_page(
			__( 'Product Link Maker', 'product-link-maker' ),
			__( 'Product Link Maker', 'product-link-maker' ),
			'manage_options',
			'product-link-maker',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * 設定を登録
	 */
	public static function register_settings() {
		register_setting(
			'plm_settings_group',
			PLM_OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * 設定値をサニタイズ
	 *
	 * @param array $settings 設定値
	 * @return array サニタイズされた設定値
	 */
	public static function sanitize_settings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return array();
		}

		// 既存の設定を取得してマージ（部分的な更新に対応）
		$existing  = get_option( PLM_OPTION_NAME, array() );
		$sanitized = $existing;

		$text_fields = array(
			'amazon_tracking_id',
			'rakuten_app_id',
			'rakuten_affiliate_id',
			'yahoo_sid',
			'yahoo_pid',
			'mercari_id',
			'dmm_id',
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
		// cache_error_minutesは使用されないため処理不要（エラーはキャッシュされない）

		return $sanitized;
	}

	/**
	 * 設定ページをレンダリング
	 */
	public static function render_settings_page() {
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
		
		// 現在のタブを取得（デフォルトは api-keys）
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'api-keys';
		
		// ページをレンダリング
		include PLM_PLUGIN_DIR . 'includes/admin/views/settings-page.php';
	}

	/**
	 * エディター用アセットを読み込み
	 */
	public static function enqueue_editor_assets() {
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
		$settings = get_option( PLM_OPTION_NAME, array() );
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
				'amazonTrackingId'   => $settings['amazon_tracking_id'] ?? '',
				'rakutenAffiliateId' => $settings['rakuten_affiliate_id'] ?? '',
				'yahooSid'           => $settings['yahoo_sid'] ?? '',
				'yahooPid'           => $settings['yahoo_pid'] ?? '',
				'mercariId'          => $settings['mercari_id'] ?? '',
				'dmmId'              => $settings['dmm_id'] ?? '',
			)
		);
	}
}
