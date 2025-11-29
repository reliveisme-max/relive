<?php

/**
 * Cart Page (FPT Style Fixed)
 */
defined('ABSPATH') || exit;
$cross_sells = array_filter(array_map('wc_get_product', WC()->cart->get_cross_sells()));
?>

<div class="container fpt-cart-page" style="margin-top: 30px; margin-bottom: 50px;">
    <?php if (WC()->cart->is_empty()) : ?>
    <div class="white-box text-center" style="padding: 50px;">
        <img src="https://cdn2.cellphones.com.vn/x,webp/media/wysiwyg/cart-empty.png"
            style="width: 150px; margin: 0 auto 20px;">
        <p style="color:#777;">Giỏ hàng đang trống.</p>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="button"
            style="background:#cb1c22; color:#fff;">Quay lại mua sắm</a>
    </div>
    <?php else : ?>

    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="row">
            <div class="col col-8 col-md-12">
                <div class="white-box" style="padding: 10px 15px; margin-bottom: 15px;">
                    <span style="font-weight: 600;">Giỏ hàng (<?php echo WC()->cart->get_cart_contents_count(); ?> sản
                        phẩm)</span>
                </div>

                <div class="cart-items-container">
                    <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = $cart_item['data'];

                            // Kiểm tra biến đã được lưu từ Session chưa
                            $is_addon = isset($cart_item['relive_is_addon']) && $cart_item['relive_is_addon'];

                            // QUAN TRỌNG: Nếu là addon thì dùng class riêng, nếu không thì dùng white-box
                            $addon_class = $is_addon ? 'cart-item-addon' : 'white-box';

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                        ?>
                    <div class="cart-item-block <?php echo esc_attr($addon_class); ?>"
                        style="margin-bottom: 10px; padding: 15px;">
                        <?php if ($is_addon): ?>
                        <div class="addon-label"><i class="fas fa-gift"></i> Mua kèm ưu đãi</div>
                        <?php endif; ?>

                        <div class="fpt-cart-item-row">
                            <div class="ci-check"><input type="checkbox" checked></div>
                            <div class="ci-img"><?php echo $_product->get_image('thumbnail'); ?></div>
                            <div class="ci-info">
                                <div style="display: flex; justify-content: space-between;">
                                    <h3 class="ci-name" style="margin:0;"><a
                                            href="<?php echo esc_url($_product->get_permalink()); ?>"><?php echo $_product->get_name(); ?></a>
                                    </h3>
                                    <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>"
                                        class="remove-item"><i class="fas fa-trash-alt"></i></a>
                                </div>
                                <div class="ci-price-row">
                                    <strong
                                        style="color: #cb1c22; font-size: 16px;"><?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?></strong>
                                    <?php if ($_product->is_on_sale() || $is_addon): ?>
                                    <del
                                        style="color: #999; font-size: 13px;"><?php echo wc_price($_product->get_regular_price()); ?></del>
                                    <?php endif; ?>
                                </div>
                                <div class="ci-qty-wrap">
                                    <button type="button" class="qty-btn minus">-</button>
                                    <input type="number" name="cart[<?php echo $cart_item_key; ?>][qty]"
                                        value="<?php echo $cart_item['quantity']; ?>" class="qty-input" min="0">
                                    <button type="button" class="qty-btn plus">+</button>
                                </div>
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
                <div class="white-box cart-sidebar">
                    <div class="coupon-block" style="margin-bottom: 20px;">
                        <div
                            style="font-weight: 600; margin-bottom: 8px; display: flex; justify-content: space-between;">
                            <span><i class="fas fa-ticket-alt" style="color: #cb1c22;"></i> Mã ưu đãi</span>
                        </div>
                        <div style="display: flex; gap: 5px;">
                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                                placeholder="Nhập mã giảm giá"
                                style="flex:1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <button type="submit" class="button" name="apply_coupon" value="Áp dụng"
                                style="background: #333; color: #fff; border: none; border-radius: 4px; font-size: 12px; padding: 0 15px;">Áp
                                dụng</button>
                        </div>
                    </div>

                    <div class="cart-totals-inner">
                        <div class="row-price"
                            style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span>Tạm tính:</span>
                            <strong><?php wc_cart_totals_subtotal_html(); ?></strong>
                        </div>
                        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                        <div class="row-price"
                            style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #28a745;">
                            <span>Giảm giá (<?php echo esc_html($code); ?>):</span>
                            <span>-<?php wc_cart_totals_coupon_html($coupon); ?></span>
                        </div>
                        <?php endforeach; ?>
                        <div class="row-price total"
                            style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 15px; margin-top: 10px;">
                            <span style="font-weight: 700;">Tổng tiền:</span>
                            <strong
                                style="color: #cb1c22; font-size: 20px;"><?php wc_cart_totals_order_total_html(); ?></strong>
                        </div>
                    </div>
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-fpt-primary full-width"
                        style="display: block; text-align: center; color: #fff; padding: 12px; border-radius: 4px; font-weight: 700; margin-top: 20px;">Xác
                        nhận đặt hàng</a>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>