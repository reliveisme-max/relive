<?php

/**
 * Relive Theme Functions - FINAL STABLE VERSION
 * Đã fix triệt để lỗi 500 khi thêm giỏ hàng
 */

define('RELIVE_VERSION', '1.0.0');
define('RELIVE_DIR', get_template_directory());
define('RELIVE_URI', get_template_directory_uri());

// 1. NẠP CÁC FILE CON
$relive_includes = array(
    '/inc/setup.php',
    '/inc/builder-fields.php',
    '/inc/woocommerce-hooks.php',
    '/inc/theme-options.php',
    '/inc/widgets.php',
    '/inc/helpers.php',
);

foreach ($relive_includes as $file) {
    if (file_exists(RELIVE_DIR . $file)) {
        require_once RELIVE_DIR . $file;
    }
}

// 2. KHỞI ĐỘNG CARBON FIELDS
add_action('after_setup_theme', 'relive_boot_carbon_fields');
function relive_boot_carbon_fields()
{
    if (class_exists('\\Carbon_Fields\\Carbon_Fields')) {
        \Carbon_Fields\Carbon_Fields::boot();
    }
}

// 3. RENDER BIẾN THỂ (SWATCHES)
add_action('wp_footer', 'relive_render_swatch_data_json', 99);
function relive_render_swatch_data_json()
{
    if (! is_product()) return;
    $product_id = get_queried_object_id();
    $product = wc_get_product($product_id);
    if (!$product) return;

    $data = array();
    $attributes = $product->get_attributes();
    if (! empty($attributes)) {
        foreach ($attributes as $attribute) {
            if ($attribute->is_taxonomy()) {
                $terms = get_terms(array('taxonomy' => $attribute->get_name(), 'hide_empty' => false));
                if (! is_wp_error($terms) && ! empty($terms)) {
                    foreach ($terms as $term) {
                        $val = function_exists('carbon_get_term_meta') ? carbon_get_term_meta($term->term_id, 'attribute_image') : '';
                        $color = function_exists('carbon_get_term_meta') ? carbon_get_term_meta($term->term_id, 'attribute_color') : '';
                        $img_url = (is_numeric($val) && $val > 0) ? wp_get_attachment_image_url($val, 'thumbnail') : $val;
                        $data[$term->slug] = array('image' => $img_url, 'color' => $color, 'name' => $term->name);
                    }
                }
            }
        }
    }
    echo '<script>var relive_swatches_json = ' . json_encode($data) . ';</script>';
}

// 4. LƯU SESSION CHO SẢN PHẨM MUA KÈM
add_filter('woocommerce_get_cart_item_from_session', 'relive_get_cart_item_from_session', 10, 2);
function relive_get_cart_item_from_session($cart_item, $values)
{
    if (isset($values['relive_is_addon'])) $cart_item['relive_is_addon'] = $values['relive_is_addon'];
    if (isset($values['relive_parent_id'])) $cart_item['relive_parent_id'] = $values['relive_parent_id'];
    return $cart_item;
}

// 5. AJAX ADD TO CART (FIXED ERROR 500)
add_action('wp_ajax_relive_add_multiple_to_cart', 'relive_ajax_add_multiple_to_cart');
add_action('wp_ajax_nopriv_relive_add_multiple_to_cart', 'relive_ajax_add_multiple_to_cart');

