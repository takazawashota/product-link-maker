import { __ } from '@wordpress/i18n';
import { useBlockProps, MediaUpload, InspectorControls, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, TextareaControl, FormTokenField, Button, Flex, __experimentalHeading as Heading, ColorPicker, Card, CardHeader, CardBody, Icon } from '@wordpress/components';

// import './style.scss';
import './editor.scss';

import { useEffect, useState, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
	const [item, setItem] = useState(null);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);
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
		if (!window.confirm('本当にキャッシュを削除してもいいですか？')) {
			return;
		}
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
			fetchData();
		} catch (error) {
			console.error('Cache clear error:', error);
		}
	}, [attributes.id, attributes.kw, attributes.no, fetchData]);

	// attributesがあれば必ずfetchDataを呼ぶ（デバウンス付き）
	useEffect(() => {
		// 前回のタイマーをクリア
		if (fetchTimeoutRef.current) {
			clearTimeout(fetchTimeoutRef.current);
		}

		if (attributes.id || attributes.no || attributes.kw) {
			// 1秒のデバウンスを追加
			fetchTimeoutRef.current = setTimeout(() => {
				fetchData();
			}, 1000);
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
					<p style={{ fontSize: '13px', color: '#757575', marginTop: 0 }}>
						既存のボタンの前に表示されるカスタムボタンを追加できます
					</p>
					{(attributes.customButtonsBefore || []).map((btn, index) => (
						<Card key={index} style={{ marginBottom: '16px', border: '1px solid #ddd' }}>
							<CardHeader>
								<Flex justify="space-between" align="center">
									<span style={{ fontWeight: 600 }}>ボタン {index + 1}</span>
									<Button
										isDestructive
										isSmall
										onClick={() => {
											const newButtons = [...(attributes.customButtonsBefore || [])];
											newButtons.splice(index, 1);
											setAttributes({ customButtonsBefore: newButtons });
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
										const newButtons = [...(attributes.customButtonsBefore || [])];
										newButtons[index] = { ...newButtons[index], text: val };
										setAttributes({ customButtonsBefore: newButtons });
									}}
									placeholder="例: 公式サイト"
								/>
								<TextControl
									label="リンクURL"
									value={btn.url || ''}
									onChange={(val) => {
										const newButtons = [...(attributes.customButtonsBefore || [])];
										newButtons[index] = { ...newButtons[index], url: val };
										setAttributes({ customButtonsBefore: newButtons });
									}}
									placeholder="https://example.com"
								/>
								<ToggleControl
									label="別タブで開く"
									checked={btn.openInNewTab !== false}
									onChange={(val) => {
										const newButtons = [...(attributes.customButtonsBefore || [])];
										newButtons[index] = { ...newButtons[index], openInNewTab: val };
										setAttributes({ customButtonsBefore: newButtons });
									}}
								/>
								<div style={{ marginTop: '12px' }}>
									<Button
										variant="secondary"
										onClick={() => {
											const newButtons = [...(attributes.customButtonsBefore || [])];
											newButtons[index] = {
												...newButtons[index],
												showColorPicker: !newButtons[index].showColorPicker
											};
											setAttributes({ customButtonsBefore: newButtons });
										}}
										style={{ width: '100%', marginBottom: '8px' }}
									>
										{btn.showColorPicker ? '色を選択中' : 'ボタンの色を選択'}
										<span style={{
											marginLeft: '8px',
											width: '20px',
											height: '20px',
											backgroundColor: btn.color || '#2196f3',
											display: 'inline-block',
											borderRadius: '3px',
											border: '1px solid #ddd',
											verticalAlign: 'middle'
										}} />
									</Button>
									{btn.showColorPicker && (
										<ColorPicker
											color={btn.color || '#2196f3'}
											onChange={(val) => {
												const newButtons = [...(attributes.customButtonsBefore || [])];
												newButtons[index] = { ...newButtons[index], color: val };
												setAttributes({ customButtonsBefore: newButtons });
											}}
											enableAlpha
											defaultValue="#2196f3"
										/>
									)}
								</div>
							</CardBody>
						</Card>
					))}
					<Button
						variant="secondary"
						onClick={() => {
							const newButtons = [...(attributes.customButtonsBefore || []), {
								text: '',
								url: '',
								openInNewTab: true,
								color: '#2196f3'
							}];
							setAttributes({ customButtonsBefore: newButtons });
						}}
						icon="plus"
						style={{ width: '100%', justifyContent: 'center' }}
					>
						ボタンを追加
					</Button>
				</PanelBody>
				<PanelBody title={__('カスタムボタン（後）', 'product-link-maker')} initialOpen={true}>
					<p style={{ fontSize: '13px', color: '#757575', marginTop: 0 }}>
						既存のボタンの後に表示されるカスタムボタンを追加できます
					</p>
					{(attributes.customButtonsAfter || []).map((btn, index) => (
						<Card key={index} style={{ marginBottom: '16px', border: '1px solid #ddd' }}>
							<CardHeader>
								<Flex justify="space-between" align="center">
									<span style={{ fontWeight: 600 }}>ボタン {index + 1}</span>
									<Button
										isDestructive
										isSmall
										onClick={() => {
											const newButtons = [...(attributes.customButtonsAfter || [])];
											newButtons.splice(index, 1);
											setAttributes({ customButtonsAfter: newButtons });
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
										const newButtons = [...(attributes.customButtonsAfter || [])];
										newButtons[index] = { ...newButtons[index], text: val };
										setAttributes({ customButtonsAfter: newButtons });
									}}
									placeholder="例: 公式サイト"
								/>
								<TextControl
									label="リンクURL"
									value={btn.url || ''}
									onChange={(val) => {
										const newButtons = [...(attributes.customButtonsAfter || [])];
										newButtons[index] = { ...newButtons[index], url: val };
										setAttributes({ customButtonsAfter: newButtons });
									}}
									placeholder="https://example.com"
								/>
								<ToggleControl
									label="別タブで開く"
									checked={btn.openInNewTab !== false}
									onChange={(val) => {
										const newButtons = [...(attributes.customButtonsAfter || [])];
										newButtons[index] = { ...newButtons[index], openInNewTab: val };
										setAttributes({ customButtonsAfter: newButtons });
									}}
								/>
								<div style={{ marginTop: '12px' }}>
									<Button
										variant="secondary"
										onClick={() => {
											const newButtons = [...(attributes.customButtonsAfter || [])];
											newButtons[index] = {
												...newButtons[index],
												showColorPicker: !newButtons[index].showColorPicker
											};
											setAttributes({ customButtonsAfter: newButtons });
										}}
										style={{ width: '100%', marginBottom: '8px' }}
									>
										{btn.showColorPicker ? '色を選択中' : 'ボタンの色を選択'}
										<span style={{
											marginLeft: '8px',
											width: '20px',
											height: '20px',
											backgroundColor: btn.color || '#2196f3',
											display: 'inline-block',
											borderRadius: '3px',
											border: '1px solid #ddd',
											verticalAlign: 'middle'
										}} />
									</Button>
									{btn.showColorPicker && (
										<ColorPicker
											color={btn.color || '#2196f3'}
											onChange={(val) => {
												const newButtons = [...(attributes.customButtonsAfter || [])];
												newButtons[index] = { ...newButtons[index], color: val };
												setAttributes({ customButtonsAfter: newButtons });
											}}
											enableAlpha
											defaultValue="#2196f3"
										/>
									)}
								</div>
							</CardBody>
						</Card>
					))}
					<Button
						variant="secondary"
						onClick={() => {
							const newButtons = [...(attributes.customButtonsAfter || []), {
								text: '',
								url: '',
								openInNewTab: true,
								color: '#2196f3'
							}];
							setAttributes({ customButtonsAfter: newButtons });
						}}
						icon="plus"
						style={{ width: '100%', justifyContent: 'center' }}
					>
						ボタンを追加
					</Button>
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
					<div style={{ padding: '32px', textAlign: 'center', color: '#888', backgroundColor: '#f5f5f5', border: '1px dashed #ccc', borderRadius: '4px' }}>
						Loading...
					</div>
				) : error ? (
					<div style={{ padding: '32px', textAlign: 'center', color: '#d63638', backgroundColor: '#fff0f0', border: '1px solid #d63638', borderRadius: '4px' }}>
						{error}
					</div>
				) : !attributes.id && !attributes.no && !attributes.kw ? (
					<div style={{ padding: '32px', textAlign: 'center', color: '#666', backgroundColor: '#f5f5f5', border: '1px dashed #ccc', borderRadius: '4px' }}>
						商品情報を設定してください
					</div>
				) : (
					<div className="rakuten-item-box product-item-box no-icon pis-m">
						<figure className="rakuten-item-thumb product-item-thumb">
							<a rel="nofollow noopener"
								href={itemLink}
								className="rakuten-item-thumb-link product-item-thumb-link"
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
										className="rakuten-item-thumb-image product-item-thumb-image"
									/>
								)}
							</a>
						</figure>
						<div className="rakuten-item-content product-item-content cf">
							<div className="rakuten-item-title product-item-title">
								<a rel="nofollow noopener"
									href={itemLink}
									className="rakuten-item-title-link product-item-title-link"
									target="_blank"
									title={itemTitle}
								>
									{itemTitle ? itemTitle : ''}
								</a>
							</div>
							<div className="rakuten-item-snippet product-item-snippet">
								{attributes.showShop !== false && (item?.shopName || attributes.shop) && (
									<div className="rakuten-item-maker product-item-maker">
										{item?.shopName || attributes.shop}
									</div>
								)}
								{attributes.price && (
									<div className="product-item-price">
										<span className="item-price">
											{item?.itemPrice ? `￥ ${item.itemPrice}` : '価格情報なし'}
										</span>
										<span className="acquired-date">
											{item?.itemPrice ? `（${new Date().toLocaleDateString()} 時点）` : ''}
										</span>
									</div>
								)}
								{attributes.desc && (
									<div className="product-item-description">{attributes.desc}</div>
								)}
							</div>
							<div className="amazon-item-buttons product-item-buttons">
								{attributes.kw && attributes.kw.split(',').filter(Boolean).length > 0 && (() => {
									const kwArray = attributes.kw.split(',').map(s => s.trim()).filter(Boolean);
									const kwForUrl = kwArray.join(' ');
									const settings = window.ProductLinkMakerSettings || {};
									const buttons = [];

									// カスタムボタン（前）
									(attributes.customButtonsBefore || []).forEach((btn, idx) => {
										if (btn.text && btn.url) {
											buttons.push(
												<div className="shoplink-custom" key={`custom-before-${idx}`}>
													<a
														rel="nofollow noopener"
														href={btn.url}
														target={btn.openInNewTab !== false ? "_blank" : "_self"}
														style={{
															backgroundColor: btn.color || '#2196f3',
															color: '#fff',
															borderRadius: '4px',
															padding: '6px 16px',
															display: 'inline-block',
															textDecoration: 'none'
														}}
													>
														{btn.text}
													</a>
												</div>
											);
										}
									});

									if (settings.amazon && attributes.showAmazon !== false) {
										buttons.push(
											<div className="shoplinkamazon" key="amazon">
												<a rel="nofollow noopener" href={`https://www.amazon.co.jp/gp/search?keywords=${encodeURIComponent(kwForUrl)}`} target="_blank" style={{ backgroundColor: '#f79901', color: '#fff', borderRadius: '4px', padding: '6px 16px', display: 'inline-block', textDecoration: 'none' }}>Amazon</a>
											</div>
										);
									}
									if (settings.rakuten && attributes.showRakuten !== false) {
										buttons.push(
											<div className="shoplinkrakuten" key="rakuten">
												<a rel="nofollow noopener" href={`https://search.rakuten.co.jp/search/mall/${encodeURIComponent(kwForUrl)}/`} target="_blank" style={{ backgroundColor: '#bf0000', color: '#fff', borderRadius: '4px', padding: '6px 16px', display: 'inline-block', textDecoration: 'none' }}>楽天</a>
											</div>
										);
									}
									if (settings.yahoo && attributes.showYahoo !== false) {
										buttons.push(
											<div className="shoplinkyahoo" key="yahoo">
												<a rel="nofollow noopener" href={`https://search.shopping.yahoo.co.jp/search?p=${encodeURIComponent(kwForUrl)}`} target="_blank" style={{ backgroundColor: '#e60033', color: '#fff', borderRadius: '4px', padding: '6px 16px', display: 'inline-block', textDecoration: 'none' }}>Yahoo!ショッピング</a>
											</div>
										);
									}
									if (settings.mercari && attributes.showMercari !== false) {
										buttons.push(
											<div className="shoplinkmercari" key="mercari">
												<a rel="nofollow noopener" href={`https://jp.mercari.com/search?keyword=${encodeURIComponent(kwForUrl)}`} target="_blank" style={{ backgroundColor: '#4dc9ff', color: '#fff', borderRadius: '4px', padding: '6px 16px', display: 'inline-block', textDecoration: 'none' }}>メルカリ</a>
											</div>
										);
									}
									if (settings.dmm && attributes.showDmm !== false) {
										// DMMは半角スペース区切りでキーワードを渡す（各キーワードを個別にエンコード）
										const dmmSearchStr = kwArray.map(kw => encodeURIComponent(kw)).join(' ');
										const dmmUrl = `https://www.dmm.com/search/=/searchstr=${dmmSearchStr}/analyze=V1ECCVYAUQQ_/limit=30/sort=rankprofile/?utm_medium=dmm_affiliate&utm_source=dummy&utm_term=dmm.com&utm_campaign=affiliate_link_tool&utm_content=link`;
										buttons.push(
											<div className="shoplinkdmm" key="dmm">
												<a rel="nofollow noopener" href={dmmUrl} target="_blank" style={{ backgroundColor: '#00bcd4', color: '#fff', borderRadius: '4px', padding: '6px 16px', display: 'inline-block', textDecoration: 'none' }}>DMM</a>
											</div>
										);
									}

									// カスタムボタン（後）
									(attributes.customButtonsAfter || []).forEach((btn, idx) => {
										if (btn.text && btn.url) {
											buttons.push(
												<div className="shoplink-custom" key={`custom-after-${idx}`}>
													<a
														rel="nofollow noopener"
														href={btn.url}
														target={btn.openInNewTab !== false ? "_blank" : "_self"}
														style={{
															backgroundColor: btn.color || '#2196f3',
															color: '#fff',
															borderRadius: '4px',
															padding: '6px 16px',
															display: 'inline-block',
															textDecoration: 'none'
														}}
													>
														{btn.text}
													</a>
												</div>
											);
										}
									}); return buttons;
								})()}
							</div>
							<div className="product-item-admin">
								<Button
									variant="link"
									onClick={handleClearCache}
									style={{ color: '#0073aa', textDecoration: 'underline', cursor: 'pointer', padding: 0, marginRight: '10px' }}
								>
									キャッシュ削除
								</Button>
								<span className="product-affiliate-rate">料率：{item?.affiliateRate ? `${item.affiliateRate}%` : ''}</span>
							</div>
						</div>
					</div>
				)}
			</div>
		</>
	);
}
