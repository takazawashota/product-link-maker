import { __ } from '@wordpress/i18n';
import { useBlockProps, MediaUpload, InspectorControls, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, TextareaControl, FormTokenField, Button, Flex, __experimentalHeading as Heading, ColorPicker, Card, CardHeader, CardBody, Icon } from '@wordpress/components';

// import './style.scss';
import './editor.scss';

import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * 定数定義
 */
const DEFAULT_BUTTON_COLOR = '#2196f3';
const DEBOUNCE_DELAY = 1000;

/**
 * 共通スタイル定義
 */
const COMMON_STYLES = {
	buttonBase: {
		color: '#fff',
		borderRadius: '3px',
		padding: '8px 16px',
		display: 'inline-flex',
		alignItems: 'center',
		justifyContent: 'center',
		textDecoration: 'none',
		fontSize: '15px',
		fontWeight: '600',
		whiteSpace: 'nowrap',
		minWidth: '85px',
		boxShadow: '0 1px 3px rgba(0, 0, 0, 0.1)'
	},
	statusBox: {
		padding: '32px',
		textAlign: 'center',
		borderRadius: '4px'
	}
};

/**
 * カスタムボタンコンポーネント（再利用可能）
 */
function CustomButtonEditor({ buttons, onChange, label }) {
	return (
		<>
			<p style={{ fontSize: '13px', color: '#757575', marginTop: 0 }}>
				{label}
			</p>
			{buttons.map((btn, index) => (
				<Card key={index} style={{ marginBottom: '16px', border: '1px solid #ddd' }}>
					<CardHeader>
						<Flex justify="space-between" align="center">
							<span style={{ fontWeight: 600 }}>ボタン {index + 1}</span>
							<Button
								isDestructive
								isSmall
								onClick={() => {
									const newButtons = [...buttons];
									newButtons.splice(index, 1);
									onChange(newButtons);
								}}
								icon="trash"
							/>
						</Flex>
					</CardHeader>
					<CardBody>
						<TextControl
							label="ボタンテキスト"
							value={btn.text || ''}
							onChange={(val) => {
								const newButtons = [...buttons];
								newButtons[index] = { ...newButtons[index], text: val };
								onChange(newButtons);
							}}
							placeholder="例: 公式サイト"
						/>
						<TextControl
							label="リンクURL"
							value={btn.url || ''}
							onChange={(val) => {
								const newButtons = [...buttons];
								newButtons[index] = { ...newButtons[index], url: val };
								onChange(newButtons);
							}}
							placeholder="https://example.com"
						/>
						<ToggleControl
							label="別タブで開く"
							checked={btn.openInNewTab !== false}
							onChange={(val) => {
								const newButtons = [...buttons];
								newButtons[index] = { ...newButtons[index], openInNewTab: val };
								onChange(newButtons);
							}}
						/>
						<div style={{ marginTop: '12px' }}>
							<Button
								variant="secondary"
								onClick={() => {
									const newButtons = [...buttons];
									newButtons[index] = {
										...newButtons[index],
										showColorPicker: !newButtons[index].showColorPicker
									};
									onChange(newButtons);
								}}
								style={{ width: '100%', marginBottom: '8px' }}
							>
								{btn.showColorPicker ? '色を選択中' : 'ボタンの色を選択'}
								<span style={{
									marginLeft: '8px',
									width: '20px',
									height: '20px',
									backgroundColor: btn.color || DEFAULT_BUTTON_COLOR,
									display: 'inline-block',
									borderRadius: '3px',
									border: '1px solid #ddd',
									verticalAlign: 'middle'
								}} />
							</Button>
							{btn.showColorPicker && (
								<ColorPicker
									color={btn.color || DEFAULT_BUTTON_COLOR}
									onChange={(val) => {
										const newButtons = [...buttons];
										newButtons[index] = { ...newButtons[index], color: val };
										onChange(newButtons);
									}}
									enableAlpha
									defaultValue={DEFAULT_BUTTON_COLOR}
								/>
							)}
						</div>
					</CardBody>
				</Card>
			))}
			<Button
				variant="secondary"
				onClick={() => {
					const newButtons = [...buttons, {
						text: '',
						url: '',
						openInNewTab: true,
						color: DEFAULT_BUTTON_COLOR
					}];
					onChange(newButtons);
				}}
				icon="plus"
				style={{ width: '100%', justifyContent: 'center' }}
			>
				ボタンを追加
			</Button>
		</>
	);
}