function relive_ajax_add_multiple_to_cart()
{
    // Tắt thông báo lỗi PHP để tránh làm hỏng JSON trả về
    error_reporting(0);
    @ini_set('display_errors', 0);

    // Bắt đầu bộ đệm output
    ob_start();

    try {
        // 1. Kiểm tra và khởi tạo WooCommerce Cart thủ công nếu cần
        if (function_exists('WC')) {
            if (!isset(WC()->cart) || empty(WC()->cart)) {
                // Nạp các file cần thiết của Woo
                include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
                include_once WC_ABSPATH . 'includes/class-wc-cart.php';

                if (function_exists('wc_load_cart')) {
                    wc_load_cart();
                }

                // Khởi tạo session nếu chưa có (Quan trọng để lưu giỏ hàng)
                if (function_exists('WC') && !WC()->session) {
                    $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
                    WC()->session = new $session_class();
                    WC()->session->init();
                }
            }
        }

        if (!isset(WC()->cart)) {
            throw new Exception('Lỗi: Không thể tải giỏ hàng WooCommerce.');
        }

        // 2. Lấy dữ liệu từ Ajax
        $items = isset($_POST['items']) ? $_POST['items'] : array();
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

        if (empty($items)) {
            throw new Exception('Chưa chọn sản phẩm nào.');
        }

        $added_count = 0;

        // 3. Duyệt qua từng sản phẩm và thêm vào giỏ
        foreach ($items as $index => $item) {
            $p_id = intval($item['id']);
            $quantity = isset($item['qty']) ? intval($item['qty']) : 1;
            $variation_id = isset($item['vid']) ? intval($item['vid']) : 0;

            if ($p_id > 0) {
                $cart_item_data = array();
                // Index > 0 là sản phẩm mua kèm
                if ($index > 0) {
                    $cart_item_data['relive_is_addon'] = true;
                    // Lấy ID cha từ phần tử đầu tiên
                    if (isset($items[0]['id'])) {
                        $cart_item_data['relive_parent_id'] = intval($items[0]['id']);
                    }
                }

                // Thêm vào giỏ hàng (Dùng try-catch riêng cho từng món để không chết cả vòng lặp)
                try {
                    $added = WC()->cart->add_to_cart($p_id, $quantity, $variation_id, array(), $cart_item_data);
                    if ($added) {
                        $added_count++;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        // 4. Áp dụng mã giảm giá (nếu có)
        $coupon_applied = false;
        if ($added_count > 0 && !empty($coupon_code)) {
            if (!WC()->cart->has_discount($coupon_code)) {
                $ret = WC()->cart->apply_coupon($coupon_code);
                if ($ret) $coupon_applied = $coupon_code;
            }
        }

        // 5. Kiểm tra kết quả
        if ($added_count == 0) {
            throw new Exception('Không thể thêm sản phẩm (Có thể hết hàng hoặc lỗi dữ liệu).');
        }

        // Tính toán lại tổng tiền
        WC()->cart->calculate_totals();

        // Lưu giỏ hàng vào session (Thay vì dùng ->save() gây lỗi ở một số version)
        if (isset(WC()->session)) {
            WC()->session->save_data();
        }

        // Xóa sạch bộ đệm trước khi trả về JSON
        if (ob_get_length()) ob_clean();

        wp_send_json_success(array(
            'redirect' => wc_get_cart_url(),
            'coupon_applied' => $coupon_applied
        ));
    } catch (Throwable $e) {
        // Bắt mọi lỗi (kể cả Fatal Error)
        if (ob_get_length()) ob_clean();
        wp_send_json_error(array('message' => 'Lỗi Server: ' . $e->getMessage()));
    }
    die();
}

// 6. TÍNH GIÁ KHUYẾN MÃI CHO SẢN PHẨM MUA KÈM
add_action('woocommerce_before_calculate_totals', 'relive_apply_addon_discount', 10, 1);
function relive_apply_addon_discount($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!function_exists('carbon_get_post_meta')) return;

    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['relive_parent_id']) && $cart_item['relive_parent_id'] > 0) {
            $parent_id = $cart_item['relive_parent_id'];
            $child_id  = $cart_item['product_id'];

            // Lấy thông tin mua kèm từ sản phẩm cha
            $bought_items = carbon_get_post_meta($parent_id, 'fpt_bought_together');
            if (!empty($bought_items)) {
                foreach ($bought_items as $item) {
                    $assoc = isset($item['product_assoc']) ? $item['product_assoc'] : array();
                    if (!empty($assoc) && $assoc[0]['id'] == $child_id) {
                        $percent = isset($item['percent_sale']) ? intval($item['percent_sale']) : 0;
                        if ($percent > 0) {
                            $price = floatval($cart_item['data']->get_price());
                            if ($price > 0) {
                                $new_price = $price * (100 - $percent) / 100;
                                $cart_item['data']->set_price($new_price);
                            }
                        }
                        break;
                    }
                }
            }
        }
    }
}

