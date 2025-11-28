<?php

/**
 * Tùy biến logic WooCommerce
 */

// 1. Thay đổi số lượng sản phẩm hiển thị trên trang Shop/Danh mục
add_filter('loop_shop_per_page', 'relive_loop_shop_per_page', 20);
function relive_loop_shop_per_page($cols)
{
    // Lấy giá trị từ Theme Options (vừa tạo ở trên)
    $per_page = carbon_get_theme_option('shop_per_page');
    return $per_page ? $per_page : 12; // Mặc định 12 nếu chưa nhập
}

// 2. Tắt CSS mặc định của WooCommerce (Để dùng CSS của mình cho nhẹ)
add_filter('woocommerce_enqueue_styles', '__return_false');

// 3. Thay đổi text nút "Add to cart"
add_filter('woocommerce_product_single_add_to_cart_text', 'relive_custom_cart_button_text');
add_filter('woocommerce_product_add_to_cart_text', 'relive_custom_cart_button_text');
function relive_custom_cart_button_text()
{
    return __('Mua ngay', 'relive');
}

// 4. Hỗ trợ hiển thị % Giảm giá (Flash Sale Badge)
// Flatsome có cái nhãn "Giảm XX%" rất hay, ta làm cái logic đó ở đây
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
/**
 * 5. AJAX CART COUNT UPDATE (Cập nhật số lượng giỏ hàng không cần F5)
 * Hook này sẽ chạy mỗi khi có hành động thêm/xóa sản phẩm
 */
add_filter('woocommerce_add_to_cart_fragments', 'relive_header_add_to_cart_fragment');

function relive_header_add_to_cart_fragment($fragments)
{
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
    <?php echo esc_html($count); ?>
</span>

<?php
    $fragments['span.cart-count'] = ob_get_clean(); // Thay thế thẻ span cũ bằng thẻ mới
    return $fragments;
}

/**
 * --- XỬ LÝ BỘ LỌC NÂNG CAO (OPTIMIZED FILTER) ---
 * Tự động bắt các tham số ?filter_... trên URL và lọc sản phẩm
 */
add_action('woocommerce_product_query', 'relive_advanced_product_filter');

function relive_advanced_product_filter($q)
{
    // Chỉ chạy ở trang danh mục sản phẩm và truy vấn chính
    if (is_admin() || ! $q->is_main_query()) {
        return;
    }

    // 1. LẤY CÁC THAM SỐ TRÊN URL
    // Chúng ta tìm các tham số bắt đầu bằng 'filter_' (ví dụ: filter_color, filter_ram)
    $tax_query = $q->get('tax_query');
    if (! $tax_query) {
        $tax_query = array();
    }

    foreach ($_GET as $key => $value) {
        if (strpos($key, 'filter_') === 0 && ! empty($value)) {
            // Lấy tên thuộc tính (Bỏ chữ filter_)
            // Ví dụ: filter_color -> color. WooCommerce lưu là pa_color
            $slug = str_replace('filter_', '', $key);
            $taxonomy = 'pa_' . $slug; // Chuẩn của Woo là phải có pa_

            // Nếu giá trị là chuỗi ngăn cách bởi dấu phẩy (nếu chọn nhiều)
            if (is_string($value)) {
                $terms = explode(',', $value);
            } elseif (is_array($value)) {
                $terms = $value;
            } else {
                continue;
            }

            // Thêm điều kiện lọc
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN', // Lấy sản phẩm nằm trong danh sách chọn
            );
        }
    }

    // 2. TỐI ƯU HÓA QUERY (QUAN TRỌNG KHI SẢN PHẨM NHIỀU)
    // Nếu có nhiều điều kiện lọc, dùng quan hệ AND (Phải thỏa mãn tất cả)
    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }

    // Nạp lại query đã sửa
    $q->set('tax_query', $tax_query);
}

/**
 * --- AJAX FILTER COUNT (Đếm số lượng sản phẩm theo bộ lọc) ---
 */
add_action('wp_ajax_relive_get_filter_count', 'relive_ajax_get_filter_count');
add_action('wp_ajax_nopriv_relive_get_filter_count', 'relive_ajax_get_filter_count');