/**
 * ショップボタンのスタイル定義
 */
const BUTTON_STYLES = {
	amazon: { backgroundColor: '#ff9900', text: 'Amazon' },
	rakuten: { backgroundColor: '#bf0000', text: '楽天' },
	yahoo: { backgroundColor: '#ff0033', text: 'Yahoo!ショッピング' },
	mercari: { backgroundColor: '#4dc9ff', text: 'メルカリ' },
	dmm: { backgroundColor: '#00bcd4', text: 'DMM' }
};

/**
 * カスタムボタンレンダリング
 */
function renderCustomButton(btn, keyPrefix, idx) {
	if (!btn.text || !btn.url) return null;

	return (
		<div className="plm-shop-custom" key={`${keyPrefix}-${idx}`}>
			<a
				rel="nofollow noopener"
				href={btn.url}
				target={btn.openInNewTab !== false ? "_blank" : "_self"}
				style={{
					...COMMON_STYLES.buttonBase,
					backgroundColor: btn.color || DEFAULT_BUTTON_COLOR
				}}
			>
				{btn.text}
			</a>
		</div>
	);
}

/**
 * ショップボタンレンダリング
 */
function renderShopButton(shopKey, url, style) {
	return (
		<div className={`plm-shop-${shopKey}`} key={shopKey}>
			<a
				rel="nofollow noopener"
				href={url}
				target="_blank"
				style={{
					...COMMON_STYLES.buttonBase,
					backgroundColor: style.backgroundColor
				}}
			>
				{style.text}
			</a>
		</div>
	);
}

/**
 * ステータスメッセージコンポーネント
 */
function StatusMessage({ type, message }) {
	const styles = {
		loading: {
			...COMMON_STYLES.statusBox,
			color: '#888',
			backgroundColor: '#f5f5f5',
			border: '1px dashed #ccc'
		},
		error: {
			...COMMON_STYLES.statusBox,
			color: '#d63638',
			backgroundColor: '#fff0f0',
			border: '1px solid #d63638'
		},
		empty: {
			...COMMON_STYLES.statusBox,
			color: '#666',
			backgroundColor: '#f5f5f5',
			border: '1px dashed #ccc'
		}
	};

	return <div style={styles[type]}>{message}</div>;
}

/**
 * 全ボタンをレンダリングする関数
 */
function renderAllButtons(attributes) {
	const kwArray = attributes.kw.split(',').map(s => s.trim()).filter(Boolean);
	const kwForUrl = kwArray.join(' ');
	const settings = window.ProductLinkMakerSettings || {};
	const buttons = [];

	// カスタムボタン（前）
	(attributes.customButtonsBefore || []).forEach((btn, idx) => {
		const customBtn = renderCustomButton(btn, 'custom-before', idx);
		if (customBtn) buttons.push(customBtn);
	});

	// ショップボタン
	if (settings.amazon && attributes.showAmazon !== false) {
		buttons.push(renderShopButton('amazon', `https://www.amazon.co.jp/gp/search?keywords=${encodeURIComponent(kwForUrl)}`, BUTTON_STYLES.amazon));
	}
	if (settings.rakuten && attributes.showRakuten !== false) {
		buttons.push(renderShopButton('rakuten', `https://search.rakuten.co.jp/search/mall/${encodeURIComponent(kwForUrl)}/`, BUTTON_STYLES.rakuten));
	}
	if (settings.yahoo && attributes.showYahoo !== false) {
		buttons.push(renderShopButton('yahoo', `https://search.shopping.yahoo.co.jp/search?p=${encodeURIComponent(kwForUrl)}`, BUTTON_STYLES.yahoo));
	}
	if (settings.mercari && attributes.showMercari !== false) {
		buttons.push(renderShopButton('mercari', `https://jp.mercari.com/search?keyword=${encodeURIComponent(kwForUrl)}`, BUTTON_STYLES.mercari));
	}
	if (settings.dmm && attributes.showDmm !== false) {
		// DMM: キーワード設定を使用
		const dmmUrl = `https://al.dmm.com/?lurl=https%3A%2F%2Fwww.dmm.com%2Fsearch%2F%3D%2Fsearchstr%3D${encodeURIComponent(kwForUrl)}%2Fanalyze%3DV1ECCVYAUQQ_%2Flimit%3D30%2Fsort%3Drankprofile%2F&af_id=dummy&ch=link_tool&ch_id=link`;
		buttons.push(renderShopButton('dmm', dmmUrl, BUTTON_STYLES.dmm));
	}

	// カスタムボタン（後）
	(attributes.customButtonsAfter || []).forEach((btn, idx) => {
		const customBtn = renderCustomButton(btn, 'custom-after', idx);
		if (customBtn) buttons.push(customBtn);
	});

	return buttons;
}