// 7. TỰ ĐỘNG XÓA SẢN PHẨM CON KHI XÓA CHA
add_action('woocommerce_remove_cart_item', 'relive_auto_remove_addons', 10, 2);
function relive_auto_remove_addons($cart_item_key, $cart)
{
    if (isset($cart->cart_contents[$cart_item_key])) {
        $removed_item = $cart->cart_contents[$cart_item_key];
        $removed_product_id = $removed_item['product_id'];

        // Nếu xóa con thì dừng, không làm gì thêm
        if (isset($removed_item['relive_is_addon']) && $removed_item['relive_is_addon']) return;

        // Nếu xóa cha -> Duyệt tìm các con của nó để xóa theo
        foreach ($cart->cart_contents as $key => $values) {
            if (isset($values['relive_parent_id']) && $values['relive_parent_id'] == $removed_product_id) {
                unset($cart->cart_contents[$key]);
            }
        }
    }
}

/* ==========================================================================
   8. REVIEW SYSTEM (ĐÁNH GIÁ)
   ========================================================================== */
add_action('wp_ajax_relive_load_reviews', 'relive_ajax_load_reviews');
add_action('wp_ajax_nopriv_relive_load_reviews', 'relive_ajax_load_reviews');
function relive_ajax_load_reviews()
{
    ob_start();
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $star = isset($_POST['star']) ? $_POST['star'] : 'all';

    $args = array('post_id' => $product_id, 'status' => 'approve', 'type' => 'review', 'number' => 5, 'paged' => $page, 'parent' => 0);
    if ($star != 'all') $args['meta_query'] = array(array('key' => 'rating', 'value' => intval($star), 'compare' => '=', 'type' => 'NUMERIC'));

    $comments = get_comments($args);
    $count_args = $args;
    unset($count_args['number'], $count_args['paged']);
    $count_args['count'] = true;
    $total = get_comments($count_args);

    ob_clean();
    if ($comments) {
        foreach ($comments as $comment) {
            // Render Cha
            relive_render_single_review($comment, false);

            // Render Con (Trả lời)
            $child_args = array('parent' => $comment->comment_ID, 'status' => 'approve', 'order' => 'ASC');
            $children = get_comments($child_args);
            if ($children) {
                echo '<div class="review-replies-wrap">';
                foreach ($children as $child) {
                    relive_render_single_review($child, true);
                }
                echo '</div>';
            }
        }
    } else {
        echo '<div class="text-center" style="padding:30px;color:#777;">Chưa có đánh giá nào.</div>';
    }

    $html = ob_get_clean();
    $pagination = paginate_links(array('base' => '%_%', 'format' => '?paged=%#%', 'total' => ceil($total / 5), 'current' => $page, 'prev_text' => '<', 'next_text' => '>'));
    wp_send_json_success(array('html' => $html, 'pagination' => $pagination));
    die();
}