function relive_ajax_get_filter_count()
{
    // Check nonce bảo mật (Tùy chọn, nhưng nên có)
    // check_ajax_referer( 'relive_filter_nonce', 'nonce' );

    // 1. Chuẩn bị Query Args
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,      // Lấy tất cả để đếm
        'fields'         => 'ids',   // TỐI ƯU: Chỉ lấy ID cho nhẹ, không lấy nội dung
        'tax_query'      => array('relation' => 'AND'),
    );

    // 2. Lấy dữ liệu từ Form gửi lên
    $params = array();
    parse_str($_POST['form_data'], $params);

    // 3. Xử lý Danh mục hiện tại (nếu đang ở trang category)
    if (! empty($params['product_cat'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $params['product_cat'],
        );
    }

    // 4. Xử lý các bộ lọc (filter_...)
    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && ! empty($value)) {
            $slug = str_replace('filter_', '', $key);
            $taxonomy = 'pa_' . $slug;

            $args['tax_query'][] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $value, // $value ở đây là mảng các mục đã chọn
                'operator' => 'IN',
            );
        }
    }

    // 5. Chạy Query đếm
    $query = new WP_Query($args);
    $count = $query->found_posts;

    // 6. Trả kết quả về cho JS
    wp_send_json_success(array('count' => $count));
}


/**
 * --- AJAX PAGINATION (PHÂN TRANG KHÔNG LOAD LẠI) ---
 */
add_action('wp_ajax_relive_load_products', 'relive_ajax_load_products');
add_action('wp_ajax_nopriv_relive_load_products', 'relive_ajax_load_products');

function relive_ajax_load_products()
{
    // 1. Nhận dữ liệu từ JS
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : '';
    $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'date'; // Lấy sắp xếp

    // Parse chuỗi form data thành mảng
    $params = array();
    parse_str($form_data, $params);

    // 2. Chuẩn bị Query
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => carbon_get_theme_option('shop_per_page') ? carbon_get_theme_option('shop_per_page') : 12,
        'paged'          => $page,
        'tax_query'      => array('relation' => 'AND'),
    );

    // Xử lý sắp xếp (Orderby)
    if ($orderby == 'price') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_price';
        $args['order'] = 'ASC';
    } elseif ($orderby == 'price-desc') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = '_price';
        $args['order'] = 'DESC';
    } elseif ($orderby == 'popularity') {
        $args['orderby'] = 'meta_value_num';
        $args['meta_key'] = 'total_sales';
        $args['order'] = 'DESC';
    } else {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
    }

    // 3. Tái tạo bộ lọc (Giống hệt hàm đếm số lượng)
    // - Danh mục hiện tại
    if (! empty($params['product_cat'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $params['product_cat'],
        );
    }
    // - Các bộ lọc thuộc tính
    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && ! empty($value)) {
            $slug = str_replace('filter_', '', $key);
            $args['tax_query'][] = array(
                'taxonomy' => 'pa_' . $slug,
                'field'    => 'slug',
                'terms'    => $value,
                'operator' => 'IN',
            );
        }
    }

    // 4. Chạy Query
    $query = new WP_Query($args);

    // 5. Trả về HTML
    if ($query->have_posts()) {
        // A. HTML Danh sách sản phẩm
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
        $products_html = ob_get_clean();

        // B. HTML Phân trang mới (FIX LỖI MẤT PHÂN TRANG)
        ob_start();
        // Quan trọng: Truyền $query vào để hàm phân trang biết tổng số trang
        relive_pagination($query);
        $pagination_html = ob_get_clean();

        // C. HTML Số lượng kết quả
        $total = $query->found_posts;
        $first = ($page - 1) * $args['posts_per_page'] + 1;
        $last = min($total, $page * $args['posts_per_page']);
        $result_count_html = 'Hiển thị <b>' . $first . '-' . $last . '</b> trong <b>' . $total . '</b> kết quả';

        wp_send_json_success(array(
            'products' => $products_html,
            'pagination' => $pagination_html, // HTML phân trang mới sẽ được gửi về
            'result_count' => $result_count_html
        ));
    } else {
        wp_send_json_error();
    }
    wp_die();
}