<?php

/**
 * Tùy biến logic WooCommerce (Đã dọn dẹp các hàm trùng lặp)
 */

// 1. Thay đổi số lượng sản phẩm hiển thị
add_filter('loop_shop_per_page', 'relive_loop_shop_per_page', 20);
function relive_loop_shop_per_page($cols)
{
    $per_page = carbon_get_theme_option('shop_per_page');
    return $per_page ? $per_page : 12;
}

// 2. Tắt CSS mặc định
add_filter('woocommerce_enqueue_styles', '__return_false');

// 3. Thay đổi text nút "Add to cart"
add_filter('woocommerce_product_single_add_to_cart_text', 'relive_custom_cart_button_text');
add_filter('woocommerce_product_add_to_cart_text', 'relive_custom_cart_button_text');
function relive_custom_cart_button_text()
{
    return __('Mua ngay', 'relive');
}

// 4. Hỗ trợ hiển thị % Giảm giá
add_action('woocommerce_before_shop_loop_item_title', 'relive_show_sale_percentage', 10);
function relive_show_sale_percentage()
{
    global $product;
    if ($product->is_on_sale() && $product->get_type() != 'variable') {
        $regular_price = (float) $product->get_regular_price();
        $sale_price    = (float) $product->get_sale_price();
        if ($regular_price > 0) {
            $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            echo '<span class="onsale" style="position: absolute; top: 10px; right: 10px; background: red; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px; z-index:9;">-' . $percentage . '%</span>';
        }
    }
}

// 5. AJAX CART COUNT
add_filter('woocommerce_add_to_cart_fragments', 'relive_header_add_to_cart_fragment');
function relive_header_add_to_cart_fragment($fragments)
{
    ob_start();
    $count = WC()->cart->get_cart_contents_count();
?>
<span class="cart-count"
    style="position: absolute; top: -8px; right: -8px; background: #cb1c22; color: #fff; font-size: 10px; width: 16px; height: 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid #fff;">
    <?php echo esc_html($count); ?>
</span>
<?php
    $fragments['span.cart-count'] = ob_get_clean();
    return $fragments;
}

// 6. XÓA CÁC THÀNH PHẦN THỪA TRONG TRANG CHI TIẾT
add_filter('woocommerce_reset_variations_link', '__return_empty_string');
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);