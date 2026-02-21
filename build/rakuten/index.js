/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/rakuten/block.json":
/*!********************************!*\
  !*** ./src/rakuten/block.json ***!
  \********************************/
/***/ ((module) => {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"product-link-maker/rakuten","version":"0.1.0","title":"楽天商品リンク","category":"product-link-maker","icon":"cart","description":"楽天市場の商品情報を取得して、アフィリエイトリンク付きの商品カードを表示するブロックです。","example":{"attributes":{"id":"book:11830886","kw":"サンプル商品","title":"サンプル商品タイトル","price":"1,980","imageUrl":"https://placehold.co/300x300/e8e8e8/666?text=Product+Image","desc":"楽天市場の商品情報を自動取得してアフィリエイトリンク付きの商品カードを表示します。","shopName":"楽天ブックス","showShop":true,"showRakuten":true,"showAmazon":true,"showYahoo":true,"showMercari":true,"showDmm":true}},"supports":{"html":false},"textdomain":"product-link-maker","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php","viewScript":"file:./view.js","attributes":{"id":{"type":"string","default":""},"no":{"type":"string","default":""},"kw":{"type":"string","default":""},"shop":{"type":"string","default":""},"search":{"type":"string","default":""},"title":{"type":"string","default":""},"price":{"type":"boolean","default":false},"showShop":{"type":"boolean","default":true},"desc":{"type":"string","default":""},"imageUrl":{"type":"string","default":""},"showImage":{"type":"boolean","default":true},"showAmazon":{"type":"boolean","default":true},"showRakuten":{"type":"boolean","default":true},"showYahoo":{"type":"boolean","default":true},"showMercari":{"type":"boolean","default":true},"showDmm":{"type":"boolean","default":true},"customButtonsBefore":{"type":"array","default":[]},"customButtonsAfter":{"type":"array","default":[]}}}');

/***/ }),

/***/ "./src/rakuten/edit.js":
/*!*****************************!*\
  !*** ./src/rakuten/edit.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./editor.scss */ "./src/rakuten/editor.scss");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__);




// import './style.scss';





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
function CustomButtonEditor({
  buttons,
  onChange,
  label
}) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("p", {
      style: {
        fontSize: '13px',
        color: '#757575',
        marginTop: 0
      },
      children: label
    }), buttons.map((btn, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Card, {
      style: {
        marginBottom: '16px',
        border: '1px solid #ddd'
      },
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardHeader, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
          justify: "space-between",
          align: "center",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("span", {
            style: {
              fontWeight: 600
            },
            children: ["\u30DC\u30BF\u30F3 ", index + 1]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
            isDestructive: true,
            isSmall: true,
            onClick: () => {
              const newButtons = [...buttons];
              newButtons.splice(index, 1);
              onChange(newButtons);
            },
            icon: "trash"
          })]
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.CardBody, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: "\u30DC\u30BF\u30F3\u30C6\u30AD\u30B9\u30C8",
          value: btn.text || '',
          onChange: val => {
            const newButtons = [...buttons];
            newButtons[index] = {
              ...newButtons[index],
              text: val
            };
            onChange(newButtons);
          },
          placeholder: "\u4F8B: \u516C\u5F0F\u30B5\u30A4\u30C8"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: "\u30EA\u30F3\u30AFURL",
          value: btn.url || '',
          onChange: val => {
            const newButtons = [...buttons];
            newButtons[index] = {
              ...newButtons[index],
              url: val
            };
            onChange(newButtons);
          },
          placeholder: "https://example.com"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: "\u5225\u30BF\u30D6\u3067\u958B\u304F",
          checked: btn.openInNewTab !== false,
          onChange: val => {
            const newButtons = [...buttons];
            newButtons[index] = {
              ...newButtons[index],
              openInNewTab: val
            };
            onChange(newButtons);
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
          style: {
            marginTop: '12px'
          },
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
            variant: "secondary",
            onClick: () => {
              const newButtons = [...buttons];
              newButtons[index] = {
                ...newButtons[index],
                showColorPicker: !newButtons[index].showColorPicker
              };
              onChange(newButtons);
            },
            style: {
              width: '100%',
              marginBottom: '8px'
            },
            children: [btn.showColorPicker ? '色を選択中' : 'ボタンの色を選択', /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("span", {
              style: {
                marginLeft: '8px',
                width: '20px',
                height: '20px',
                backgroundColor: btn.color || DEFAULT_BUTTON_COLOR,
                display: 'inline-block',
                borderRadius: '3px',
                border: '1px solid #ddd',
                verticalAlign: 'middle'
              }
            })]
          }), btn.showColorPicker && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ColorPicker, {
            color: btn.color || DEFAULT_BUTTON_COLOR,
            onChange: val => {
              const newButtons = [...buttons];
              newButtons[index] = {
                ...newButtons[index],
                color: val
              };
              onChange(newButtons);
            },
            enableAlpha: true,
            defaultValue: DEFAULT_BUTTON_COLOR
          })]
        })]
      })]
    }, index)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
      variant: "secondary",
      onClick: () => {
        const newButtons = [...buttons, {
          text: '',
          url: '',
          openInNewTab: true,
          color: DEFAULT_BUTTON_COLOR
        }];
        onChange(newButtons);
      },
      icon: "plus",
      style: {
        width: '100%',
        justifyContent: 'center'
      },
      children: "\u30DC\u30BF\u30F3\u3092\u8FFD\u52A0"
    })]
  });
}

