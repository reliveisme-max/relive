<?php

/**
 * Cart Page (FPT Style Updated)
 */
defined('ABSPATH') || exit;

// Lấy sản phẩm Cross-sells (Bán chéo) để làm mục "Combo ưu đãi"
$cross_sells = array_filter(array_map('wc_get_product', WC()->cart->get_cross_sells()));
?>

<div class="container fpt-cart-page"
    style="margin-top: 30px; margin-bottom: 50px; background: #f4f4f4; padding: 20px; border-radius: 8px;">

    <?php if (WC()->cart->is_empty()) : ?>
    <div class="white-box text-center" style="padding: 50px;">
        <p>Giỏ hàng đang trống.</p>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn-fpt-primary">Quay lại mua sắm</a>
    </div>
    <?php else : ?>

    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="row">

            <div class="col col-8 col-md-12">
                <div class="cart-header-bar white-box"
                    style="display: flex; justify-content: space-between; padding: 15px; margin-bottom: 10px; border-radius: 8px; border: none;">
                    <label style="font-weight: 600;">
                        <input type="checkbox" checked disabled> Chọn tất cả
                        (<?php echo WC()->cart->get_cart_contents_count(); ?>)
                    </label>
                </div>

                <div class="cart-items-container">
                    <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product   = $cart_item['data'];
                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                        ?>
                    <div class="white-box cart-item-block"
                        style="margin-bottom: 10px; border: none; border-radius: 8px; padding: 15px;">
                        <div class="fpt-cart-item-row" style="display: flex; align-items: flex-start;">
                            <div class="ci-check" style="margin-right: 15px; padding-top: 35px;">
                                <input type="checkbox" checked>
                            </div>
                            <div class="ci-img"
                                style="width: 80px; height: 80px; margin-right: 15px; border: 1px solid #eee; border-radius: 4px; padding: 5px;">
                                <?php echo $_product->get_image(); ?>
                            </div>
                            <div class="ci-info" style="flex: 1;">
                                <div style="display: flex; justify-content: space-between;">
                                    <h3 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0;">
                                        <a href="<?php echo esc_url($_product->get_permalink()); ?>"
                                            style="color: #333; text-decoration: none;">
                                            <?php echo $_product->get_name(); ?>
                                        </a>
                                    </h3>
                                    <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>"
                                        class="remove-item" style="color: #999;"><i class="fas fa-trash-alt"></i></a>
                                </div>

                                <div class="ci-price-row"
                                    style="display: flex; align-items: center; margin-bottom: 10px;">
                                    <strong style="color: #cb1c22; font-size: 16px; margin-right: 10px;">
                                        <?php echo WC()->cart->get_product_price($_product); ?>
                                    </strong>
                                    <?php if ($_product->is_on_sale()): ?>
                                    <del
                                        style="color: #999; font-size: 13px;"><?php echo wc_price($_product->get_regular_price()); ?></del>
                                    <?php endif; ?>
                                </div>

                                <div class="ci-qty-wrap"
                                    style="display: inline-flex; border: 1px solid #ddd; border-radius: 4px; height: 30px;">
                                    <button type="button" class="qty-btn minus"
                                        style="width: 30px; border: none; background: #fff;">-</button>
                                    <input type="number" name="cart[<?php echo $cart_item_key; ?>][qty]"
                                        value="<?php echo $cart_item['quantity']; ?>" class="qty-input"
                                        style="width: 40px; border: none; text-align: center; font-weight: 600;"
                                        min="0">
                                    <button type="button" class="qty-btn plus"
                                        style="width: 30px; border: none; background: #fff;">+</button>
                                </div>
                            </div>
                        </div>

                        <div class="cart-combo-section"
                            style="margin-top: 15px; border-top: 1px dashed #eee; padding-top: 15px;">
                            <h4
                                style="font-size: 13px; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center;">
                                <i class="fas fa-fire" style="color: #cb1c22; margin-right: 5px;"></i> Combo ưu đãi
                            </h4>

                            <?php if (!empty($cross_sells)): foreach ($cross_sells as $cs_product): ?>
                            <div class="combo-item"
                                style="display: flex; align-items: center; justify-content: space-between; border: 1px solid #eee; border-radius: 6px; padding: 8px; margin-bottom: 8px;">
                                <div style="display: flex; align-items: center;">
                                    <input type="checkbox" class="combo-check" style="margin-right: 10px;">
                                    <?php echo $cs_product->get_image('thumbnail', array('style' => 'width: 40px; height: 40px; object-fit: contain; margin-right: 10px;')); ?>
                                    <div>
                                        <div style="font-size: 12px; font-weight: 600;">
                                            <?php echo $cs_product->get_name(); ?></div>
                                        <div style="font-size: 12px; color: #cb1c22; font-weight: 700;">
                                            <?php echo $cs_product->get_price_html(); ?></div>
                                    </div>
                                </div>
                                <a href="?add-to-cart=<?php echo $cs_product->get_id(); ?>" class="view-detail"
                                    style="font-size: 12px; color: #288ad6; font-weight: 500;">Chi tiết</a>
                            </div>
                            <?php endforeach;
                                        else: ?>
                            <p style="font-size: 12px; color: #777;">Không có ưu đãi đi kèm cho sản phẩm này.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                            }
                        }
                        ?>
                    <button type="submit" class="button" name="update_cart" style="display: none;">Update</button>
                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
                </div>
            </div>

            <div class="col col-4 col-md-12">
                <div class="white-box cart-sidebar"
                    style="border: none; border-radius: 8px; padding: 15px; position: sticky; top: 20px;">

                    <div
                        style="border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span style="font-weight: 600; font-size: 14px;"><i class="fas fa-gift"></i> Quà tặng</span>
                        <a href="#" style="font-size: 13px; color: #288ad6;">Xem quà</a>
                    </div>

                    <div class="coupon-block"
                        style="background: #f4f4f4; padding: 10px; border-radius: 6px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                        <span style="font-size: 13px; font-weight: 600; color: #cb1c22;"><i
                                class="fas fa-ticket-alt"></i> Chọn hoặc nhập ưu đãi</span>
                        <i class="fas fa-chevron-right" style="font-size: 12px; color: #999;"></i>
                    </div>

                    <h4 style="font-size: 14px; font-weight: 700; margin-bottom: 10px;">Thông tin đơn hàng</h4>
                    <div class="row-price"
                        style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;">
                        <span>Tổng tiền</span>
                        <strong><?php wc_cart_totals_subtotal_html(); ?></strong>
                    </div>
                    <div class="row-price"
                        style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 13px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">
                        <span>Tổng khuyến mãi</span>
                        <span>0đ</span>
                    </div>

                    <div class="row-price total"
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                        <span style="font-weight: 600;">Cần thanh toán</span>
                        <strong
                            style="color: #cb1c22; font-size: 18px;"><?php wc_cart_totals_order_total_html(); ?></strong>
                    </div>
                    <div style="text-align: right; font-size: 11px; color: #f5a623; margin-bottom: 15px;">
                        Điểm thưởng: +<?php echo intval(WC()->cart->total / 1000); ?>
                    </div>

                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="btn-fpt-primary full-width"
                        style="display: block; text-align: center; background: #cb1c22; color: #fff; padding: 12px; border-radius: 6px; font-weight: 700; text-transform: uppercase; text-decoration: none;">
                        Xác nhận đơn
                    </a>
                </div>
            </div>

        </div>
    </form>

    <?php endif; ?>
</div>