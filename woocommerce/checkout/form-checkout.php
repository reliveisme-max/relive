<?php

/**
 * Checkout Form - FPT Style (Final Complete & Fixed)
 */
defined('ABSPATH') || exit;

// 1. Tắt thành phần mặc định
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);

// 2. Check Login
if (! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in()) {
    echo '<div class="container" style="margin-top:20px;">';
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Bạn vui lòng đăng nhập để thanh toán.', 'woocommerce')));
    echo '</div>';
    return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <div class="container" style="margin-top: 20px; margin-bottom: 50px;">

        <?php wc_print_notices(); ?>

        <div class="row">

            <div class="col col-8 col-md-12">

                <div class="checkout-section-box">
                    <div class="box-header">
                        <h3 class="box-title">Sản phẩm trong đơn</h3>
                        <a href="<?php echo wc_get_cart_url(); ?>" class="edit-cart-link">Sửa</a>
                    </div>

                    <div class="mini-prod-list" id="fpt-checkout-left-list">
                        <?php foreach (WC()->cart->get_cart() as $key => $item) {
                            $_product = $item['data'];

                            // --- LOGIC SẢN PHẨM CON (ADDON) ---
                            $is_addon = isset($item['relive_is_addon']) && $item['relive_is_addon'];
                            $addon_class = $is_addon ? 'mini-prod-addon' : '';
                            // ----------------------------------

                            if ($_product && $_product->exists() && $item['quantity'] > 0) { ?>

                        <div class="mini-prod-item <?php echo esc_attr($addon_class); ?>">

                            <?php if ($is_addon): ?><div class="addon-connector"></div><?php endif; ?>

                            <div class="mini-prod-img"><?php echo $_product->get_image('thumbnail'); ?></div>
                            <div class="mini-prod-info">
                                <div class="mini-prod-name">
                                    <?php if ($is_addon): ?><span class="badge-addon">Mua kèm</span><?php endif; ?>
                                    <?php echo $_product->get_name(); ?>
                                </div>
                                <div class="mini-prod-meta">
                                    <span>Số lượng: <strong><?php echo $item['quantity']; ?></strong></span>
                                    <span
                                        class="mini-prod-price"><?php echo WC()->cart->get_product_subtotal($_product, $item['quantity']); ?></span>
                                </div>
                            </div>

                            <a href="javascript:void(0)" class="ajax-remove-item"
                                data-key="<?php echo esc_attr($key); ?>">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                        <?php }
                        } ?>
                    </div>
                </div>

                <div class="checkout-section-box">
                    <div class="box-header">
                        <h3 class="box-title">Thông tin nhận hàng</h3>
                    </div>
                    <div class="customer-info-grid">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>
                </div>

                <div class="checkout-section-box">
                    <div class="box-header">
                        <h3 class="box-title">Hình thức nhận hàng</h3>
                    </div>
                    <div class="shipping-methods-tabs">
                        <label class="sm-tab active">
                            <input type="radio" name="shipping_option" value="delivery" checked>
                            <span><i class="fas fa-truck"></i> Giao hàng tận nơi</span>
                        </label>
                        <label class="sm-tab">
                            <input type="radio" name="shipping_option" value="pickup">
                            <span><i class="fas fa-store"></i> Nhận tại cửa hàng</span>
                        </label>
                    </div>
                    <div class="shipping-notes" style="margin-top: 15px;">
                        <?php do_action('woocommerce_checkout_shipping'); ?>
                    </div>
                </div>

                <div class="checkout-section-box">
                    <div class="box-header">
                        <h3 class="box-title">Phương thức thanh toán</h3>
                    </div>
                    <div id="payment" class="woocommerce-checkout-payment">
                        <?php woocommerce_checkout_payment(); ?>
                    </div>
                </div>

            </div>

            <div class="col col-4 col-md-12">
                <?php get_template_part('template-parts/cart-sidebar'); ?>
            </div>

        </div>
    </div>

</form>