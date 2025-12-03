<?php

/**
 * Empty Cart Page (Giao diện giỏ hàng trống giống FPT)
 */
defined('ABSPATH') || exit;
?>

<div class="container" style="margin-top: 15px; margin-bottom: 10px;">
    <?php if (function_exists('relive_breadcrumbs')) relive_breadcrumbs(); ?>
</div>

<div class="fpt-cart-page">

    <div class="cart-item-block empty-cart-box">
        <div class="ec-content">
            <h3 class="ec-title">Chưa có sản phẩm nào trong giỏ hàng</h3>
            <p class="ec-desc">Cùng mua sắm hàng ngàn sản phẩm tại cửa hàng nhé!</p>
            <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>"
                class="ec-btn">
                Mua hàng
            </a>
        </div>
        <div class="ec-image">
            <img src="/wp-content/uploads/2025/11/empty_cart.png" alt="Giỏ hàng trống">
        </div>
    </div>

</div>