/**
 * プレビューコンポーネント
 */
function ProductPreview({ attributes, item, imageUrl, imageKey, itemTitle, itemLink }) {
	return (
		<div className="plm-product-box">
			<figure className="plm-product-thumb">
				<a rel="nofollow noopener"
					href={itemLink}
					className="plm-product-thumb-link"
					target="_blank"
					title={itemTitle}
				>
					{imageUrl && (
						<img
							key={imageKey}
							decoding="async"
							src={imageUrl}
							alt={itemTitle || '商品画像'}
							width="128"
							height="128"
							className="plm-product-thumb-image"
						/>
					)}
				</a>
			</figure>
			<div className="plm-product-content">
				<div className="plm-product-title">
					<a rel="nofollow noopener"
						href={itemLink}
						className="plm-product-title-link"
						target="_blank"
						title={itemTitle}
					>
						{itemTitle ? itemTitle : ''}
					</a>
				</div>
				<div className="plm-product-snippet">
					{attributes.showShop !== false && (item?.shopName || attributes.shop) && (
						<div className="plm-product-maker">
							{item?.shopName || attributes.shop}
						</div>
					)}
					{attributes.price && (
						<div className="plm-product-price">
							<span className="plm-price-value">
								{item?.itemPrice ? `￥ ${Number(item.itemPrice).toLocaleString('ja-JP')}` : '価格情報なし'}
							</span>
							<span className="acquired-date">
								{item?.itemPrice ? `（${new Date().toLocaleDateString()} 時点）` : ''}
							</span>
						</div>
					)}
					{attributes.desc && (
						<div className="plm-product-description">{attributes.desc}</div>
					)}
				</div>
				<div className="plm-product-buttons">
					{attributes.kw && attributes.kw.split(',').filter(Boolean).length > 0 && renderAllButtons(attributes)}
				</div>
			</div>
		</div>
	);
}