function relive_render_single_review($comment, $is_reply = false)
{
    $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
    $likes = intval(get_comment_meta($comment->comment_ID, 'likes', true));
    $images = get_comment_meta($comment->comment_ID, 'review_images', true);

    $author_name = $comment->comment_author;
    $user_id     = $comment->user_id;
    $user_email  = $comment->comment_author_email;
    $post_id     = $comment->comment_post_ID;

    // Check Admin
    $is_admin = false;
    if ($user_id > 0) {
        $user_meta = get_userdata($user_id);
        if (!empty($user_meta->roles) && (in_array('administrator', (array)$user_meta->roles) || in_array('shop_manager', (array)$user_meta->roles))) {
            $is_admin = true;
        }
    }

    // Check Mua hàng
    $is_verified = false;
    if (!$is_admin && function_exists('wc_customer_bought_product')) {
        $is_verified = wc_customer_bought_product($user_email, $user_id, $post_id);
    }

    $avatar_char = mb_substr($author_name, 0, 1);
    $item_class = $is_reply ? 'review-item is-reply' : 'review-item';
?>
<div class="<?php echo esc_attr($item_class); ?>">
    <div class="ri-header">
        <?php if ($is_admin): ?>
        <div class="ri-avatar admin-avatar"><?php echo get_avatar($comment, 60); ?></div>
        <div class="ri-name"><?php echo esc_html($author_name); ?> <span class="badge-qtv">Quản trị viên</span></div>
        <?php else: ?>
        <div class="ri-avatar"><?php echo esc_html(strtoupper($avatar_char)); ?></div>
        <div class="ri-name"><?php echo esc_html($author_name); ?></div>
        <?php if ($is_verified): ?><div class="ri-check"><i class="fas fa-check-circle"></i> Đã mua tại Relive</div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="ri-content">
        <?php if (!$is_reply): ?>
        <div class="ri-stars">
            <?php for ($i = 1; $i <= 5; $i++) echo '<i class="fas fa-star ' . ($i <= $rating ? '' : 'text-muted') . '"></i>'; ?>
        </div>
        <?php endif; ?>
        <div class="ri-text"><?php echo wpautop($comment->comment_content); ?></div>

        <?php if (!empty($images) && is_array($images)): ?>
        <div class="ri-images-list" style="display: flex; gap: 10px; margin-top: 10px;">
            <?php foreach ($images as $img_id):
                        $img_url = wp_get_attachment_image_url($img_id, 'thumbnail');
                        $full_url = wp_get_attachment_image_url($img_id, 'full');
                        if ($img_url): ?>
            <a href="<?php echo esc_url($full_url); ?>" data-fancybox="review-<?php echo $comment->comment_ID; ?>">
                <img src="<?php echo esc_url($img_url); ?>"
                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #eee;">
            </a>
            <?php endif;
                    endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="ri-actions" style="margin-top: 12px; display: flex; gap: 20px; font-size: 13px;">
            <span class="ri-action-btn btn-like-review" data-id="<?php echo $comment->comment_ID; ?>"
                style="cursor: pointer; color: #333;">
                <i class="fas fa-thumbs-up"></i> <span>Thích (<?php echo $likes; ?>)</span>
            </span>
            <?php if (!$is_reply): ?>
            <span class="ri-action-btn btn-reply-trigger" data-id="<?php echo $comment->comment_ID; ?>"
                data-name="<?php echo esc_attr($author_name); ?>" style="cursor: pointer; color: #333;">
                <i class="fas fa-comment-alt"></i> Trả lời
            </span>
            <?php endif; ?>
            <span class="ri-date"
                style="color:#999; margin-left:auto;"><?php echo get_comment_date('d/m/Y - H:i', $comment->comment_ID); ?></span>
        </div>
    </div>
</div>
<?php
}

// Gửi đánh giá
add_action('wp_ajax_relive_submit_review', 'relive_ajax_submit_review');
add_action('wp_ajax_nopriv_relive_submit_review', 'relive_ajax_submit_review');
function relive_ajax_submit_review()
{
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'relive_review_nonce')) wp_send_json_error(['message' => 'Lỗi bảo mật']);

    $user_id = get_current_user_id();
    $email = '';
    if ($user_id) {
        $user_info = get_userdata($user_id);
        $email = $user_info->user_email;
    }
    $parent_id = isset($_POST['comment_parent']) ? intval($_POST['comment_parent']) : 0;

    $data = array(
        'comment_post_ID' => intval($_POST['product_id']),
        'comment_author' => sanitize_text_field($_POST['author']),
        'comment_author_email' => $email,
        'comment_content' => sanitize_textarea_field($_POST['comment']),
        'comment_type' => 'review',
        'comment_parent' => $parent_id,
        'user_id' => $user_id,
        'comment_approved' => 1
    );
    $id = wp_insert_comment($data);

    if ($id) {
        update_comment_meta($id, 'rating', intval($_POST['rating']));
        update_comment_meta($id, 'phone', sanitize_text_field($_POST['phone']));

        if (!empty($_FILES['review_image'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $attachment_ids = array();
            $files = $_FILES['review_image'];
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array('name' => $files['name'][$key], 'type' => $files['type'][$key], 'tmp_name' => $files['tmp_name'][$key], 'error' => $files['error'][$key], 'size' => $files['size'][$key]);
                    $_FILES = array('upload_file' => $file);
                    $attachment_id = media_handle_upload('upload_file', $id);
                    if (!is_wp_error($attachment_id)) $attachment_ids[] = $attachment_id;
                }
            }
            if (!empty($attachment_ids)) update_comment_meta($id, 'review_images', $attachment_ids);
        }
        wp_send_json_success(['message' => 'Thành công']);
    }
    wp_send_json_error(['message' => 'Lỗi lưu đánh giá']);
    die();
}