/**
 * ショップボタンのスタイル定義
 */
const BUTTON_STYLES = {
  amazon: {
    backgroundColor: '#ff9900',
    text: 'Amazon'
  },
  rakuten: {
    backgroundColor: '#bf0000',
    text: '楽天市場'
  },
  yahoo: {
    backgroundColor: '#ff0033',
    text: 'Yahoo!ショッピング'
  },
  mercari: {
    backgroundColor: '#4dc9ff',
    text: 'メルカリ'
  },
  dmm: {
    backgroundColor: '#00bcd4',
    text: 'DMM'
  }
};

/**
 * カスタムボタンレンダリング
 */
function renderCustomButton(btn, keyPrefix, idx) {
  if (!btn.text || !btn.url) return null;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
    className: "plm-shop-custom",
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("a", {
      rel: "nofollow noopener",
      href: btn.url,
      target: btn.openInNewTab !== false ? "_blank" : "_self",
      style: {
        ...COMMON_STYLES.buttonBase,
        backgroundColor: btn.color || DEFAULT_BUTTON_COLOR
      },
      children: btn.text
    })
  }, `${keyPrefix}-${idx}`);
}

/**
 * ショップボタンレンダリング
 */
function renderShopButton(shopKey, url, style) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
    className: `plm-shop-${shopKey}`,
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("a", {
      rel: "nofollow noopener",
      href: url,
      target: "_blank",
      style: {
        ...COMMON_STYLES.buttonBase,
        backgroundColor: style.backgroundColor
      },
      children: style.text
    })
  }, shopKey);
}

/**
 * ステータスメッセージコンポーネント
 */
