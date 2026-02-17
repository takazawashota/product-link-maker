<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

/**
 * カスタムボタンをレンダリングするヘルパー関数
 * 
 * @param array $buttons カスタムボタンの配列
 */
if ( ! function_exists( 'plm_render_custom_buttons' ) ) {
	function plm_render_custom_buttons( $buttons ) {
		foreach ( $buttons as $btn ) {
			if ( ! empty( $btn['text'] ) && ! empty( $btn['url'] ) ) {
				$target = ( isset( $btn['openInNewTab'] ) && $btn['openInNewTab'] !== false ) ? '_blank' : '_self';
				$color = ! empty( $btn['color'] ) ? esc_attr( $btn['color'] ) : '#2196f3';
				?>
				<div class="plm-shop-custom">
					<a rel="nofollow noopener" href="<?php echo esc_url( $btn['url'] ); ?>" target="<?php echo $target; ?>" style="background-color: <?php echo $color; ?>;"><?php echo esc_html( $btn['text'] ); ?></a>
				</div>
				<?php
			}
		}
	}
}

// attributes取得
$id         = isset( $attributes['id'] ) ? esc_html( $attributes['id'] ) : '';
$no         = isset( $attributes['no'] ) ? esc_html( $attributes['no'] ) : '';
$kw         = isset( $attributes['kw'] ) ? esc_html( $attributes['kw'] ) : '';
$shop       = isset( $attributes['shop'] ) ? esc_html( $attributes['shop'] ) : '';
$search     = isset( $attributes['search'] ) ? esc_html( $attributes['search'] ) : '';
$title      = isset( $attributes['title'] ) ? esc_html( $attributes['title'] ) : '';
$price      = ! empty( $attributes['price'] );
$showShop   = isset( $attributes['showShop'] ) ? $attributes['showShop'] : true;
$desc       = isset( $attributes['desc'] ) ? esc_html( $attributes['desc'] ) : '';
$imageUrl   = isset( $attributes['imageUrl'] ) ? esc_html( $attributes['imageUrl'] ) : '';

// ボタン表示設定（デフォルトはtrue）
$showAmazon  = isset( $attributes['showAmazon'] ) ? $attributes['showAmazon'] : true;
$showRakuten = isset( $attributes['showRakuten'] ) ? $attributes['showRakuten'] : true;
$showYahoo   = isset( $attributes['showYahoo'] ) ? $attributes['showYahoo'] : true;
$showMercari = isset( $attributes['showMercari'] ) ? $attributes['showMercari'] : true;
$showDmm     = isset( $attributes['showDmm'] ) ? $attributes['showDmm'] : true;

// カスタムボタン
$customButtonsBefore = isset( $attributes['customButtonsBefore'] ) ? $attributes['customButtonsBefore'] : [];
$customButtonsAfter  = isset( $attributes['customButtonsAfter'] ) ? $attributes['customButtonsAfter'] : [];

// Rakuten APIを使用して商品情報を取得する処理
$affiliate_settings = get_option('affiliate_settings', []);
$amazon_tracking_id = $affiliate_settings['amazon_tracking_id'] ?? '';
$rakuten_application_id = $affiliate_settings['rakuten_app_id'] ?? '';
$rakuten_affiliate_id = $affiliate_settings['rakuten_affiliate_id'] ?? '';
$vc_sid = $affiliate_settings['yahoo_sid'] ?? '';
$vc_pid = $affiliate_settings['yahoo_pid'] ?? '';
$mercari_ambassador_id = $affiliate_settings['mercari_id'] ?? '';
$dmm_affiliate_id = $affiliate_settings['dmm_id'] ?? '';

$item_id = $id; // 商品ID
$keyword = $no ?: $kw; // 商品番号があれば優先してキーワードに
$json = plm_get_rakuten_item_cached($rakuten_application_id, $rakuten_affiliate_id, $item_id, $keyword, $no);
// echo $json;
$data = json_decode($json, true);

// エラーチェック - エラーの場合は何も表示しない
if (isset($data['error'])) {
    // エラーメッセージを表示する場合はここに追加
    // echo '<!-- Rakuten API Error: ' . esc_html($data['error_description'] ?? $data['error']) . ' -->';
    return;
}

// echo '<pre>';
// var_dump($data); // デバッグ用
// echo '</pre>';
if (
    isset($data['Items']) &&
    isset($data['Items'][0]) &&
    isset($data['Items'][0]['Item'])
):
	$item = $data['Items'][0]['Item'];

	// 文字列や数値
	$itemName    = $item['itemName'];    // 商品名
	$itemPrice   = $item['itemPrice'];   // 価格
	$itemUrl     = $item['itemUrl'];     // 商品URL
	$shopName    = $item['shopName'];    // ショップ名
	$affiliateUrl = $item['affiliateUrl']; // アフィリエイトURL
	$affiliateRate = $item['affiliateRate']; // アフィリエイト料率
	$itemCaption = $item['itemCaption']; // 商品説明
	$reviewAverage = $item['reviewAverage']; // レビュー平均
	$reviewCount   = $item['reviewCount'];   // レビュー数

	// 配列（画像URLなど）
	$mediumImageUrl = $item['mediumImageUrls'][0]['imageUrl']; // 中サイズ画像
	$smallImageUrl  = $item['smallImageUrls'][0]['imageUrl'];  // 小サイズ画像

	// その他
	$shopUrl = $item['shopUrl']; // ショップURL
	$shopAffiliateUrl = $item['shopAffiliateUrl']; // ショップアフィリエイトURL
	$itemCode = $item['itemCode']; // アイテムコード
	$catchcopy = $item['catchcopy']; // キャッチコピー