// 9. FILTER SYSTEM
add_action('wp_ajax_relive_get_filter_count', 'relive_ajax_get_filter_count');
add_action('wp_ajax_nopriv_relive_get_filter_count', 'relive_ajax_get_filter_count');
function relive_ajax_get_filter_count()
{
    parse_str($_POST['form_data'], $params);
    $tax_query = array('relation' => 'AND');
    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $tax_query[] = array('taxonomy' => 'pa_' . str_replace('filter_', '', $key), 'field' => 'slug', 'terms' => $value, 'operator' => 'IN');
        }
    }
    $args = array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'tax_query' => $tax_query);
    if (!empty($params['current_cat_id'])) $args['tax_query'][] = array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $params['current_cat_id']);
    $q = new WP_Query($args);
    wp_send_json_success(array('count' => $q->found_posts));
    die();
}

add_action('wp_ajax_relive_load_products', 'relive_ajax_load_products');
add_action('wp_ajax_nopriv_relive_load_products', 'relive_ajax_load_products');
function relive_ajax_load_products()
{
    parse_str($_POST['form_data'], $params);
    $tax_query = array('relation' => 'AND');
    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $tax_query[] = array('taxonomy' => 'pa_' . str_replace('filter_', '', $key), 'field' => 'slug', 'terms' => $value, 'operator' => 'IN');
        }
    }
    $args = array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 12, 'paged' => $_POST['page'], 'tax_query' => $tax_query);
    if (!empty($params['current_cat_id'])) $args['tax_query'][] = array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $params['current_cat_id']);

    $q = new WP_Query($args);
    ob_start();
    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            wc_get_template_part('content', 'product');
        }
    } else {
        echo 'Không tìm thấy sản phẩm.';
    }
    $html = ob_get_clean();
    wp_send_json_success(array('products' => $html));
    die();
}

add_action('wp_ajax_relive_like_review', 'relive_ajax_like_review');
add_action('wp_ajax_nopriv_relive_like_review', 'relive_ajax_like_review');
function relive_ajax_like_review()
{
    $comment_id = intval($_POST['comment_id']);
    $likes = get_comment_meta($comment_id, 'likes', true);
    $likes = $likes ? $likes + 1 : 1;
    update_comment_meta($comment_id, 'likes', $likes);
    wp_send_json_success(array('count' => $likes));
    die();
}
/* =============================================================
   10. AJAX XÓA SẢN PHẨM GIỎ HÀNG & CẬP NHẬT SIDEBAR
   ============================================================= */
add_action('wp_ajax_relive_remove_cart_item', 'relive_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_relive_remove_cart_item', 'relive_ajax_remove_cart_item');