function StatusMessage({
  type,
  message
}) {
  const styles = {
    loading: {
      ...COMMON_STYLES.statusBox,
      color: '#888',
      backgroundColor: '#f5f5f5',
      border: '1px dashed #ccc',
      display: 'flex',
      alignItems: 'center',
      gap: '8px'
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
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
    style: styles[type],
    children: [type === 'loading' && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, {}), message]
  });
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
    const amazonUrl = `https://www.amazon.co.jp/gp/search?keywords=${encodeURIComponent(kwForUrl)}${settings.amazonTrackingId ? '&tag=' + settings.amazonTrackingId : ''}`;
    buttons.push(renderShopButton('amazon', amazonUrl, BUTTON_STYLES.amazon));
  }
  if (settings.rakuten && attributes.showRakuten !== false) {
    const rakutenSearchUrl = encodeURIComponent('https://search.rakuten.co.jp/search/mall/' + kwForUrl + '/');
    const rakutenUrl = `https://hb.afl.rakuten.co.jp/hgc/${settings.rakutenAffiliateId || ''}/?pc=${rakutenSearchUrl}&m=${rakutenSearchUrl}`;
    buttons.push(renderShopButton('rakuten', rakutenUrl, BUTTON_STYLES.rakuten));
  }
  if (settings.yahoo && attributes.showYahoo !== false) {
    const yahooSearchUrl = encodeURIComponent('https://search.shopping.yahoo.co.jp/search?p=' + kwForUrl);
    const yahooUrl = `https://ck.jp.ap.valuecommerce.com/servlet/referral?sid=${settings.yahooSid || ''}&pid=${settings.yahooPid || ''}&vc_url=${yahooSearchUrl}`;
    buttons.push(renderShopButton('yahoo', yahooUrl, BUTTON_STYLES.yahoo));
  }
  if (settings.mercari && attributes.showMercari !== false) {
    const mercariUrl = `https://jp.mercari.com/search?afid=${settings.mercariId || ''}&keyword=${encodeURIComponent(kwForUrl)}`;
    buttons.push(renderShopButton('mercari', mercariUrl, BUTTON_STYLES.mercari));
  }
  if (settings.dmm && attributes.showDmm !== false) {
    // DMM: キーワード設定を使用（render.phpと同じ形式）
    const dmmUrl = `https://al.dmm.com/?lurl=https%3A%2F%2Fwww.dmm.com%2Fsearch%2F%3D%2Fsearchstr%3D${encodeURIComponent(kwForUrl)}%2Fanalyze%3DV1ECCVYAUQQ_%2Flimit%3D30%2Fsort%3Drankprofile%2F&af_id=${settings.dmmId || ''}&ch=link_tool&ch_id=link`;
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
function ProductPreview({
  attributes,
  item,
  imageUrl,
  imageKey,
  itemTitle,
  itemLink
}) {
  const showImage = attributes.showImage !== false;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
    className: `plm-product-box${!showImage ? ' plm-product-box--no-image' : ''}`,
    children: [showImage && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("figure", {
      className: "plm-product-thumb",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("a", {
        rel: "nofollow noopener",
        href: itemLink,
        className: "plm-product-thumb-link",
        target: "_blank",
        title: itemTitle,
        children: imageUrl && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("img", {
          decoding: "async",
          src: imageUrl,
          alt: itemTitle || '商品画像',
          width: "128",
          height: "128",
          className: "plm-product-thumb-image"
        }, imageKey)
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
      className: "plm-product-content",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
        className: "plm-product-title",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("a", {
          rel: "nofollow noopener",
          href: itemLink,
          className: "plm-product-title-link",
          target: "_blank",
          title: itemTitle,
          children: itemTitle ? itemTitle : ''
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
        className: "plm-product-snippet",
        children: [attributes.showShop !== false && (item?.shopName || attributes.shop) && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
          className: "plm-product-maker",
          children: item?.shopName || attributes.shop
        }), attributes.price && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
          className: "plm-product-price",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("span", {
            className: "plm-price-value",
            children: item?.itemPrice ? `￥ ${Number(item.itemPrice).toLocaleString('ja-JP')}` : '価格情報なし'
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("span", {
            className: "acquired-date",
            children: item?.itemPrice ? `（${new Date().toLocaleDateString()} 時点）` : ''
          })]
        }), attributes.desc && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
          className: "plm-product-description",
          children: attributes.desc
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
        className: "plm-product-buttons",
        children: attributes.kw && attributes.kw.split(',').filter(Boolean).length > 0 && renderAllButtons(attributes)
      })]
    })]
  });
}
function Edit({
  attributes,
  setAttributes
}) {
  const [item, setItem] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(null);
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(true); // 初期状態をtrueに変更
  const [error, setError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(null);
  const [isClearingCache, setIsClearingCache] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(false);
  const [hasInitialized, setHasInitialized] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(false); // 初回取得完了フラグ
  const fetchTimeoutRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useRef)(null);
  const isInitialLoadRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useRef)(true); // 初回ロードを追跡するRef

  // 現在の投稿IDを取得
  const postId = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_6__.useSelect)(select => {
    const editor = select('core/editor');
    return editor ? editor.getCurrentPostId() : null;
  }, []);
  const fetchData = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useCallback)(async () => {
    if (!attributes.id && !attributes.no && !attributes.kw) {
      setItem(null);
      setAttributes(prev => ({
        ...prev,
        imageUrl: ''
      }));
      setIsLoading(false);
      setHasInitialized(true);
      return;
    }
    setIsLoading(true);
    setError(null);
    try {
      // キャッシュ削除は行わず、常にキャッシュを利用する
      const queryParams = new URLSearchParams({
        id: attributes.id || '',
        kw: attributes.kw || '',
        no: attributes.no || '',
        post_id: postId || ''
      });
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
        path: `/product-link-maker/v1/rakuten/?${queryParams.toString()}`
      });
      if (data && data.Items && data.Items.length > 0 && data.Items[0]?.Item) {
        // 正常にデータを取得できた場合
        setItem(data.Items[0].Item);
        setAttributes(prev => ({
          ...prev,
          imageUrl: data.Items[0].Item?.mediumImageUrls?.[0]?.imageUrl || ''
        }));
      } else if (data?.error) {
        // APIエラーレスポンス
        setItem(null);
        setAttributes(prev => ({
          ...prev,
          imageUrl: ''
        }));
        setError(data.error_description || 'APIエラーが発生しました。しばらく待ってから再度お試しください。');

        // エラーログに記録（初回データ取得完了後のみ、rate_limit以外）
        if (hasInitialized && data.error !== 'rate_limit' && (attributes.id || attributes.kw || attributes.no)) {
          try {
            await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
              path: '/product-link-maker/v1/log-client-error/',
              method: 'POST',
              data: {
                error_type: data.error,
                error_message: data.error_description || data.error,
                post_id: postId,
                item_id: attributes.id || '',
                keyword: attributes.kw || attributes.no || ''
              }
            });
          } catch (logError) {
            // エラーログの記録に失敗しても処理は継続
          }
        }
      } else {
        // データなし（商品が見つからない等）
        setItem(null);
        setAttributes(prev => ({
          ...prev,
          imageUrl: ''
        }));
        setError('データが取得できませんでした');

        // 初回データ取得完了後のみエラーログに記録（商品削除の検知）
        if (hasInitialized && (attributes.id || attributes.kw || attributes.no)) {
          try {
            await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
              path: '/product-link-maker/v1/log-client-error/',
              method: 'POST',
              data: {
                error_type: 'product_not_found',
                error_message: '商品が見つかりませんでした。商品が削除されたか、IDが正しくない可能性があります。',
                post_id: postId,
                item_id: attributes.id || '',
                keyword: attributes.kw || attributes.no || ''
              }
            });
          } catch (logError) {
            // エラーログの記録に失敗しても処理は継続
          }
        }
      }
    } catch (error) {
      console.error('Rakuten API fetch error:', error);
      setItem(null);
      setAttributes(prev => ({
        ...prev,
        imageUrl: ''
      }));

      // より詳細なエラーメッセージを表示
      let errorMessage = 'APIエラーが発生しました。';
      if (error.message) {
        errorMessage += ' ' + error.message;
      }
      if (error.data?.message) {
        errorMessage += ' ' + error.data.message;
      }
      setError(errorMessage);

      // 例外もエラーログに記録（初回データ取得完了後のみ）
      if (hasInitialized && (attributes.id || attributes.kw || attributes.no)) {
        try {
          await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
            path: '/product-link-maker/v1/log-client-error/',
            method: 'POST',
            data: {
              error_type: 'fetch_exception',
              error_message: 'API取得中に例外が発生: ' + (error.message || error.toString()),
              post_id: postId,
              item_id: attributes.id || '',
              keyword: attributes.kw || attributes.no || ''
            }
          });
        } catch (logError) {
          // エラーログの記録に失敗しても処理は継続
        }
      }
    } finally {
      setIsLoading(false);
      setHasInitialized(true);
      // 初回ロード完了後、フラグを更新
      if (isInitialLoadRef.current) {
        isInitialLoadRef.current = false;
      }
    }
  }, [attributes.id, attributes.no, attributes.kw, postId, setAttributes]);

  // キャッシュを削除して再取得する関数
  const handleClearCache = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useCallback)(async () => {
    if (!window.confirm('キャッシュを削除して最新データを取得しますか？')) {
      return;
    }
    setIsClearingCache(true);
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
        path: '/product-link-maker/v1/rakuten-cache/',
        method: 'POST',
        data: {
          id: attributes.id,
          kw: attributes.kw,
          no: attributes.no
        }
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
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
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
      setIsLoading(false);
      setHasInitialized(true);
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
  const [imageKey, setImageKey] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(0);
  const imageUrl = attributes.imageUrl || (item && item.mediumImageUrls && item.mediumImageUrls[0] ? item.mediumImageUrls[0].imageUrl : '');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    setImageKey(k => k + 1);
  }, [imageUrl]);
  const itemTitle = attributes.title || (item ? item.itemName : '');
  const itemLink = item ? item.affiliateUrl || item.itemUrl || '#' : '#';
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品情報設定', 'product-link-maker'),
        initialOpen: true,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アイテムコード（ID）', 'product-link-maker'),
          help: attributes.no ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品番号が入力されている場合は入力できません', 'product-link-maker') : '',
          value: attributes.id,
          disabled: !!attributes.no,
          onChange: val => {
            setAttributes({
              id: val,
              no: ''
            }); // 入力時にnoをクリア
          },
          placeholder: "book:11830886",
          style: attributes.no ? {
            backgroundColor: '#f5f5f5',
            color: '#aaa'
          } : {}
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品番号', 'product-link-maker'),
          help: attributes.id ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アイテムコード（ID）が入力されている場合は入力できません', 'product-link-maker') : '',
          value: attributes.no,
          disabled: !!attributes.id,
          onChange: val => {
            setAttributes({
              no: val,
              id: ''
            }); // 入力時にidをクリア
          },
          placeholder: "4902102072625",
          style: attributes.id ? {
            backgroundColor: '#f5f5f5',
            color: '#aaa'
          } : {}
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.FormTokenField, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品検索キーワード', 'product-link-maker'),
          value: attributes.kw ? attributes.kw.split(',').filter(Boolean) : [],
          onChange: tokens => setAttributes({
            kw: tokens.join(',')
          }),
          placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('キーワードを入力してEnter', 'product-link-maker')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品タイトル', 'product-link-maker'),
          value: attributes.title,
          onChange: val => setAttributes({
            title: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('店名表示', 'product-link-maker'),
          checked: attributes.showShop !== false,
          onChange: val => setAttributes({
            showShop: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('価格表示', 'product-link-maker'),
          checked: attributes.price,
          onChange: val => setAttributes({
            price: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextareaControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('説明文', 'product-link-maker'),
          value: attributes.desc,
          onChange: val => setAttributes({
            desc: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
          style: {
            margin: '24px 0 0 0'
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalHeading, {
          children: "\u30DC\u30BF\u30F3\u8868\u793A\u8A2D\u5B9A"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Amazonボタンを表示', 'product-link-maker'),
          checked: attributes.showAmazon !== false,
          onChange: val => setAttributes({
            showAmazon: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('楽天ボタンを表示', 'product-link-maker'),
          checked: attributes.showRakuten !== false,
          onChange: val => setAttributes({
            showRakuten: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Yahoo!ボタンを表示', 'product-link-maker'),
          checked: attributes.showYahoo !== false,
          onChange: val => setAttributes({
            showYahoo: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('メルカリボタンを表示', 'product-link-maker'),
          checked: attributes.showMercari !== false,
          onChange: val => setAttributes({
            showMercari: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('DMMボタンを表示', 'product-link-maker'),
          checked: attributes.showDmm !== false,
          onChange: val => setAttributes({
            showDmm: val
          })
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('カスタムボタン（前）', 'product-link-maker'),
        initialOpen: true,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(CustomButtonEditor, {
          buttons: attributes.customButtonsBefore || [],
          onChange: newButtons => setAttributes({
            customButtonsBefore: newButtons
          }),
          label: "\u65E2\u5B58\u306E\u30DC\u30BF\u30F3\u306E\u524D\u306B\u8868\u793A\u3055\u308C\u308B\u30AB\u30B9\u30BF\u30E0\u30DC\u30BF\u30F3\u3092\u8FFD\u52A0\u3067\u304D\u307E\u3059"
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('カスタムボタン（後）', 'product-link-maker'),
        initialOpen: true,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(CustomButtonEditor, {
          buttons: attributes.customButtonsAfter || [],
          onChange: newButtons => setAttributes({
            customButtonsAfter: newButtons
          }),
          label: "\u65E2\u5B58\u306E\u30DC\u30BF\u30F3\u306E\u5F8C\u306B\u8868\u793A\u3055\u308C\u308B\u30AB\u30B9\u30BF\u30E0\u30DC\u30BF\u30F3\u3092\u8FFD\u52A0\u3067\u304D\u307E\u3059"
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品画像設定', 'product-link-maker'),
        initialOpen: true,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品画像を表示', 'product-link-maker'),
          checked: attributes.showImage !== false,
          onChange: val => setAttributes({
            showImage: val
          })
        }), attributes.showImage !== false && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.MediaUploadCheck, {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
            style: {
              marginBottom: '0',
              marginTop: '16px'
            },
            children: [(attributes.imageUrl !== '' ? attributes.imageUrl : item?.mediumImageUrls?.[0]?.imageUrl) && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
              style: {
                marginBottom: '10px',
                display: 'flex',
                justifyContent: 'center',
                backgroundColor: '#eee'
              },
              children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("img", {
                src: attributes.imageUrl !== '' ? attributes.imageUrl : item?.mediumImageUrls?.[0]?.imageUrl,
                alt: "",
                style: {
                  display: 'block'
                }
              })
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.MediaUpload, {
              onSelect: media => setAttributes({
                imageUrl: media.url || ''
              }),
              allowedTypes: ['image'],
              render: ({
                open
              }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
                  onClick: open,
                  variant: "secondary",
                  children: "\u753B\u50CF\u3092\u9078\u629E"
                }), attributes.imageUrl && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
                  style: {
                    marginTop: 4
                  },
                  onClick: () => setAttributes({
                    imageUrl: ''
                  }),
                  isDestructive: true,
                  children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('画像をリセット', 'product-link-maker')
                })]
              })
            })]
          })
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アフィリエイト設定', 'product-link-maker'),
        initialOpen: false,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          href: `${window.location.origin}/wp-admin/options-general.php?page=product-link-maker`,
          target: "_blank",
          className: "",
          variant: "primary",
          icon: "admin-generic",
          style: {
            marginTop: '5px',
            width: '100%',
            textAlign: 'center'
          },
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アフィリエイト設定', 'product-link-maker')
        })
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
      ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)(),
      children: isLoading ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(StatusMessage, {
        type: "loading",
        message: "Loading..."
      }) : error ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(StatusMessage, {
        type: "error",
        message: error
      }) : !attributes.id && !attributes.no && !attributes.kw ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(StatusMessage, {
        type: "empty",
        message: "\u5546\u54C1\u60C5\u5831\u3092\u8A2D\u5B9A\u3057\u3066\u304F\u3060\u3055\u3044"
      }) : !hasInitialized || !item && (attributes.id || attributes.no) ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(StatusMessage, {
        type: "loading",
        message: "Loading..."
      }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(ProductPreview, {
          attributes: attributes,
          item: item,
          imageUrl: imageUrl,
          imageKey: imageKey,
          itemTitle: itemTitle,
          itemLink: itemLink
        }), item && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
          className: "plm-admin-info",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
            variant: "secondary",
            onClick: handleClearCache,
            disabled: isClearingCache,
            isBusy: isClearingCache,
            size: "small",
            children: isClearingCache ? '削除中...' : 'キャッシュ更新'
          }), item?.affiliateRate && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("span", {
            children: ["\u6599\u7387: ", item.affiliateRate, "%"]
          })]
        })]
      })
    })]
  });
}

/***/ }),

/***/ "./src/rakuten/editor.scss":
/*!*********************************!*\
  !*** ./src/rakuten/editor.scss ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/rakuten/index.js":
/*!******************************!*\
  !*** ./src/rakuten/index.js ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./style.scss */ "./src/rakuten/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./edit */ "./src/rakuten/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./block.json */ "./src/rakuten/block.json");
/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */


/**
 * Internal dependencies
 */



/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_3__.name, {
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_2__["default"]
});

/***/ }),

/***/ "./src/rakuten/style.scss":
/*!********************************!*\
  !*** ./src/rakuten/style.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["ReactJSXRuntime"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"rakuten/index": 0,
/******/ 			"rakuten/style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkproduct_link_maker"] = globalThis["webpackChunkproduct_link_maker"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["rakuten/style-index"], () => (__webpack_require__("./src/rakuten/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map