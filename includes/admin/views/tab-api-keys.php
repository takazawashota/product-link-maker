<?php
/**
 * APIキー設定タブ
 *
 * @package ProductLinkMaker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="plm-card">
    <h2>
        Amazon
        <?php if ( ! empty( $options['amazon_tracking_id'] ) ) : ?>
            <span class="plm-status-badge plm-status-active">設定済み</span>
        <?php else : ?>
            <span class="plm-status-badge plm-status-inactive">未設定</span>
        <?php endif; ?>
    </h2>
    <table class="form-table">
        <tr>
            <th scope="row">トラッキングID <span style="color: #d63638;">*</span></th>
            <td>
                <input type="text" name="affiliate_settings[amazon_tracking_id]" 
                       value="<?= esc_attr( $options['amazon_tracking_id'] ?? '' ) ?>" 
                       class="regular-text" 
                       placeholder="例: yourname-22" />
                <p class="description">
                    Amazonアソシエイト・プログラムのトラッキングIDを入力してください。<br>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="plm-card">
    <h2>
        楽天
        <?php if ( ! empty( $options['rakuten_affiliate_id'] ) ) : ?>
            <span class="plm-status-badge plm-status-active">設定済み</span>
        <?php else : ?>
            <span class="plm-status-badge plm-status-inactive">未設定</span>
        <?php endif; ?>
    </h2>
    <table class="form-table">
        <tr>
            <th scope="row">楽天アプリケーションID</th>
            <td>
                <input type="text" name="affiliate_settings[rakuten_app_id]" 
                       value="<?= esc_attr( $options['rakuten_app_id'] ?? '' ) ?>" 
                       class="regular-text" 
                       placeholder="例: 1234567890123456789" />
                <p class="description">
                    楽天ウェブサービスで取得したアプリケーションIDを入力してください。<br>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">楽天アフィリエイトID</th>
            <td>
                <input type="text" name="affiliate_settings[rakuten_affiliate_id]" 
                       value="<?= esc_attr( $options['rakuten_affiliate_id'] ?? '' ) ?>" 
                       class="regular-text" 
                       placeholder="例: 12345678.90123456.12345678.90123456" />
                <p class="description">
                    楽天アフィリエイトIDを入力してください。<br>
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="plm-card">
    <h2>
        Yahoo!ショッピング
        <?php if ( ! empty( $options['yahoo_sid'] ) && ! empty( $options['yahoo_pid'] ) ) : ?>
            <span class="plm-status-badge plm-status-active">設定済み</span>
        <?php else : ?>
            <span class="plm-status-badge plm-status-inactive">未設定</span>
        <?php endif; ?>
    </h2>
    <table class="form-table">
        <tr>
            <th scope="row">バリューコマースSID</th>
            <td>
                <input type="text" name="affiliate_settings[yahoo_sid]" 
                       value="<?= esc_attr( $options['yahoo_sid'] ?? '' ) ?>" 
                       class="regular-text" 
                       placeholder="例: 1234567" />
                <p class="description">
                    バリューコマースのSID（サイトID）を入力してください。
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">バリューコマースPID</th>
            <td>
                <input type="text" name="affiliate_settings[yahoo_pid]" 
                       value="<?= esc_attr( $options['yahoo_pid'] ?? '' ) ?>" 
                       class="regular-text" 
                       placeholder="例: 890123456" />
                <p class="description">バリューコマースのPID（プロモーションID）を入力してください。SIDと同じ管理画面で確認できます。</p>
            </td>
        </tr>
    </table>
</div>

<div class="plm-card">
    <h2>
        メルカリ
        <?php if ( ! empty( $options['mercari_id'] ) ) : ?>
            <span class="plm-status-badge plm-status-active">設定済み</span>
        <?php else : ?>
            <span class="plm-status-badge plm-status-inactive">未設定</span>
        <?php endif; ?>
    </h2>
    <table class="form-table">
        <tr>
            <th scope="row">メルカリアンバサダーID</th>
            <td>
                <input type="text" name="affiliate_settings[mercari_id]" 
                       value="<?= esc_attr( $options['mercari_id'] ?? '' ) ?>" 
                       class="regular-text" 
                       placeholder="例: your_ambassador_id" />
                <p class="description">
                    メルカリアンバサダープログラムのIDを入力してください。
                </p>
            </td>
        </tr>
    </table>
</div>

<div class="plm-card">
    <h2>
        DMM
        <?php if ( ! empty( $options['dmm_id'] ) ) : ?>
            <span class="plm-status-badge plm-status-active">設定済み</span>
        <?php else : ?>
            <span class="plm-status-badge plm-status-inactive">未設定</span>
        <?php endif; ?>
    </h2>
    <table class="form-table">
        <tr>
            <th scope="row">DMMアフィリエイトID</th>
            <td>
                <input type="text" name="affiliate_settings[dmm_id]" 
                       value="<?= esc_attr( $options['dmm_id'] ?? '' ) ?>" 
                       class="regular-text" 
                    placeholder="例: yourname-001" />
                <p class="description">
                    DMMアフィリエイトIDを入力してください。
                </p>
            </td>
        </tr>
    </table>
</div>
