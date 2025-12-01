<?php

/**
 * Checkout Form - FPT Style (Final: Removed Shipping/Notes Block)
 * File: woocommerce/checkout/form-checkout.php
 */
defined('ABSPATH') || exit;

// 1. Tắt thành phần mặc định
remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);

// 2. Check Login
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo '<div class="container" style="margin-top:20px;">';
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('Bạn vui lòng đăng nhập để thanh toán.', 'woocommerce')));
    echo '</div>';
    return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout"
    action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <div class="checkout-wrapper-inner" style="margin-bottom: 50px;">

        <div style="margin-top: 15px; margin-bottom: 15px;">
            <?php if (function_exists('relive_breadcrumbs')) relive_breadcrumbs(); ?>
        </div>

        <?php wc_print_notices(); ?>

        <div class="row">
            <div class="col col-8 col-md-12">

                <div class="checkout-section-box">
                    <div class="box-header">
                        <h3 class="box-title">Sản phẩm trong đơn</h3>
                        <a href="<?php echo wc_get_cart_url(); ?>" class="edit-cart-link"
                            style="font-size: 14px; color: #1250dc; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-weight: 500;">
                            <i class="fas fa-chevron-left" style="font-size: 12px;"></i> Quay lại
                        </a>
                    </div>

                    <div class="mini-prod-list" id="fpt-checkout-left-list">
                        <?php
                        $cart_items = WC()->cart->get_cart();
                        $is_open_addon_box = false;
                        $parent_name = '';
                        $counter = 0;
                        $total_items = count($cart_items);

                        foreach ($cart_items as $key => $item) {
                            $counter++;
                            $_product = $item['data'];
                            $is_addon = isset($item['relive_is_addon']) && $item['relive_is_addon'];

                            if ($_product && $_product->exists() && $item['quantity'] > 0) {
                                $regular_price = $_product->get_regular_price();
                                $sale_price = $_product->get_price();
                                if ($is_addon) $sale_price = $item['data']->get_price();

                                // --- TRƯỜNG HỢP: SẢN PHẨM CHÍNH ---
                                if (!$is_addon) {
                                    if ($is_open_addon_box) {
                                        echo '</div></div>';
                                        $is_open_addon_box = false;
                                    }
                                    $parent_name = $_product->get_name();
                        ?>
                        <div class="mini-prod-item mini-prod-main">
                            <div class="fpt-item-left">
                                <div class="mini-prod-img"><?php echo $_product->get_image('thumbnail'); ?></div>
                                <div class="mini-prod-info">
                                    <div class="mini-prod-name"><?php echo $_product->get_name(); ?></div>
                                    <?php
                                                $attributes = $_product->get_attributes();
                                                if (!empty($attributes)) : ?>
                                    <div class="fpt-variations">
                                        <?php
                                                        foreach ($attributes as $attribute_name => $attribute_value) {
                                                            if (is_string($attribute_value)) {
                                                                if (taxonomy_exists($attribute_name)) {
                                                                    $term = get_term_by('slug', $attribute_value, $attribute_name);
                                                                    if ($term && !is_wp_error($term)) echo '<span class="fpt-tag">' . esc_html($term->name) . '</span> ';
                                                                } else {
                                                                    echo '<span class="fpt-tag">' . esc_html($attribute_value) . '</span> ';
                                                                }
                                                            } elseif (is_object($attribute_value) && $attribute_value->is_taxonomy()) {
                                                                $terms = wp_get_post_terms($_product->get_id(), $attribute_value->get_name(), 'all');
                                                                foreach ($terms as $term) echo '<span class="fpt-tag">' . esc_html($term->name) . '</span> ';
                                                            }
                                                        }
                                                        ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="fpt-item-right">
                                <div class="fpt-price-group">
                                    <span
                                        class="fpt-current-price"><?php echo wc_price($sale_price * $item['quantity']); ?></span>
                                    <?php if ($regular_price > $sale_price): ?>
                                    <del
                                        class="fpt-old-price"><?php echo wc_price($regular_price * $item['quantity']); ?></del>
                                    <?php endif; ?>
                                </div>
                                <div class="fpt-qty">x<?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <?php
                                }
                                // --- TRƯỜNG HỢP: SẢN PHẨM MUA KÈM ---
                                else {
                                    if (!$is_open_addon_box) {
                                    ?>
                        <div class="fpt-combo-box">
                            <div class="combo-header">
                                <i class="fas fa-fire"></i> Gói Phụ kiện Chuẩn - <?php echo esc_html($parent_name); ?>
                            </div>
                            <div class="combo-body">
                                <?php
                                            $is_open_addon_box = true;
                                        }
                                            ?>
                                <div class="addon-row-item">
                                    <div class="ari-img"><?php echo $_product->get_image('thumbnail'); ?></div>
                                    <div class="ari-info">
                                        <div class="ari-name"><?php echo $_product->get_name(); ?></div>
                                        <div class="ari-price">
                                            <span
                                                class="ari-curr"><?php echo wc_price($sale_price * $item['quantity']); ?></span>
                                            <?php if ($regular_price > $sale_price): ?>
                                            <del
                                                class="ari-old"><?php echo wc_price($regular_price * $item['quantity']); ?></del>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                }
                            }

                            if ($counter === $total_items && $is_open_addon_box) {
                                echo '</div></div>';
                            }
                        }
                                ?>
                            </div>
                        </div>

                        <div class="checkout-section-box">
                            <div class="box-header">
                                <h3 class="box-title">Thông tin nhận hàng</h3>
                            </div>
                            <div class="customer-info-grid"><?php do_action('woocommerce_checkout_billing'); ?></div>
                        </div>

                        <div class="checkout-section-box">
                            <div class="box-header">
                                <h3 class="box-title">Phương thức thanh toán</h3>
                            </div>
                            <div id="payment" class="woocommerce-checkout-payment">
                                <?php woocommerce_checkout_payment(); ?></div>
                        </div>

                    </div>

                    <div class="col col-4 col-md-12">
                        <?php get_template_part('template-parts/cart-sidebar'); ?>
                    </div>
                </div>
            </div>
</form>