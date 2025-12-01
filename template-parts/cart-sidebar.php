<?php

/**
 * File: template-parts/cart-sidebar.php
 * Sidebar dùng chung HTML & CSS cho cả Cart & Checkout (Đồng bộ giao diện 100%)
 */
defined('ABSPATH') || exit;

// Kiểm tra đang ở trang nào
$is_checkout = is_checkout() && !is_wc_endpoint_url('order-received');

// --- 1. TÍNH TOÁN LOGIC GIÁ (Dùng chung cho cả 2 trang) ---
$total_regular = 0; // Tổng giá niêm yết
$total_sale    = 0; // Tổng giá bán thực tế

if (WC()->cart) {
    foreach (WC()->cart->get_cart() as $cart_item) {
        $prod = $cart_item['data'];
        $qty  = $cart_item['quantity'];

        // Giá gốc (Regular)
        $reg_price = $prod->get_regular_price();
        if (!$reg_price) $reg_price = $prod->get_price();

        // Giá đang bán (Sale) - Đã bao gồm logic giảm giá Addon
        $sale_price = $prod->get_price();

        $total_regular += (float)$reg_price * $qty;
        $total_sale    += (float)$sale_price * $qty;
    }
}

// Tính các khoản giảm
$product_discount = $total_regular - $total_sale; // Giảm giá trực tiếp trên sản phẩm
$coupon_discount  = WC()->cart->get_discount_total(); // Giảm giá qua Voucher
$total_discount   = $product_discount + $coupon_discount; // Tổng giảm

// Phí ship
$shipping_total = WC()->cart->get_shipping_total();
?>

<div class="cart-sidebar sticky-sidebar">

    <div class="coupon-block">
        <div class="cb-header"><i class="fas fa-ticket-alt"></i> Mã giảm giá / Ưu đãi</div>
        <div class="cb-input-group">
            <input type="text" name="<?php echo $is_checkout ? 'coupon_code_checkout' : 'coupon_code'; ?>"
                class="input-text" id="<?php echo $is_checkout ? 'coupon_code_checkout' : 'coupon_code'; ?>"
                placeholder="Nhập mã giảm giá" value="">
            <button type="<?php echo $is_checkout ? 'button' : 'submit'; ?>" class="button btn-apply"
                id="<?php echo $is_checkout ? 'apply_coupon_checkout' : 'apply_coupon'; ?>" name="apply_coupon"
                value="Áp dụng">Áp dụng</button>
        </div>
        <?php if ($is_checkout): ?>
        <div class="checkout_coupon_message" style="display:none; margin-top:10px; font-size:12px;"></div>
        <?php endif; ?>
    </div>

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

        <div class="discount-details"
            style="padding-left: 10px; font-size: 13px; color: #666; margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">

            <?php if ($product_discount > 0) : ?>
            <div class="row-price sub-row" style="margin-bottom: 5px;">
                <span>• Giảm giá sản phẩm</span>
                <span>-<?php echo wc_price($product_discount); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($coupon_discount > 0) : ?>
            <div class="row-price sub-row" style="margin-bottom: 5px;">
                <span>• Voucher giảm giá</span>
                <span>-<?php echo wc_price($coupon_discount); ?></span>
            </div>
            <?php endif; ?>

            <div class="row-price sub-row" style="margin-bottom: 5px;">
                <span>• Phí vận chuyển</span>
                <span><?php echo ($shipping_total > 0) ? wc_price($shipping_total) : 'Miễn phí'; ?></span>
            </div>
        </div>

        <?php if ($coupons = WC()->cart->get_coupons()) : ?>
        <div class="fpt-applied-coupons">
            <?php foreach ($coupons as $code => $coupon) :
                    $amount = WC()->cart->get_coupon_discount_amount($code);
                    $remove_url = add_query_arg('remove_coupon', urlencode($code), wc_get_cart_url());
                ?>
            <div class="fpt-coupon-card">
                <div class="cp-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="cp-content">
                    <div class="cp-title">Đã áp dụng mã <strong><?php echo esc_html(strtoupper($code)); ?></strong>
                    </div>
                    <div class="cp-bottom-row" style="display: flex; align-items: center; gap: 10px;">
                        <div class="cp-amount"><span
                                style="color: #cb1c22; font-weight: 700;">-<?php echo wc_price(abs($amount)); ?></span>
                        </div>
                        <?php if (!$is_checkout): ?>
                        <a href="<?php echo esc_url($remove_url); ?>" class="cp-remove" title="Xóa mã"
                            style="font-size: 13px; color: #288ad6;">[Xóa]</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="row-price total" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
            <span>Cần thanh toán</span>
            <div style="text-align:right;">
                <strong
                    style="display:block; color: #cb1c22; font-size: 18px;"><?php wc_cart_totals_order_total_html(); ?></strong>
                <span style="font-size: 11px; color: #999; font-weight: 400;">(Đã bao gồm VAT)</span>
            </div>
        </div>
    </div>

    <?php if ($is_checkout) : ?>

    <button type="submit" class="btn-fpt-primary full-width" name="woocommerce_checkout_place_order"
        id="place_order_fpt" style="margin-top: 15px;">
        HOÀN TẤT ĐẶT HÀNG
    </button>

    <div style="display:none;">
        <?php woocommerce_order_review(); ?>
    </div>

    <?php else : ?>

    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-fpt-primary full-width">
        XÁC NHẬN ĐƠN
    </a>

    <?php endif; ?>

</div>