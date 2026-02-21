<?php
/**
 * 楽天API通信クラス
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PLM_Rakuten_API
 */
class PLM_Rakuten_API {

	/**
	 * APIリクエストURLを生成
	 *
	 * @param string      $app_id アプリケーションID
	 * @param string      $affiliate_id アフィリエイトID
	 * @param string|null $item_id 商品ID
	 * @param string|null $keyword キーワード
	 * @return string リクエストURL
	 */
	public static function build_api_url( $app_id, $affiliate_id, $item_id = null, $keyword = null ) {
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
	public static function create_error_response( $error_code, $error_message ) {
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
	public static function fetch_item( $app_id, $affiliate_id, $item_id = null, $keyword = null ) {
		// パラメータ検証
		if ( empty( $app_id ) || empty( $affiliate_id ) ) {
			return self::create_error_response(
				'missing_credentials',
				__( '楽天アプリケーションIDまたはアフィリエイトIDがありません。', 'product-link-maker' )
			);
		}

		if ( empty( $item_id ) && empty( $keyword ) ) {
			return self::create_error_response(
				'missing_params',
				__( '商品IDまたはキーワードを指定してください。', 'product-link-maker' )
			);
		}

		// APIリクエスト
		$request_url = self::build_api_url( $app_id, $affiliate_id, $item_id, $keyword );
		$response    = wp_remote_get(
			$request_url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
			)
		);

		// エラーハンドリング
		if ( is_wp_error( $response ) ) {
			return self::create_error_response(
				'request_failed',
				/* translators: %s: エラーメッセージ */
				sprintf( __( '楽天APIへの接続に失敗しました: %s', 'product-link-maker' ), $response->get_error_message() )
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$body          = wp_remote_retrieve_body( $response );

		// レート制限エラー
		if ( 429 === $response_code ) {
			return self::create_error_response(
				'rate_limit',
				__( 'APIリクエスト制限に達しました。しばらく待ってから再度お試しください。', 'product-link-maker' )
			);
		}

		// その他のHTTPエラー
		if ( 200 !== $response_code || empty( $body ) ) {
			return self::create_error_response(
				'api_error',
				/* translators: %d: HTTPステータスコード */
				sprintf( __( '楽天APIからデータを取得できませんでした。(HTTP %d)', 'product-link-maker' ), $response_code )
			);
		}

		return $body;
	}
}
