<?php
/**
 * Tùy biến logic WooCommerce
 */

// 1. Thay đổi số lượng sản phẩm hiển thị trên trang Shop/Danh mục
add_filter( 'loop_shop_per_page', 'relive_loop_shop_per_page', 20 );
function relive_loop_shop_per_page( $cols ) {
    // Lấy giá trị từ Theme Options (vừa tạo ở trên)
    $per_page = carbon_get_theme_option( 'shop_per_page' );
    return $per_page ? $per_page : 12; // Mặc định 12 nếu chưa nhập
}

// 2. Tắt CSS mặc định của WooCommerce (Để dùng CSS của mình cho nhẹ)
add_filter( 'woocommerce_enqueue_styles', '__return_false' );

// 3. Thay đổi text nút "Add to cart"
add_filter( 'woocommerce_product_single_add_to_cart_text', 'relive_custom_cart_button_text' ); 
add_filter( 'woocommerce_product_add_to_cart_text', 'relive_custom_cart_button_text' );
function relive_custom_cart_button_text() {
    return __( 'Mua ngay', 'relive' );
}

// 4. Hỗ trợ hiển thị % Giảm giá (Flash Sale Badge)
// Flatsome có cái nhãn "Giảm XX%" rất hay, ta làm cái logic đó ở đây
add_action( 'woocommerce_before_shop_loop_item_title', 'relive_show_sale_percentage', 10 );
function relive_show_sale_percentage() {
    global $product;
    if ( $product->is_on_sale() && $product->get_type() != 'variable' ) {
        $regular_price = (float) $product->get_regular_price();
        $sale_price    = (float) $product->get_sale_price();
        
        if ( $regular_price > 0 ) {
            $percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
            echo '<span class="onsale" style="position: absolute; top: 10px; right: 10px; background: red; color: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px; z-index:9;">-' . $percentage . '%</span>';
        }
    }
}
/**
 * 5. AJAX CART COUNT UPDATE (Cập nhật số lượng giỏ hàng không cần F5)
 * Hook này sẽ chạy mỗi khi có hành động thêm/xóa sản phẩm
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'relive_header_add_to_cart_fragment' );

function relive_header_add_to_cart_fragment( $fragments ) {
    ob_start();
    
    // Lấy số lượng sản phẩm hiện tại
    $count = WC()->cart->get_cart_contents_count();
    ?>
    
    <span class="cart-count" style="
        position: absolute; top: -8px; right: -8px; 
        background: #cb1c22; color: #fff; font-size: 10px; 
        width: 16px; height: 16px; border-radius: 50%; 
        display: flex; align-items: center; justify-content: center; 
        font-weight: bold; border: 2px solid #fff;">
        <?php echo esc_html( $count ); ?>
    </span>
    
    <?php
    $fragments['span.cart-count'] = ob_get_clean(); // Thay thế thẻ span cũ bằng thẻ mới
    return $fragments;
}

/**
 * --- TÙY BIẾN TRANG CHI TIẾT SẢN PHẨM (SINGLE PRODUCT) ---
 */

// 1. Bỏ Sidebar mặc định (Để full width)
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// 2. Thêm Wrapper bao quanh ảnh và thông tin (Để chia cột bằng CSS)
add_action( 'woocommerce_before_single_product_summary', 'relive_single_wrapper_start', 5 );
function relive_single_wrapper_start() {
    echo '<div class="single-product-grid container">';
}

add_action( 'woocommerce_after_single_product_summary', 'relive_single_wrapper_end', 5 );
function relive_single_wrapper_end() {
    echo '</div>'; // Đóng .single-product-grid
}

// 3. Thêm khung "Ưu đãi" (Giống FPT Shop) trước nút Mua hàng
add_action( 'woocommerce_before_add_to_cart_form', 'relive_show_promo_box', 10 );
function relive_show_promo_box() {
    echo '
    <div class="promo-box">
        <h4 class="promo-title"><span class="dashicons dashicons-gift"></span> Ưu đãi thêm</h4>
        <ul>
            <li>✅ Tặng phiếu mua hàng 100k</li>
            <li>✅ Giảm thêm 5% khi thanh toán qua VNPAY</li>
            <li>✅ Bảo hành chính hãng 12 tháng</li>
        </ul>
    </div>
    ';
}

// 4. Bỏ bớt mấy cái rườm rà (Meta category, SKU) cho giống FPT
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );