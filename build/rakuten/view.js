/******/ (() => { // webpackBootstrap
/*!*****************************!*\
  !*** ./src/rakuten/view.js ***!
  \*****************************/
/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

// フロントエンド表示時のエラーログ記録
document.addEventListener('DOMContentLoaded', function () {
  // エラーフォールバック要素を検索
  const errorBlocks = document.querySelectorAll('.plm-error-fallback');
  if (errorBlocks.length === 0) {
    return; // エラーがない場合は何もしない
  }

  // 各エラーブロックを処理
  errorBlocks.forEach(function (errorBlock) {
    // data属性からエラー情報を取得
    const errorType = errorBlock.dataset.errorType || 'unknown_error';
    const errorMessage = errorBlock.dataset.errorMessage || 'エラーが発生しました';
    const postId = errorBlock.dataset.postId || '';
    const itemId = errorBlock.dataset.itemId || '';
    const keyword = errorBlock.dataset.keyword || '';

    // レート制限エラーは記録しない
    if (errorType === 'rate_limit') {
      return;
    }

    // REST APIでエラーログを記録
    fetch('/wp-json/product-link-maker/v1/log-client-error/', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        error_type: errorType,
        error_message: errorMessage,
        post_id: postId,
        item_id: itemId,
        keyword: keyword
      })
    }).catch(function (error) {
      // エラーログの記録に失敗しても何もしない（ユーザー体験に影響させない）
      console.debug('Failed to log error:', error);
    });
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map