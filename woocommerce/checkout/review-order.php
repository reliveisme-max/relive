<?php

/**
 * Review Order Template (FPT Style - Fixed Fatal Error)
 */
defined('ABSPATH') || exit;

// --- LOGIC TÍNH TOÁN GIÁ (Giống trang Cart) ---
$total_regular = 0;
$total_active = 0;

if (WC()->cart) {
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

<div class="cart-totals-inner fpt-order-review-table">

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

                // --- SỬA LỖI TẠI ĐÂY: Tạo link thủ công thay vì dùng hàm gây lỗi ---
                $remove_url = add_query_arg('remove_coupon', urlencode($code), wc_get_cart_url());
            ?>
        <div class="fpt-coupon-card">
            <div class="cp-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="cp-content">
                <div class="cp-title">Đã áp dụng mã <strong><?php echo esc_html(strtoupper($code)); ?></strong></div>
                <div class="cp-bottom-row" style="display: flex; align-items: center; gap: 10px;">
                    <div class="cp-amount"><span
                            style="color: #cb1c22; font-weight: 700;">-<?php echo wc_price(abs($amount)); ?></span>
                    </div>
                    <a href="<?php echo esc_url($remove_url); ?>" class="cp-remove woocommerce-remove-coupon"
                        data-coupon="<?php echo esc_attr($code); ?>" style="font-size: 13px; color: #288ad6;">[Xóa]</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="discount-details" style="padding-left: 15px; font-size: 13px; color: #666; margin-top: 10px;">
        <?php if ($product_discount > 0) : ?>
        <div class="row-price sub-row"><span>• Giảm giá sản
                phẩm</span><span>-<?php echo wc_price($product_discount); ?></span></div>
        <?php endif; ?>
        <?php if ($coupon_discount > 0) : ?>
        <div class="row-price sub-row"><span>• Voucher giảm
                giá</span><span>-<?php echo wc_price($coupon_discount); ?></span></div>
        <?php endif; ?>

        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
        <?php foreach (WC()->cart->get_shipping_packages() as $package_id => $package) : ?>
        <?php foreach ($package['rates'] as $method) : ?>
        <div class="row-price sub-row">
            <span>• Phí vận chuyển</span>
            <span><?php echo ($method->cost > 0) ? wc_price($method->cost) : 'Miễn phí'; ?></span>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="row-price total" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
        <span>Cần thanh toán</span>
        <div style="text-align:right;">
            <strong style="display:block; color: #cb1c22; font-size: 18px;">
                <?php wc_cart_totals_order_total_html(); ?>
            </strong>
            <span style="font-size: 11px; color: #999; font-weight: 400;">(Đã bao gồm VAT)</span>
        </div>
    </div>

</div>