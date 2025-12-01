<?php

/**
 * File: template-parts/cart-sidebar.php
 * Sidebar dùng chung cho Cart & Checkout
 */
defined('ABSPATH') || exit;

// Kiểm tra đang ở trang nào
$is_checkout = is_checkout() && !is_wc_endpoint_url('order-received');
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

    <?php if ($is_checkout) : ?>

    <div class="checkout-section-box" style="padding: 0; border: none; box-shadow: none; margin-bottom: 0;">
        <?php woocommerce_order_review(); ?>
    </div>

    <button type="submit" class="btn-fpt-primary full-width" name="woocommerce_checkout_place_order"
        id="place_order_fpt" style="margin-top: 15px;">
        Hoàn tất đặt hàng
    </button>

    <?php else : ?>

    <?php
        $total_regular = 0;
        foreach (WC()->cart->get_cart() as $cart_item) {
            $prod = $cart_item['data'];
            $qty = $cart_item['quantity'];
            $reg = $prod->get_regular_price() ? $prod->get_regular_price() : $prod->get_price();
            $total_regular += (float)$reg * $qty;
        }
        $total_discount = $total_regular - WC()->cart->get_total('edit');
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
                        <a href="<?php echo esc_url($remove_url); ?>" class="cp-remove" title="Xóa mã"
                            style="font-size: 13px; color: #288ad6;">[Xóa]</a>
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

    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-fpt-primary full-width">Xác nhận đơn</a>

    <?php endif; ?>
</div>