export default function Edit({ attributes, setAttributes }) {
	const [item, setItem] = useState(null);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);
	const [isClearingCache, setIsClearingCache] = useState(false);
	const fetchTimeoutRef = useRef(null);

	const fetchData = useCallback(async () => {
		if (!attributes.id && !attributes.no && !attributes.kw) {
			setItem(null);
			setAttributes(prev => ({ ...prev, imageUrl: '' }));
			return;
		}
		setIsLoading(true);
		setError(null);
		try {
			// キャッシュ削除は行わず、常にキャッシュを利用する
			const data = await apiFetch({
				path: `/product-link-maker/v1/rakuten/?id=${encodeURIComponent(attributes.id || '')}&kw=${encodeURIComponent(attributes.kw || '')}&no=${encodeURIComponent(attributes.no || '')}`
			});
			if (data?.Items?.[0]?.Item) {
				const newItem = data.Items[0].Item;
				setItem(newItem);
				const apiImage = newItem.mediumImageUrls?.[0]?.imageUrl || '';
				setAttributes(prev => ({ ...prev, imageUrl: apiImage }));
				setError(null);
			} else if (data?.error) {
				setItem(null);
				setAttributes(prev => ({ ...prev, imageUrl: '' }));
				setError('APIエラー: ' + (data.error_description || 'リクエスト制限に達しました。しばらく待ってから再度お試しください。'));
			} else {
				setItem(null);
				setAttributes(prev => ({ ...prev, imageUrl: '' }));
				setError('データが取得できませんでした');
			}
		} catch (error) {
			console.error('Rakuten API fetch error:', error);
			setItem(null);
			setAttributes(prev => ({ ...prev, imageUrl: '' }));
			setError('APIエラーが発生しました。しばらく待ってから再度お試しください。');
		} finally {
			setIsLoading(false);
		}
	}, [attributes.id, attributes.no, attributes.kw, setAttributes]);

	// キャッシュを削除して再取得する関数
	const handleClearCache = useCallback(async () => {
		if (!window.confirm('キャッシュを削除して最新データを取得しますか？')) {
			return;
		}
		setIsClearingCache(true);
		try {
			await apiFetch({
				path: '/product-link-maker/v1/rakuten-cache/',
				method: 'POST',
				data: {
					id: attributes.id,
					kw: attributes.kw,
					no: attributes.no,
				},
			});
			// キャッシュ削除後、再取得
			await fetchData();
		} catch (error) {
			console.error('Cache clear error:', error);
			alert('キャッシュ削除中にエラーが発生しました。');
		} finally {
			setIsClearingCache(false);
		}
	}, [attributes.id, attributes.kw, attributes.no, fetchData]);

	// attributesがあれば必ずfetchDataを呼ぶ（デバウンス付き）
	useEffect(() => {
		// 前回のタイマーをクリア
		if (fetchTimeoutRef.current) {
			clearTimeout(fetchTimeoutRef.current);
		}

		if (attributes.id || attributes.no || attributes.kw) {
			// デバウンスを追加
			fetchTimeoutRef.current = setTimeout(() => {
				fetchData();
			}, DEBOUNCE_DELAY);
		} else {
			setItem(null);
		}

		// クリーンアップ
		return () => {
			if (fetchTimeoutRef.current) {
				clearTimeout(fetchTimeoutRef.current);
			}
		};
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [attributes.id, attributes.no, attributes.kw]);

	// 画像・リンク・タイトル等をAPIデータ優先で差し替え
	const [imageKey, setImageKey] = useState(0);
	const imageUrl = attributes.imageUrl || (item && item.mediumImageUrls && item.mediumImageUrls[0] ? item.mediumImageUrls[0].imageUrl : '');
	useEffect(() => {
		setImageKey(k => k + 1);
	}, [imageUrl]);
	const itemTitle = attributes.title || (item ? item.itemName : '');
	const itemLink = item ? (item.affiliateUrl || item.itemUrl || '#') : '#';

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('商品情報設定', 'product-link-maker')} initialOpen={true}>
					<TextControl
						label={__('アイテムコード（ID）', 'product-link-maker')}
						help={attributes.no ? __('商品番号が入力されている場合は入力できません', 'product-link-maker') : ''}
						value={attributes.id}
						disabled={!!attributes.no}
						onChange={(val) => {
							setAttributes({ id: val, no: '' }); // 入力時にnoをクリア
						}}
						placeholder="book:11830886"
						style={attributes.no ? { backgroundColor: '#f5f5f5', color: '#aaa' } : {}}
					/>
					<TextControl
						label={__('商品番号', 'product-link-maker')}
						help={attributes.id ? __('アイテムコード（ID）が入力されている場合は入力できません', 'product-link-maker') : ''}
						value={attributes.no}
						disabled={!!attributes.id}
						onChange={(val) => {
							setAttributes({ no: val, id: '' }); // 入力時にidをクリア
						}}
						placeholder="4902102072625"
						style={attributes.id ? { backgroundColor: '#f5f5f5', color: '#aaa' } : {}}
					/>
					<FormTokenField
						label={__('キーワード指定', 'product-link-maker')}
						value={attributes.kw ? attributes.kw.split(',').filter(Boolean) : []}
						onChange={(tokens) => setAttributes({ kw: tokens.join(',') })}
						placeholder={__('キーワードを入力してEnter', 'product-link-maker')}
					/>
					<TextControl
						label={__('ショップコード', 'product-link-maker')}
						value={attributes.shop}
						onChange={(val) => setAttributes({ shop: val })}
					/>
					<FormTokenField
						label={__('サーチ', 'product-link-maker')}
						value={attributes.search ? attributes.search.split(',').filter(Boolean) : []}
						onChange={(tokens) => setAttributes({ search: tokens.join(',') })}
						placeholder={__('サーチを入力してEnter', 'product-link-maker')}
					/>
					<TextareaControl
						label={__('タイトル', 'product-link-maker')}
						value={attributes.title}
						onChange={(val) => setAttributes({ title: val })}
					/>
					<ToggleControl
						label={__('店名表示', 'product-link-maker')}
						checked={attributes.showShop !== false}
						onChange={(val) => setAttributes({ showShop: val })}
					/>
					<ToggleControl
						label={__('価格表示', 'product-link-maker')}
						checked={attributes.price}
						onChange={(val) => setAttributes({ price: val })}
					/>
					<TextareaControl
						label={__('説明文', 'product-link-maker')}
						value={attributes.desc}
						onChange={(val) => setAttributes({ desc: val })}
					/>
					<Heading>画像アップロード</Heading>
					<MediaUploadCheck>
						<div style={{ marginBottom: '0' }}>
							{(attributes.imageUrl !== '' ? attributes.imageUrl : item?.mediumImageUrls?.[0]?.imageUrl) && (
								<div style={{ marginBottom: '10px', display: 'flex', justifyContent: 'center', backgroundColor: '#eee' }}>
									<img
										src={attributes.imageUrl !== '' ? attributes.imageUrl : item?.mediumImageUrls?.[0]?.imageUrl}
										alt=""
										style={{ display: 'block' }}
									/>
								</div>
							)}
							<MediaUpload
								onSelect={(media) => setAttributes({ imageUrl: media.url || '' })}
								allowedTypes={['image']}
								render={({ open }) => (
									<Flex>
										<Button onClick={open} variant="secondary">
											画像を選択
										</Button>
										{attributes.imageUrl && (
											<Button
												style={{ marginTop: 4 }}
												onClick={() => setAttributes({ imageUrl: '' })}
												isDestructive
											>
												{__('画像をリセット', 'product-link-maker')}
											</Button>
										)}
									</Flex>
								)}
							/>
						</div>
					</MediaUploadCheck>

					<div style={{ margin: '24px 0 0 0' }} />
					<Heading>ボタン表示設定</Heading>
					<ToggleControl
						label={__('Amazonボタンを表示', 'product-link-maker')}
						checked={attributes.showAmazon !== false}
						onChange={val => setAttributes({ showAmazon: val })}
					/>
					<ToggleControl
						label={__('楽天ボタンを表示', 'product-link-maker')}
						checked={attributes.showRakuten !== false}
						onChange={val => setAttributes({ showRakuten: val })}
					/>
					<ToggleControl
						label={__('Yahoo!ボタンを表示', 'product-link-maker')}
						checked={attributes.showYahoo !== false}
						onChange={val => setAttributes({ showYahoo: val })}
					/>
					<ToggleControl
						label={__('メルカリボタンを表示', 'product-link-maker')}
						checked={attributes.showMercari !== false}
						onChange={val => setAttributes({ showMercari: val })}
					/>
					<ToggleControl
						label={__('DMMボタンを表示', 'product-link-maker')}
						checked={attributes.showDmm !== false}
						onChange={val => setAttributes({ showDmm: val })}
					/>

				</PanelBody>
				<PanelBody title={__('カスタムボタン（前）', 'product-link-maker')} initialOpen={true}>
					<CustomButtonEditor
						buttons={attributes.customButtonsBefore || []}
						onChange={(newButtons) => setAttributes({ customButtonsBefore: newButtons })}
						label="既存のボタンの前に表示されるカスタムボタンを追加できます"
					/>
				</PanelBody>
				<PanelBody title={__('カスタムボタン（後）', 'product-link-maker')} initialOpen={true}>
					<CustomButtonEditor
						buttons={attributes.customButtonsAfter || []}
						onChange={(newButtons) => setAttributes({ customButtonsAfter: newButtons })}
						label="既存のボタンの後に表示されるカスタムボタンを追加できます"
					/>
				</PanelBody>
				<PanelBody title={__('アフィリエイト設定', 'product-link-maker')} initialOpen={false}>
					<Button
						href={`${window.location.origin}/wp-admin/options-general.php?page=product-link-maker`}
						target="_blank"
						className=""
						variant="primary"
						icon="admin-generic"
						style={{ marginTop: '5px', width: '100%', textAlign: 'center' }}
					>
						{__('アフィリエイト設定', 'product-link-maker')}
					</Button>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				{isLoading ? (
					<StatusMessage type="loading" message="Loading..." />
				) : error ? (
					<StatusMessage type="error" message={error} />
				) : !attributes.id && !attributes.no && !attributes.kw ? (
					<StatusMessage type="empty" message="商品情報を設定してください" />
				) : (
					<>
						<ProductPreview
							attributes={attributes}
							item={item}
							imageUrl={imageUrl}
							imageKey={imageKey}
							itemTitle={itemTitle}
							itemLink={itemLink}
						/>
						{item && (
							<div className="plm-admin-info">
								<Button
									variant="secondary"
									onClick={handleClearCache}
									disabled={isClearingCache}
									isBusy={isClearingCache}
									size="small"
								>
									{isClearingCache ? '削除中...' : 'キャッシュ更新'}
								</Button>
								{item?.affiliateRate && (
									<span>
										料率: {item.affiliateRate}%
									</span>
								)}
							</div>
						)}
					</>
				)}
			</div>
		</>
	);
}
