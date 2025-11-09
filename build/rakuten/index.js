/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/rakuten/block.json":
/*!********************************!*\
  !*** ./src/rakuten/block.json ***!
  \********************************/
/***/ ((module) => {

module.exports = /*#__PURE__*/JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":3,"name":"create-block/rakuten","version":"0.1.0","title":"Rakuten","category":"widgets","icon":"smiley","description":"Example block scaffolded with Create Block tool.","example":{},"supports":{"html":false},"textdomain":"rakuten","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","render":"file:./render.php","viewScript":"file:./view.js","attributes":{"id":{"type":"string","default":""},"no":{"type":"string","default":""},"kw":{"type":"string","default":""},"shop":{"type":"string","default":""},"search":{"type":"string","default":""},"title":{"type":"string","default":""},"price":{"type":"boolean","default":false},"desc":{"type":"string","default":""},"imageUrl":{"type":"string","default":""},"showAmazon":{"type":"boolean","default":true},"showRakuten":{"type":"boolean","default":true},"showYahoo":{"type":"boolean","default":true},"showMercari":{"type":"boolean","default":true},"showDmm":{"type":"boolean","default":true}}}');

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
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__);




// import './style.scss';




function Edit({
  attributes,
  setAttributes
}) {
  const [item, setItem] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(null);
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(false);
  const [error, setError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(null);
  const fetchTimeoutRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useRef)(null);
  const fetchData = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useCallback)(async () => {
    if (!attributes.id && !attributes.no && !attributes.kw) {
      setItem(null);
      setAttributes(prev => ({
        ...prev,
        imageUrl: ''
      }));
      return;
    }
    setIsLoading(true);
    setError(null);
    try {
      // キャッシュ削除は行わず、常にキャッシュを利用する
      const data = await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
        path: `/myplugin/v1/rakuten/?id=${encodeURIComponent(attributes.id || '')}&kw=${encodeURIComponent(attributes.kw || '')}&no=${encodeURIComponent(attributes.no || '')}`
      });
      if (data?.Items?.[0]?.Item) {
        const newItem = data.Items[0].Item;
        setItem(newItem);
        const apiImage = newItem.mediumImageUrls?.[0]?.imageUrl || '';
        setAttributes(prev => ({
          ...prev,
          imageUrl: apiImage
        }));
        setError(null);
      } else if (data?.error) {
        setItem(null);
        setAttributes(prev => ({
          ...prev,
          imageUrl: ''
        }));
        setError('APIエラー: ' + (data.error_description || 'リクエスト制限に達しました。しばらく待ってから再度お試しください。'));
      } else {
        setItem(null);
        setAttributes(prev => ({
          ...prev,
          imageUrl: ''
        }));
        setError('データが取得できませんでした');
      }
    } catch (error) {
      console.error('Rakuten API fetch error:', error);
      setItem(null);
      setAttributes(prev => ({
        ...prev,
        imageUrl: ''
      }));
      setError('APIエラーが発生しました。しばらく待ってから再度お試しください。');
    } finally {
      setIsLoading(false);
    }
  }, [attributes.id, attributes.no, attributes.kw, setAttributes]);

  // キャッシュを削除して再取得する関数
  const handleClearCache = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useCallback)(async () => {
    if (!window.confirm('本当にキャッシュを削除してもいいですか？')) {
      return;
    }
    try {
      await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_5___default()({
        path: '/myplugin/v1/rakuten-cache/',
        method: 'POST',
        data: {
          id: attributes.id,
          kw: attributes.kw,
          no: attributes.no
        }
      });
      // キャッシュ削除後、再取得
      fetchData();
    } catch (error) {
      console.error('Cache clear error:', error);
    }
  }, [attributes.id, attributes.kw, attributes.no, fetchData]);

  // attributesがあれば必ずfetchDataを呼ぶ（デバウンス付き）
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
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
  const [imageKey, setImageKey] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(0);
  const imageUrl = attributes.imageUrl || (item && item.mediumImageUrls && item.mediumImageUrls[0] ? item.mediumImageUrls[0].imageUrl : '');
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    setImageKey(k => k + 1);
  }, [imageUrl]);
  const itemTitle = attributes.title || (item ? item.itemName : '');
  const itemLink = item ? item.affiliateUrl || item.itemUrl || '#' : '#';
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.Fragment, {
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.InspectorControls, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品情報設定', 'rakuten'),
        initialOpen: true,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アイテムコード（ID）', 'rakuten'),
          help: attributes.no ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品番号が入力されている場合は入力できません', 'rakuten') : '',
          value: attributes.id,
          disabled: !!attributes.no,
          onChange: val => {
            setAttributes({
              id: val,
              no: ''
            }); // 入力時にnoをクリア
          },
          style: attributes.no ? {
            backgroundColor: '#f5f5f5',
            color: '#aaa'
          } : {}
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('商品番号', 'rakuten'),
          help: attributes.id ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アイテムコード（ID）が入力されている場合は入力できません', 'rakuten') : '',
          value: attributes.no,
          disabled: !!attributes.id,
          onChange: val => {
            setAttributes({
              no: val,
              id: ''
            }); // 入力時にidをクリア
          },
          style: attributes.id ? {
            backgroundColor: '#f5f5f5',
            color: '#aaa'
          } : {}
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.FormTokenField, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('キーワード指定', 'rakuten'),
          value: attributes.kw ? attributes.kw.split(',').filter(Boolean) : [],
          onChange: tokens => setAttributes({
            kw: tokens.join(',')
          }),
          placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('キーワードを入力してEnter', 'rakuten')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('ショップコード', 'rakuten'),
          value: attributes.shop,
          onChange: val => setAttributes({
            shop: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.FormTokenField, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('サーチ', 'rakuten'),
          value: attributes.search ? attributes.search.split(',').filter(Boolean) : [],
          onChange: tokens => setAttributes({
            search: tokens.join(',')
          }),
          placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('サーチを入力してEnter', 'rakuten')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextareaControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('タイトル', 'rakuten'),
          value: attributes.title,
          onChange: val => setAttributes({
            title: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('価格表示', 'rakuten'),
          checked: attributes.price,
          onChange: val => setAttributes({
            price: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextareaControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('説明文', 'rakuten'),
          value: attributes.desc,
          onChange: val => setAttributes({
            desc: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalHeading, {
          children: "\u753B\u50CF\u30A2\u30C3\u30D7\u30ED\u30FC\u30C9"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.MediaUploadCheck, {
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            style: {
              marginBottom: '0'
            },
            children: [(attributes.imageUrl !== '' ? attributes.imageUrl : item?.mediumImageUrls?.[0]?.imageUrl) && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
              style: {
                marginBottom: '10px',
                display: 'flex',
                justifyContent: 'center',
                backgroundColor: '#eee'
              },
              children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("img", {
                src: attributes.imageUrl !== '' ? attributes.imageUrl : item?.mediumImageUrls?.[0]?.imageUrl,
                alt: "",
                style: {
                  display: 'block'
                }
              })
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.MediaUpload, {
              onSelect: media => setAttributes({
                imageUrl: media.url || ''
              }),
              allowedTypes: ['image'],
              render: ({
                open
              }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
                children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
                  onClick: open,
                  variant: "secondary",
                  children: "\u753B\u50CF\u3092\u9078\u629E"
                }), attributes.imageUrl && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
                  style: {
                    marginTop: 4
                  },
                  onClick: () => setAttributes({
                    imageUrl: ''
                  }),
                  isDestructive: true,
                  children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('画像をリセット', 'rakuten')
                })]
              })
            })]
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
          style: {
            margin: '24px 0 0 0'
          }
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalHeading, {
          children: "\u30DC\u30BF\u30F3\u8868\u793A\u8A2D\u5B9A"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Amazonボタンを表示', 'rakuten'),
          checked: attributes.showAmazon !== false,
          onChange: val => setAttributes({
            showAmazon: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('楽天ボタンを表示', 'rakuten'),
          checked: attributes.showRakuten !== false,
          onChange: val => setAttributes({
            showRakuten: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Yahoo!ボタンを表示', 'rakuten'),
          checked: attributes.showYahoo !== false,
          onChange: val => setAttributes({
            showYahoo: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('メルカリボタンを表示', 'rakuten'),
          checked: attributes.showMercari !== false,
          onChange: val => setAttributes({
            showMercari: val
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
          label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('DMMボタンを表示', 'rakuten'),
          checked: attributes.showDmm !== false,
          onChange: val => setAttributes({
            showDmm: val
          })
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, {
        title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アフィリエイト設定', 'rakuten'),
        initialOpen: false,
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
          href: `${window.location.origin}/wp-admin/admin.php?page=affiliate-settings`,
          target: "_blank",
          className: "",
          variant: "primary",
          icon: "admin-generic",
          style: {
            marginTop: '5px',
            width: '100%',
            textAlign: 'center'
          },
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('アフィリエイト設定', 'rakuten')
        })
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
      ...(0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps)(),
      children: isLoading ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        style: {
          padding: '32px',
          textAlign: 'center',
          color: '#888',
          backgroundColor: '#f5f5f5',
          border: '1px dashed #ccc',
          borderRadius: '4px'
        },
        children: "Loading..."
      }) : error ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        style: {
          padding: '32px',
          textAlign: 'center',
          color: '#d63638',
          backgroundColor: '#fff0f0',
          border: '1px solid #d63638',
          borderRadius: '4px'
        },
        children: error
      }) : !attributes.id && !attributes.no && !attributes.kw ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
        style: {
          padding: '32px',
          textAlign: 'center',
          color: '#666',
          backgroundColor: '#f5f5f5',
          border: '1px dashed #ccc',
          borderRadius: '4px'
        },
        children: "\u5546\u54C1\u60C5\u5831\u3092\u8A2D\u5B9A\u3057\u3066\u304F\u3060\u3055\u3044"
      }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        className: "rakuten-item-box product-item-box no-icon pis-m",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("figure", {
          className: "rakuten-item-thumb product-item-thumb",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
            rel: "nofollow noopener",
            href: itemLink,
            className: "rakuten-item-thumb-link product-item-thumb-link",
            target: "_blank",
            title: itemTitle,
            children: imageUrl && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("img", {
              decoding: "async",
              src: imageUrl,
              alt: itemTitle || '商品画像',
              width: "128",
              height: "128",
              className: "rakuten-item-thumb-image product-item-thumb-image"
            }, imageKey)
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
          className: "rakuten-item-content product-item-content cf",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
            className: "rakuten-item-title product-item-title",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
              rel: "nofollow noopener",
              href: itemLink,
              className: "rakuten-item-title-link product-item-title-link",
              target: "_blank",
              title: itemTitle,
              children: itemTitle ? itemTitle : ''
            })
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "rakuten-item-snippet product-item-snippet",
            children: [(item?.shopName || attributes.shop) && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
              className: "rakuten-item-maker product-item-maker",
              children: item?.shopName || attributes.shop
            }), attributes.price && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
              className: "product-item-price",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
                className: "item-price",
                children: item?.itemPrice ? `￥ ${item.itemPrice}` : '価格情報なし'
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
                className: "acquired-date",
                children: item?.itemPrice ? `（${new Date().toLocaleDateString()} 時点）` : ''
              })]
            }), attributes.desc && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
              className: "product-item-description",
              children: attributes.desc
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
            className: "amazon-item-buttons product-item-buttons",
            children: attributes.kw && attributes.kw.split(',').filter(Boolean).length > 0 && (() => {
              const kwArray = attributes.kw.split(',').map(s => s.trim()).filter(Boolean);
              const kwForUrl = kwArray.join(' ');
              const settings = window.MyAffiliateSettings || {};
              const buttons = [];
              if (settings.amazon && attributes.showAmazon !== false) {
                buttons.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
                  className: "shoplinkamazon",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
                    rel: "nofollow noopener",
                    href: `https://www.amazon.co.jp/gp/search?keywords=${encodeURIComponent(kwForUrl)}`,
                    target: "_blank",
                    style: {
                      backgroundColor: '#f79901',
                      color: '#fff',
                      borderRadius: '4px',
                      padding: '6px 16px',
                      display: 'inline-block',
                      marginRight: '6px',
                      textDecoration: 'none'
                    },
                    children: "Amazon"
                  })
                }, "amazon"));
              }
              if (settings.rakuten && attributes.showRakuten !== false) {
                buttons.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
                  className: "shoplinkrakuten",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
                    rel: "nofollow noopener",
                    href: `https://search.rakuten.co.jp/search/mall/${encodeURIComponent(kwForUrl)}/`,
                    target: "_blank",
                    style: {
                      backgroundColor: '#bf0000',
                      color: '#fff',
                      borderRadius: '4px',
                      padding: '6px 16px',
                      display: 'inline-block',
                      marginRight: '6px',
                      textDecoration: 'none'
                    },
                    children: "\u697D\u5929"
                  })
                }, "rakuten"));
              }
              if (settings.yahoo && attributes.showYahoo !== false) {
                buttons.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
                  className: "shoplinkyahoo",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
                    rel: "nofollow noopener",
                    href: `https://search.shopping.yahoo.co.jp/search?p=${encodeURIComponent(kwForUrl)}`,
                    target: "_blank",
                    style: {
                      backgroundColor: '#e60033',
                      color: '#fff',
                      borderRadius: '4px',
                      padding: '6px 16px',
                      display: 'inline-block',
                      marginRight: '6px',
                      textDecoration: 'none'
                    },
                    children: "Yahoo!\u30B7\u30E7\u30C3\u30D4\u30F3\u30B0"
                  })
                }, "yahoo"));
              }
              if (settings.mercari && attributes.showMercari !== false) {
                buttons.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
                  className: "shoplinkmercari",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
                    rel: "nofollow noopener",
                    href: `https://jp.mercari.com/search?keyword=${encodeURIComponent(kwForUrl)}`,
                    target: "_blank",
                    style: {
                      backgroundColor: '#4dc9ff',
                      color: '#fff',
                      borderRadius: '4px',
                      padding: '6px 16px',
                      display: 'inline-block',
                      marginRight: '6px',
                      textDecoration: 'none'
                    },
                    children: "\u30E1\u30EB\u30AB\u30EA"
                  })
                }, "mercari"));
              }
              if (settings.dmm && attributes.showDmm !== false) {
                buttons.push(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
                  className: "shoplinkdmm",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
                    rel: "nofollow noopener",
                    href: `https://www.dmm.com/search/=/searchstr=${encodeURIComponent(kwForUrl)}/`,
                    target: "_blank",
                    style: {
                      backgroundColor: '#00bcd4',
                      color: '#fff',
                      borderRadius: '4px',
                      padding: '6px 16px',
                      display: 'inline-block',
                      marginRight: '6px',
                      textDecoration: 'none'
                    },
                    children: "DMM"
                  })
                }, "dmm"));
              }
              return buttons;
            })()
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "product-item-admin",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
              variant: "link",
              onClick: handleClearCache,
              style: {
                color: '#0073aa',
                textDecoration: 'underline',
                cursor: 'pointer',
                padding: 0,
                marginRight: '10px'
              },
              children: "\u30AD\u30E3\u30C3\u30B7\u30E5\u524A\u9664"
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("span", {
              className: "product-affiliate-rate",
              children: ["\u6599\u7387\uFF1A", item?.affiliateRate ? `${item.affiliateRate}%` : '']
            })]
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