function relive_ajax_remove_cart_item()
{
    // 1. Kiểm tra bảo mật
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'relive_cart_nonce')) {
        wp_send_json_error(['message' => 'Lỗi bảo mật']);
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    // 2. Xóa sản phẩm
    if ($cart_item_key && WC()->cart->get_cart_item($cart_item_key)) {
        WC()->cart->remove_cart_item($cart_item_key);
    }

    // 3. Tính toán lại
    WC()->cart->calculate_totals();
    WC()->cart->calculate_shipping();

    if (WC()->cart->is_empty()) {
        wp_send_json_success(['is_empty' => true, 'redirect' => wc_get_cart_url()]);
    }

    // 4. Render lại HTML Sidebar để JS cập nhật giá tiền
    ob_start();

    // --- COPY LOGIC TÍNH TOÁN ---
    $total_regular = 0;
    $total_active = 0;
    foreach (WC()->cart->get_cart() as $cart_item) {
        $prod = $cart_item['data'];
        $qty = $cart_item['quantity'];
        $reg = $prod->get_regular_price() ? $prod->get_regular_price() : $prod->get_price();
        $total_regular += (float)$reg * $qty;
        $total_active += $cart_item['line_subtotal'];
    }
    $product_discount = $total_regular - $total_active;
    $coupon_discount  = WC()->cart->get_discount_total();
    $total_discount   = $product_discount + $coupon_discount;
    // --- END LOGIC ---
?>

<div class="coupon-block">
    <div class="cb-header"><i class="fas fa-ticket-alt"></i> Chọn hoặc nhập ưu đãi</div>
    <div class="cb-input-group">
        <input type="text" name="coupon_code" class="input-text" id="coupon_code" placeholder="Nhập mã giảm giá">
        <button type="submit" class="button btn-apply" name="apply_coupon" value="Áp dụng">Áp dụng</button>
    </div>
</div>

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

    <?php
        $coupons = WC()->cart->get_coupons();
        if (!empty($coupons)) :
        ?>
    <div class="fpt-applied-coupons">
        <?php foreach ($coupons as $code => $coupon) :
                    $code_str = (string)$code;
                    $amount = WC()->cart->get_coupon_discount_amount($code_str);
                    $amount_html = wc_price(abs($amount));
                    $remove_url = add_query_arg('remove_coupon', urlencode($code_str), wc_get_cart_url());
                ?>
        <div class="fpt-coupon-card">
            <div class="cp-icon"><i class="fas fa-ticket-alt"></i></div>
            <div class="cp-content">
                <div class="cp-title">Đã áp dụng mã <strong><?php echo esc_html(strtoupper($code_str)); ?></strong>
                </div>
                <div class="cp-bottom-row" style="display: flex; align-items: center; gap: 10px;">
                    <div class="cp-amount"><span
                            style="color: #cb1c22; font-weight: 700;">-<?php echo $amount_html; ?></span></div>
                    <a href="<?php echo esc_url($remove_url); ?>" class="cp-remove"
                        style="font-size: 13px; color: #288ad6;">[Xóa]</a>
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
    </div>

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

<?php
    $sidebar_html = ob_get_clean();

    // 5. Trả về kết quả
    wp_send_json_success([
        'sidebar_html' => $sidebar_html,
        'cart_count'   => WC()->cart->get_cart_contents_count()
    ]);
    die();
}
/* =============================================================
   11. XỬ LÝ BỘ LỌC TRÊN URL (FIX LỖI FILTER KHÔNG CHẠY)
   ============================================================= */
add_action('woocommerce_product_query', 'relive_handle_custom_filter_query');

function relive_handle_custom_filter_query($q)
{
    // Chỉ chạy trên frontend và query chính của WooCommerce
    if (is_admin() || !$q->is_main_query()) return;

    // Lấy tax_query hiện tại (nếu có) để giữ lại các điều kiện cũ (ví dụ: đang ở danh mục nào)
    $tax_query = $q->get('tax_query');
    if (!$tax_query) $tax_query = array();

    // Quan hệ AND: Phải thỏa mãn tất cả điều kiện (vừa màu Đen VỪA 256GB)
    $tax_query['relation'] = 'AND';

    $has_filter = false;

    // Duyệt qua tất cả tham số trên URL
    foreach ($_GET as $key => $value) {
        // Kiểm tra các tham số bắt đầu bằng filter_ (VD: filter_mau-sac)
        if (strpos($key, 'filter_') === 0 && !empty($value)) {

            // Tách lấy tên thuộc tính (bỏ chữ filter_) -> VD: mau-sac
            $slug = str_replace('filter_', '', $key);

            // Tên taxonomy trong Database luôn có tiền tố pa_ (VD: pa_mau-sac)
            $taxonomy = 'pa_' . $slug;

            // Nếu value là mảng thì giữ nguyên, nếu chuỗi thì tách ra
            $terms = is_array($value) ? $value : explode(',', $value);

            // Thêm điều kiện lọc vào query
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN' // Lấy sản phẩm nằm trong danh sách terms đã chọn
            );
            $has_filter = true;
        }
    }

    // Nếu có lọc thì set lại tax_query cho WooCommerce hiểu
    if ($has_filter) {
        $q->set('tax_query', $tax_query);
    }
}