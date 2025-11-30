<?php

/**
 * Cart Page - Relive Theme (Final Style Fix: Inline Remove Link)
 */
defined('ABSPATH') || exit;
?>

<div style="margin-top: 15px; margin-bottom: 10px;">
    <?php if (function_exists('relive_breadcrumbs')) relive_breadcrumbs(); ?>
</div>

<div class="fpt-cart-page">
    <?php if (WC()->cart->is_empty()) : ?>

    <div class="cart-item-block empty-cart-box">
        <div class="ec-content">
            <h3 class="ec-title">Chưa có sản phẩm nào trong giỏ hàng</h3>
            <p class="ec-desc">Cùng mua sắm hàng ngàn sản phẩm tại cửa hàng nhé!</p>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="ec-btn">Mua hàng</a>
        </div>
        <div class="ec-image">
            <img src="/wp-content/uploads/2025/11/empty_cart.png" alt="Giỏ hàng trống">
        </div>
    </div>

    <?php else : ?>

    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="row">

            <div class="col col-8 col-md-12">
                <div class="cart-item-block">
                    <span style="font-weight: 600;">Giỏ hàng (<?php echo WC()->cart->get_cart_contents_count(); ?> sản
                        phẩm)</span>
                </div>

                <div class="cart-items-container">
                    <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = $cart_item['data'];
                            $is_addon = isset($cart_item['relive_is_addon']) && $cart_item['relive_is_addon'];
                            $class_name = $is_addon ? 'cart-item-addon' : 'cart-item-block';

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                        ?>
                    <div class="<?php echo esc_attr($class_name); ?>">
                        <?php if ($is_addon): ?>
                        <div class="addon-label"><i class="fas fa-gift"></i> Mua kèm ưu đãi</div>
                        <?php endif; ?>

                        <div class="fpt-cart-item-row">
                            <div class="ci-check <?php echo $is_addon ? 'hidden-check' : ''; ?>">
                                <input type="checkbox" checked class="cart-checkbox-remove"
                                    data-key="<?php echo esc_attr($cart_item_key); ?>">
                            </div>
                            <div class="ci-img">
                                <?php echo $_product->get_image('thumbnail'); ?>
                            </div>
                            <div class="ci-info">
                                <div style="display: flex; justify-content: space-between;">
                                    <h3 class="ci-name" style="margin:0;">
                                        <a href="<?php echo esc_url($_product->get_permalink()); ?>">
                                            <?php echo $_product->get_name(); ?>
                                        </a>
                                    </h3>
                                    <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>"
                                        class="remove-item ajax-remove-item"
                                        data-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                                <div class="ci-price-row">
                                    <strong style="color: #cb1c22; font-size: 16px;">
                                        <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                    </strong>
                                    <?php if ($_product->is_on_sale() || $is_addon): ?>
                                    <del style="color: #999; font-size: 13px;">
                                        <?php echo wc_price($_product->get_regular_price()); ?>
                                    </del>
                                    <?php endif; ?>
                                </div>
                                <?php if (!$is_addon) : ?>
                                <div class="ci-qty-wrap">
                                    <button type="button" class="qty-btn minus">-</button>
                                    <input type="number" name="cart[<?php echo $cart_item_key; ?>][qty]"
                                        value="<?php echo $cart_item['quantity']; ?>" class="qty-input" min="0">
                                    <button type="button" class="qty-btn plus">+</button>
                                </div>
                                <?php else: ?>
                                <div style="font-size: 13px; color: #777; margin-top: 5px;">Số lượng:
                                    <?php echo $cart_item['quantity']; ?></div>
                                <input type="hidden" name="cart[<?php echo $cart_item_key; ?>][qty]"
                                    value="<?php echo $cart_item['quantity']; ?>">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                            }
                        }
                        ?>
                    <button type="submit" class="button" name="update_cart" value="Update cart"
                        style="display: none;"></button>
                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                </div>
            </div>

            <div class="col col-4 col-md-12">
                <div class="cart-sidebar">

                    <div class="coupon-block">
                        <div class="cb-header">
                            <i class="fas fa-ticket-alt"></i> Chọn hoặc nhập ưu đãi
                        </div>
                        <div class="cb-input-group">
                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                                placeholder="Nhập mã giảm giá">
                            <button type="submit" class="button btn-apply" name="apply_coupon" value="Áp dụng">Áp
                                dụng</button>
                        </div>
                    </div>

                    <?php
                        // --- TÍNH TOÁN ---
                        $total_regular = 0;
                        $total_active = 0;

                        if (WC()->cart->get_cart()) {
                            foreach (WC()->cart->get_cart() as $cart_item) {
                                $prod = $cart_item['data'];
                                $qty = $cart_item['quantity'];
                                $reg = $prod->get_regular_price() ? $prod->get_regular_price() : $prod->get_price();
                                $total_regular += (float)$reg * $qty;
                                $total_active += $cart_item['line_subtotal'];
                            }
                        }

                        $product_discount = $total_regular - $total_active;
                        $coupon_discount  = WC()->cart->get_discount_total();
                        $total_discount   = $product_discount + $coupon_discount;
                        ?>

                    <div class="cart-totals-inner">
                        <h3 class="cart-total-title">Thông tin đơn hàng</h3>

                        <div class="row-price">
                            <span>Tổng tiền (giá niêm yết)</span>
                            <strong><?php echo wc_price($total_regular); ?></strong>
                        </div>

                        <div class="row-price">
                            <span>Tổng khuyến mãi</span>
                            <strong style="color: #28a745;">-<?php echo wc_price($total_discount); ?></strong>
                        </div>

                        <?php
                            $coupons = WC()->cart->get_coupons();
                            if (!empty($coupons)) :
                            ?>
                        <div class="fpt-applied-coupons">
                            <?php foreach ($coupons as $code => $coupon) :
                                        $code_str = (string)$code;
                                        $amount = WC()->cart->get_coupon_discount_amount($code_str);
                                        $amount_html = wc_price(abs($amount));
                                        $remove_url = add_query_arg('remove_coupon', urlencode($code_str), wc_get_cart_url());
                                    ?>
                            <div class="fpt-coupon-card">
                                <div class="cp-icon">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div class="cp-content">
                                    <div class="cp-title">Đã áp dụng mã
                                        <strong><?php echo esc_html(strtoupper($code_str)); ?></strong>
                                    </div>

                                    <div class="cp-bottom-row" style="display: flex; align-items: center; gap: 10px;">
                                        <div class="cp-amount">
                                            <span
                                                style="color: #cb1c22; font-weight: 700;">-<?php echo $amount_html; ?></span>
                                        </div>
                                        <a href="<?php echo esc_url($remove_url); ?>" class="cp-remove" title="Xóa mã"
                                            style="font-size: 13px; color: #288ad6; text-decoration: none;">
                                            [Xóa]
                                        </a>
                                    </div>

                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="discount-details"
                            style="padding-left: 15px; font-size: 13px; color: #666; margin-top: 10px;">
                            <?php if ($product_discount > 0) : ?>
                            <div class="row-price sub-row">
                                <span>• Giảm giá sản phẩm</span>
                                <span>-<?php echo wc_price($product_discount); ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($coupon_discount > 0) : ?>
                            <div class="row-price sub-row">
                                <span>• Voucher giảm giá</span>
                                <span>-<?php echo wc_price($coupon_discount); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="row-price total"
                            style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                            <span>Cần thanh toán</span>
                            <div style="text-align:right;">
                                <strong style="display:block; color: #cb1c22; font-size: 18px;">
                                    <?php wc_cart_totals_order_total_html(); ?>
                                </strong>
                                <span style="font-size: 11px; color: #999; font-weight: 400;">(Đã bao gồm VAT)</span>
                            </div>
                        </div>
                    </div>

                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-fpt-primary full-width">
                        Xác nhận đơn
                    </a>

                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<div id="fpt-delete-modal" class="fpt-cart-overlay">
    <div class="fpt-cart-box fpt-confirm-box">
        <span class="rm-close" id="btn-close-delete-modal"><i class="fas fa-times"></i></span>

        <div class="confirm-icon">
            <img src="/wp-content/uploads/2025/11/warning_mascot.png" alt="Warning">
        </div>

        <div class="confirm-title">
            Bạn muốn xoá sản phẩm này<br>ra khỏi giỏ hàng?
        </div>

        <div class="confirm-actions">
            <button type="button" id="btn-cancel-delete">Hủy bỏ</button>
            <button type="button" id="btn-confirm-delete">Xóa</button>
        </div>
    </div>
</div>

<style>
/* Style riêng cho Popup Xóa */
.fpt-confirm-box {
    background: #fff !important;
    color: #333 !important;
    width: 380px;
    max-width: 90%;
    padding: 30px 20px 25px;
    border-radius: 12px;
    text-align: center;
    position: relative;
}

.fpt-confirm-box .rm-close {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
    color: #999;
}

.confirm-icon img {
    width: 80px;
    margin: 0 auto 15px;
    display: block;
}

.confirm-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 25px;
    line-height: 1.4;
    color: #000;
}

.confirm-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.confirm-actions button {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    width: 80px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Nút Hủy (Đỏ đặc) */
#btn-cancel-delete {
    background: #cb1c22;
    color: #fff;
    border: 1px solid #cb1c22;
}

#btn-cancel-delete:hover {
    background: #a61217;
}

/* Nút Xóa (Trắng viền đỏ) */
#btn-confirm-delete {
    background: #fff;
    color: #cb1c22;
    border: 1px solid #cb1c22;
}

#btn-confirm-delete:hover {
    background: #fcebeb;
}
</style>