endif;
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<div class="plm-product-box <?php echo esc_attr($itemCode ?? ''); ?>">
		<figure class="plm-product-thumb">
			<a rel="nofollow noopener" href="<?php echo esc_url($affiliateUrl ?? $itemUrl ?? '#'); ?>" class="plm-product-thumb-link" target="_blank" title="<?php echo esc_attr($title ?: ($itemName ?? '')); ?>">
				<img decoding="async" src="<?php echo esc_url($imageUrl ?: ($mediumImageUrl ?? '')); ?>" alt="<?php echo esc_attr($title ?: ($itemName ?? '商品画像')); ?>" width="128" height="128" class="plm-product-thumb-image">
			</a>
		</figure>
		<div class="plm-product-content">
			<div class="plm-product-title">
				<a rel="nofollow noopener" href="<?php echo esc_url($affiliateUrl ?? $itemUrl ?? '#'); ?>" class="plm-product-title-link" target="_blank" title="<?php echo esc_attr($title ?: ($itemName ?? '')); ?>">
					<?php
					if ($title) {
						echo $title;
					} elseif (!empty($itemName)) {
						echo $itemName;
					} else {
						// どちらも空なら何も表示しない
					}
					?>
				</a>
			</div>
			<div class="plm-product-snippet">
				<?php if ( $showShop && ($shop || !empty($shopName)) ) : ?>
					<div class="plm-product-maker"><?php echo esc_html($shop ?: $shopName); ?></div>
				<?php endif; ?>
				<?php if ( $price ) : ?>
					<div class="plm-product-price">
					<span class="plm-price-value">¥ <?php echo esc_html($itemPrice ? number_format($itemPrice) : '価格情報なし'); ?></span>
						<span class="acquired-date">（<?php echo date('Y/m/d H:i'); ?>時点）</span>
					</div>
				<?php endif; ?>
				<?php if ( trim($desc) !== '' ) : ?>
					<div class="plm-product-description">
						<?php echo $desc; ?>
					</div>
				<?php endif; ?>
			</div>
		<div class="plm-product-buttons">
		<?php
		// キーワード指定があればボタンを出す
		$kw_tokens = array_filter( array_map( 'trim', explode( ',', $kw ) ) );
		if ( count( $kw_tokens ) > 0 ) :
			$kw_for_url = implode( ' ', $kw_tokens );
			
			// カスタムボタン（前）
			plm_render_custom_buttons( $customButtonsBefore );
			
			if ( $showAmazon && ! empty( $amazon_tracking_id ) ) : ?>
					<div class="plm-shop-amazon">
						<a rel="nofollow noopener" href="https://www.amazon.co.jp/gp/search?keywords=<?php echo urlencode( $kw_for_url ); ?><?php echo $amazon_tracking_id ? '&tag=' . esc_attr( $amazon_tracking_id ) : ''; ?>" target="_blank">Amazon</a>
					</div>
				<?php endif; ?>
				<?php if ( $showRakuten && ! empty( $rakuten_affiliate_id ) ) : ?>
					<div class="plm-shop-rakuten">
						<a rel="nofollow noopener" href="https://hb.afl.rakuten.co.jp/hgc/<?php echo esc_attr( $rakuten_affiliate_id ); ?>/?pc=<?php echo urlencode( 'https://search.rakuten.co.jp/search/mall/' . $kw_for_url . '/' ); ?>&m=<?php echo urlencode( 'https://search.rakuten.co.jp/search/mall/' . $kw_for_url . '/' ); ?>" target="_blank">楽天</a>
					</div>
				<?php endif; ?>
				<?php if ( $showYahoo && ! empty( $vc_sid ) && ! empty( $vc_pid ) ) : ?>
					<div class="plm-shop-yahoo">
						<a rel="nofollow noopener" href="https://ck.jp.ap.valuecommerce.com/servlet/referral?sid=<?php echo esc_attr( $vc_sid ); ?>&pid=<?php echo esc_attr( $vc_pid ); ?>&vc_url=<?php echo urlencode( 'https://search.shopping.yahoo.co.jp/search?p=' . $kw_for_url ); ?>" target="_blank">Yahoo!ショッピング</a>
					</div>
				<?php endif; ?>
				<?php if ( $showMercari && ! empty( $mercari_ambassador_id ) ) : ?>
					<div class="plm-shop-mercari">
						<a rel="nofollow noopener" href="https://jp.mercari.com/search?afid=<?php echo esc_attr( $mercari_ambassador_id ); ?>&keyword=<?php echo urlencode( $kw_for_url ); ?>" target="_blank">メルカリ</a>
					</div>
				<?php endif; ?>
			<?php if ( $showDmm && ! empty( $dmm_affiliate_id ) ) : 
				// DMM: キーワード設定を使用
				$dmm_url = 'https://al.dmm.com/?lurl=https%3A%2F%2Fwww.dmm.com%2Fsearch%2F%3D%2Fsearchstr%3D' . rawurlencode( $kw_for_url ) . '%2Fanalyze%3DV1ECCVYAUQQ_%2Flimit%3D30%2Fsort%3Drankprofile%2F&af_id=' . trim( $dmm_affiliate_id ) . '&ch=link_tool&ch_id=link';
			?>
				<div class="plm-shop-dmm">
					<a rel="nofollow noopener" href="<?php echo esc_url( $dmm_url ); ?>" target="_blank">DMM</a>
				</div>
			<?php endif; ?>
			<?php
			// カスタムボタン（後）
			plm_render_custom_buttons( $customButtonsAfter );
			?>
		<?php endif; ?>
			</div>
		</div>
	</div>
</div>