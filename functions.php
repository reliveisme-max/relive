<?php

/**
 * Relive Theme Functions - FINAL STABLE VERSION (FIXED PARSE ERROR)
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

// 5. AJAX ADD TO CART
add_action('wp_ajax_relive_add_multiple_to_cart', 'relive_ajax_add_multiple_to_cart');
add_action('wp_ajax_nopriv_relive_add_multiple_to_cart', 'relive_ajax_add_multiple_to_cart');

function relive_ajax_add_multiple_to_cart()
{
    error_reporting(0);
    @ini_set('display_errors', 0);
    ob_start();

    try {
        if (function_exists('WC')) {
            if (!isset(WC()->cart) || empty(WC()->cart)) {
                include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
                include_once WC_ABSPATH . 'includes/class-wc-cart.php';
                if (function_exists('wc_load_cart')) wc_load_cart();
                if (function_exists('WC') && !WC()->session) {
                    $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
                    WC()->session = new $session_class();
                    WC()->session->init();
                }
            }
        }

        if (!isset(WC()->cart)) throw new Exception('Lỗi: Không thể tải giỏ hàng WooCommerce.');

        $items = isset($_POST['items']) ? $_POST['items'] : array();
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

        if (empty($items)) throw new Exception('Chưa chọn sản phẩm nào.');

        $added_count = 0;
        foreach ($items as $index => $item) {
            $p_id = intval($item['id']);
            $quantity = isset($item['qty']) ? intval($item['qty']) : 1;
            $variation_id = isset($item['vid']) ? intval($item['vid']) : 0;

            if ($p_id > 0) {
                $cart_item_data = array();
                if ($index > 0) {
                    $cart_item_data['relive_is_addon'] = true;
                    if (isset($items[0]['id'])) $cart_item_data['relive_parent_id'] = intval($items[0]['id']);
                }
                try {
                    if (WC()->cart->add_to_cart($p_id, $quantity, $variation_id, array(), $cart_item_data)) {
                        $added_count++;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
        }

        $coupon_applied = false;
        if ($added_count > 0 && !empty($coupon_code)) {
            if (!WC()->cart->has_discount($coupon_code)) {
                if (WC()->cart->apply_coupon($coupon_code)) $coupon_applied = $coupon_code;
            }
        }

        if ($added_count == 0) throw new Exception('Không thể thêm sản phẩm.');

        WC()->cart->calculate_totals();
        if (isset(WC()->session)) WC()->session->save_data();

        if (ob_get_length()) ob_clean();
        wp_send_json_success(array(
            'redirect' => wc_get_cart_url(),
            'coupon_applied' => $coupon_applied
        ));
    } catch (Throwable $e) {
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
        if (isset($removed_item['relive_is_addon']) && $removed_item['relive_is_addon']) return;
        foreach ($cart->cart_contents as $key => $values) {
            if (isset($values['relive_parent_id']) && $values['relive_parent_id'] == $removed_product_id) {
                unset($cart->cart_contents[$key]);
            }
        }
    }
}

// 8. REVIEW SYSTEM (ĐÁNH GIÁ)
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
            relive_render_single_review($comment, false);
            $child_args = array('parent' => $comment->comment_ID, 'status' => 'approve', 'order' => 'ASC');
            $children = get_comments($child_args);
            if ($children) {
                echo '<div class="review-replies-wrap">';
                foreach ($children as $child) relive_render_single_review($child, true);
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
    $user_id = $comment->user_id;
    $user_email = $comment->comment_author_email;
    $post_id = $comment->comment_post_ID;
    $is_admin = false;
    if ($user_id > 0) {
        $user_meta = get_userdata($user_id);
        if (!empty($user_meta->roles) && (in_array('administrator', (array)$user_meta->roles) || in_array('shop_manager', (array)$user_meta->roles))) {
            $is_admin = true;
        }
    }
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
                style="cursor: pointer; color: #333;"><i class="fas fa-thumbs-up"></i> <span>Thích
                    (<?php echo $likes; ?>)</span></span>
            <?php if (!$is_reply): ?>
            <span class="ri-action-btn btn-reply-trigger" data-id="<?php echo $comment->comment_ID; ?>"
                data-name="<?php echo esc_attr($author_name); ?>" style="cursor: pointer; color: #333;"><i
                    class="fas fa-comment-alt"></i> Trả lời</span>
            <?php endif; ?>
            <span class="ri-date"
                style="color:#999; margin-left:auto;"><?php echo get_comment_date('d/m/Y - H:i', $comment->comment_ID); ?></span>
        </div>
    </div>
</div>
<?php
}

// Submit Review
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
    $data = array('comment_post_ID' => intval($_POST['product_id']), 'comment_author' => sanitize_text_field($_POST['author']), 'comment_author_email' => $email, 'comment_content' => sanitize_textarea_field($_POST['comment']), 'comment_type' => 'review', 'comment_parent' => $parent_id, 'user_id' => $user_id, 'comment_approved' => 1);
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

// Filter System
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

// Like Review
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

// 10. AJAX REMOVE CART ITEM (FIXED & TESTED)
add_action('wp_ajax_relive_remove_cart_item', 'relive_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_relive_remove_cart_item', 'relive_ajax_remove_cart_item');

function relive_ajax_remove_cart_item()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'relive_cart_nonce')) {
        wp_send_json_error(['message' => 'Lỗi bảo mật']);
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $is_checkout   = isset($_POST['is_checkout']) && $_POST['is_checkout'] === 'true';

    if ($cart_item_key && WC()->cart->get_cart_item($cart_item_key)) {
        WC()->cart->remove_cart_item($cart_item_key);
    }

    WC()->cart->calculate_totals();
    WC()->cart->calculate_shipping();

    if (WC()->cart->is_empty()) {
        wp_send_json_success(['is_empty' => true, 'redirect' => wc_get_cart_url()]);
    }

    $response = ['cart_count' => WC()->cart->get_cart_contents_count()];

    ob_start();
    get_template_part('template-parts/cart-sidebar');
    $response['sidebar_html'] = ob_get_clean();

    if ($is_checkout) {
        ob_start();
        foreach (WC()->cart->get_cart() as $key => $item) {
            $_prod = $item['data'];
            $is_addon = isset($item['relive_is_addon']) && $item['relive_is_addon'];
            $addon_class = $is_addon ? 'mini-prod-addon' : '';

            if ($_prod && $_prod->exists() && $item['quantity'] > 0) {
    ?>
<div class="mini-prod-item <?php echo esc_attr($addon_class); ?>" style="position: relative;">
    <?php if ($is_addon): ?><div class="addon-connector"></div><?php endif; ?>
    <div class="mini-prod-img"><?php echo $_prod->get_image('thumbnail'); ?></div>
    <div class="mini-prod-info">
        <div class="mini-prod-name">
            <?php if ($is_addon): ?><span class="badge-addon">Mua kèm</span><?php endif; ?>
            <?php echo $_prod->get_name(); ?>
        </div>
        <div class="mini-prod-meta">
            <span>Số lượng: <strong><?php echo $item['quantity']; ?></strong></span>
            <span
                class="mini-prod-price"><?php echo WC()->cart->get_product_subtotal($_prod, $item['quantity']); ?></span>
        </div>
    </div>
</div>
<?php
            }
        }
        $response['checkout_left_html'] = ob_get_clean();

        ob_start();
        woocommerce_order_review();
        $response['checkout_review_html'] = ob_get_clean();
    }

    wp_send_json_success($response);
    die();
}

// 11. FILTER URL HANDLER
add_action('woocommerce_product_query', 'relive_handle_custom_filter_query');
function relive_handle_custom_filter_query($q)
{
    if (is_admin() || !$q->is_main_query()) return;
    $tax_query = $q->get('tax_query');
    if (!$tax_query) $tax_query = array();
    $tax_query['relation'] = 'AND';
    $has_filter = false;
    foreach ($_GET as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $slug = str_replace('filter_', '', $key);
            $taxonomy = 'pa_' . $slug;
            $terms = is_array($value) ? $value : explode(',', $value);
            $tax_query[] = array('taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => $terms, 'operator' => 'IN');
            $has_filter = true;
        }
    }
    if ($has_filter) $q->set('tax_query', $tax_query);
}

// 12. CHECKOUT CLEANUP
// --- TÙY BIẾN FORM THANH TOÁN (SẮP XẾP & THÊM TRƯỜNG ĐỊA CHỈ) ---
add_filter('woocommerce_checkout_fields', 'relive_custom_checkout_fields');
function relive_custom_checkout_fields($fields)
{
    // 1. Xóa các trường không cần thiết
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_country']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['shipping']);

    // 2. Chỉnh sửa & Sắp xếp

    // --- HỌ VÀ TÊN ---
    $fields['billing']['billing_first_name']['label'] = 'Họ và tên';
    $fields['billing']['billing_first_name']['placeholder'] = 'Nhập họ tên (bắt buộc)';
    $fields['billing']['billing_first_name']['class'] = array('form-row-first');
    $fields['billing']['billing_first_name']['priority'] = 10;

    // --- SỐ ĐIỆN THOẠI ---
    $fields['billing']['billing_phone']['label'] = 'Số điện thoại';
    $fields['billing']['billing_phone']['placeholder'] = 'Nhập số điện thoại (bắt buộc)';
    $fields['billing']['billing_phone']['class'] = array('form-row-last');
    $fields['billing']['billing_phone']['priority'] = 20;

    // --- EMAIL ---
    $fields['billing']['billing_email']['label'] = 'Email';
    $fields['billing']['billing_email']['placeholder'] = 'Nhập email (để nhận hóa đơn)';
    $fields['billing']['billing_email']['class'] = array('form-row-wide');
    $fields['billing']['billing_email']['priority'] = 30;

    // === MỚI: TÙY CHỌN PHIÊN BẢN ĐỊA CHỈ ===
    $fields['billing']['billing_address_mode'] = array(
        'type'    => 'radio',
        'label'   => 'Chọn bộ dữ liệu hành chính:',
        'required' => true,
        'class'   => array('form-row-wide', 'address-mode-selector'),
        'options' => array(
            'v1' => 'Địa chỉ Cũ (Trước sáp nhập)',
            'v2' => 'Địa chỉ Mới (Sau sáp nhập)',
        ),
        'default' => 'v1', // Mặc định dùng bản cũ cho ổn định
        'priority' => 35,
    );

    // --- TỈNH / THÀNH PHỐ ---
    $fields['billing']['billing_city']['type'] = 'select';
    $fields['billing']['billing_city']['label'] = 'Tỉnh / Thành phố';
    $fields['billing']['billing_city']['options'] = array('' => 'Đang tải dữ liệu...');
    $fields['billing']['billing_city']['class'] = array('form-row-wide', 'address-select-field');
    $fields['billing']['billing_city']['priority'] = 40;

    // --- QUẬN / HUYỆN ---
    $fields['billing']['billing_district'] = array(
        'type'        => 'select',
        'label'       => 'Quận / Huyện',
        'required'    => true,
        'class'       => array('form-row-first', 'address-select-field'),
        'priority'    => 50,
        'options'     => array('' => 'Chọn Quận / Huyện'),
    );

    // --- PHƯỜNG / XÃ ---
    $fields['billing']['billing_ward'] = array(
        'type'        => 'select',
        'label'       => 'Phường / Xã',
        'required'    => true,
        'class'       => array('form-row-last', 'address-select-field'),
        'priority'    => 60,
        'options'     => array('' => 'Chọn Phường / Xã'),
    );

    // --- ĐỊA CHỈ CỤ THỂ ---
    $fields['billing']['billing_address_1']['label'] = 'Số nhà, tên đường';
    $fields['billing']['billing_address_1']['placeholder'] = 'Ví dụ: Số 10, Ngõ 50...';
    $fields['billing']['billing_address_1']['class'] = array('form-row-wide');
    $fields['billing']['billing_address_1']['priority'] = 70;

    unset($fields['billing']['billing_address_2']);

    return $fields;
}

// Lưu Quận/Huyện & Phường/Xã vào đơn hàng
add_action('woocommerce_checkout_update_order_meta', 'relive_save_new_checkout_fields');
function relive_save_new_checkout_fields($order_id)
{
    if (!empty($_POST['billing_district'])) update_post_meta($order_id, '_billing_district', sanitize_text_field($_POST['billing_district']));
    if (!empty($_POST['billing_ward'])) update_post_meta($order_id, '_billing_ward', sanitize_text_field($_POST['billing_ward']));
}

add_filter('woocommerce_default_address_fields', 'relive_override_default_address_fields');
function relive_override_default_address_fields($fields)
{
    if (isset($fields['address_1'])) $fields['address_1']['required'] = false;
    if (isset($fields['city'])) $fields['city']['required'] = false;
    return $fields;
}

add_filter('woocommerce_order_button_text', 'relive_custom_order_button_text');
function relive_custom_order_button_text($order_button_text)
{
    return 'HOÀN TẤT ĐẶT HÀNG';
}
/* =========================================
   TÍCH HỢP VNPAY (THEME VERSION - FIX HOOK)
   ========================================= */

// Sử dụng hook 'init' thay vì 'plugins_loaded' vì theme load sau plugin
add_action('init', 'relive_init_vnpay_gateway_theme');

function relive_init_vnpay_gateway_theme()
{
    // Nếu WooCommerce chưa chạy hoặc chưa có class Gateway thì dừng
    if (!class_exists('WC_Payment_Gateway')) return;

    // Định nghĩa Class VNPAY
    class WC_Gateway_Relive_VNPAY extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id                 = 'relive_vnpay';
            $this->icon               = '';
            $this->has_fields         = false;
            $this->method_title       = 'VNPAY-QR (Relive Real)';
            $this->method_description = 'Thanh toán thực qua VNPAY (Hỗ trợ Sandbox/Production).';

            $this->init_form_fields();
            $this->init_settings();

            $this->title       = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled     = $this->get_option('enabled');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Kích hoạt',
                    'type'    => 'checkbox',
                    'label'   => 'Bật thanh toán VNPAY',
                    'default' => 'yes'
                ),
                'title' => array(
                    'title'       => 'Tiêu đề',
                    'type'        => 'text',
                    'default'     => 'Thanh toán qua VNPAY-QR',
                ),
                'description' => array(
                    'title'       => 'Mô tả',
                    'type'        => 'textarea',
                    'default'     => 'Quét mã QR từ ứng dụng ngân hàng hoặc ví VNPAY.',
                ),
                'tmn_code' => array(
                    'title'       => 'Terminal ID (TmnCode)',
                    'type'        => 'text',
                    'description' => 'Mã website do VNPAY cấp.',
                ),
                'hash_secret' => array(
                    'title'       => 'Secret Key (HashSecret)',
                    'type'        => 'password',
                    'description' => 'Chuỗi bí mật tạo checksum.',
                ),
                'vnp_url' => array(
                    'title'       => 'VNPAY URL',
                    'type'        => 'text',
                    'default'     => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
                    'description' => 'Link Sandbox: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
                ),
            );
        }

        /**
         * XỬ LÝ THANH TOÁN (TẠO URL & REDIRECT)
         */
        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);

            $vnp_TmnCode    = $this->get_option('tmn_code');
            $vnp_HashSecret = $this->get_option('hash_secret');
            $vnp_Url        = $this->get_option('vnp_url');

            if (empty($vnp_TmnCode) || empty($vnp_HashSecret)) {
                wc_add_notice('Lỗi: Chưa cấu hình VNPAY (Thiếu Terminal ID hoặc Secret Key).', 'error');
                return;
            }

            $vnp_TxnRef    = $order_id;
            $vnp_OrderInfo = 'Thanh toan don hang ' . $order_id;
            $vnp_OrderType = 'other';
            $vnp_Amount    = $order->get_total() * 100;
            $vnp_Locale    = 'vn';
            $vnp_IpAddr    = $_SERVER['REMOTE_ADDR'];
            $vnp_ReturnUrl = $this->get_return_url($order);

            $inputData = array(
                "vnp_Version"    => "2.1.0",
                "vnp_TmnCode"    => $vnp_TmnCode,
                "vnp_Amount"     => $vnp_Amount,
                "vnp_Command"    => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode"   => "VND",
                "vnp_IpAddr"     => $vnp_IpAddr,
                "vnp_Locale"     => $vnp_Locale,
                "vnp_OrderInfo"  => $vnp_OrderInfo,
                "vnp_OrderType"  => $vnp_OrderType,
                "vnp_ReturnUrl"  => $vnp_ReturnUrl,
                "vnp_TxnRef"     => $vnp_TxnRef,
            );

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            return array(
                'result'   => 'success',
                'redirect' => $vnp_Url
            );
        }
    }

    // Đăng ký Gateway vào list của Woo
    add_filter('woocommerce_payment_gateways', 'relive_add_vnpay_gateway_to_woo');
}

function relive_add_vnpay_gateway_to_woo($methods)
{
    $methods[] = 'WC_Gateway_Relive_VNPAY';
    return $methods;
}
/* =========================================
   LƯU META DATA TỪ GIỎ HÀNG SANG ĐƠN HÀNG
   (Để trang Thank You biết đâu là sản phẩm mua kèm)
   ========================================= */
add_action('woocommerce_checkout_create_order_line_item', 'relive_save_cart_meta_to_order_item', 10, 4);
function relive_save_cart_meta_to_order_item($item, $cart_item_key, $values, $order)
{
    if (isset($values['relive_is_addon'])) {
        $item->add_meta_data('relive_is_addon', $values['relive_is_addon']);
    }
    if (isset($values['relive_parent_id'])) {
        $item->add_meta_data('relive_parent_id', $values['relive_parent_id']);
    }
}
/* =================================================================
   XỬ LÝ TÀI KHOẢN: BỎ MẬT KHẨU CŨ + LƯU AVATAR/SĐT (MẠNH MẼ NHẤT)
   ================================================================= */

// 1. Thêm thuộc tính enctype cho form (để upload được ảnh)
add_action('woocommerce_edit_account_form_tag', 'relive_add_enctype_edit_account');
function relive_add_enctype_edit_account()
{
    echo ' enctype="multipart/form-data"';
}

// 2. CHẶN LỖI MẬT KHẨU (ĐỘ ƯU TIÊN 9999 - CHẠY SAU CÙNG)
add_action('woocommerce_save_account_details_errors', 'relive_force_remove_password_errors', 9999, 2);
function relive_force_remove_password_errors($errors, $user)
{
    // Chỉ chạy khi khách có nhập mật khẩu mới
    if (! empty($_POST['password_1'])) {

        // Xóa triệt để các lỗi liên quan đến mật khẩu cũ
        $errors->remove('password_current_error');

        // Duyệt qua tất cả lỗi, nếu thấy cái nào nhắc đến "password_current" thì xóa luôn
        $error_codes = $errors->get_error_codes();
        foreach ($error_codes as $code) {
            if (strpos($code, 'password_current') !== false) {
                $errors->remove($code);
            }
        }

        // Kiểm tra lại mật khẩu mới có khớp không (WooCommerce có thể đã bỏ qua bước này do lỗi trên)
        if (empty($_POST['password_2']) || $_POST['password_1'] !== $_POST['password_2']) {
            $errors->add('password_mismatch', __('Mật khẩu xác nhận không khớp.', 'woocommerce'));
        }
    }
}

// 3. LƯU DỮ LIỆU: AVATAR, SĐT VÀ MẬT KHẨU MỚI
add_action('woocommerce_save_account_details', 'relive_save_account_all_data_final', 10, 1);
function relive_save_account_all_data_final($user_id)
{

    // A. Lưu Số điện thoại
    if (isset($_POST['account_phone'])) {
        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['account_phone']));
    }

    // B. Lưu Avatar (Upload ảnh)
    if (! empty($_FILES['account_avatar']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('account_avatar', 0);

        if (! is_wp_error($attachment_id)) {
            // Xóa ảnh cũ để dọn dẹp
            $old_avatar_id = get_user_meta($user_id, 'relive_custom_avatar', true);
            if ($old_avatar_id) wp_delete_attachment($old_avatar_id, true);

            // Lưu ảnh mới
            update_user_meta($user_id, 'relive_custom_avatar', $attachment_id);
        }
    }

    // C. CẬP NHẬT MẬT KHẨU MỚI (GHI ĐÈ LOGIC CỦA WOO)
    if (! empty($_POST['password_1']) && ! empty($_POST['password_2'])) {
        if ($_POST['password_1'] === $_POST['password_2']) {

            // Cập nhật mật khẩu trực tiếp vào Database WordPress
            wp_set_password($_POST['password_1'], $user_id);

            // Tự động Đăng nhập lại (Vì đổi pass xong WP sẽ đá user ra)
            $user = get_user_by('id', $user_id);
            wp_set_current_user($user_id, $user->user_login);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $user->user_login, $user);

            // Xóa sạch các thông báo lỗi cũ (nếu còn sót lại) và báo thành công
            wc_clear_notices();
            wc_add_notice(__('Thông tin tài khoản và mật khẩu đã được cập nhật thành công.', 'woocommerce'), 'success');
        }
    }
}

// 4. HIỂN THỊ AVATAR (Giữ nguyên như cũ)
add_filter('get_avatar', 'relive_custom_user_avatar_display', 10, 5);
function relive_custom_user_avatar_display($avatar, $id_or_email, $size, $default, $alt)
{
    $user = false;
    if (is_numeric($id_or_email)) $user = get_user_by('id', $id_or_email);
    elseif (is_object($id_or_email) && !empty($id_or_email->user_id)) $user = get_user_by('id', $id_or_email->user_id);
    else $user = get_user_by('email', $id_or_email);

    if ($user && is_object($user)) {
        $custom_avatar_id = get_user_meta($user->ID, 'relive_custom_avatar', true);
        if ($custom_avatar_id) {
            $avatar_url = wp_get_attachment_image_url($custom_avatar_id, 'thumbnail');
            if ($avatar_url) {
                $avatar = '<img alt="' . esc_attr($alt) . '" src="' . esc_url($avatar_url) . '" class="avatar avatar-' . esc_attr($size) . ' photo" height="' . esc_attr($size) . '" width="' . esc_attr($size) . '" style="border-radius:50%; object-fit:cover;" />';
            }
        }
    }
    return $avatar;
}
/* =========================================
   TẮT KIỂM TRA MẬT KHẨU MẠNH (DISABLE PASSWORD STRENGTH)
   ========================================= */

// 1. Giảm yêu cầu độ mạnh xuống mức 0 (Chấp nhận mọi mật khẩu)
add_filter('woocommerce_min_password_strength', 'relive_allow_weak_password');
function relive_allow_weak_password()
{
    return 0;
}

// 2. Xóa luôn bộ đếm độ mạnh mật khẩu (JS) để không hiện chữ "Yếu/Mạnh"
add_action('wp_print_scripts', 'relive_remove_password_strength_meter', 100);
function relive_remove_password_strength_meter()
{
    if (wp_script_is('wc-password-strength-meter', 'enqueued')) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}
/* =========================================
   BỎ QUA MẬT KHẨU CŨ (PHƯƠNG PHÁP "ẨN THÂN")
   ========================================= */

// Biến toàn cục để lưu tạm mật khẩu mới
global $relive_temp_new_pass;

// 1. Chạy trước khi WooCommerce xử lý form (Priority 19 < 20)
add_action('wp_loaded', 'relive_intercept_save_account', 19);

function relive_intercept_save_account()
{
    global $relive_temp_new_pass;

    // Kiểm tra xem có đang submit form đổi pass không
    if (isset($_POST['action']) && 'save_account_details' === $_POST['action'] && ! empty($_POST['password_1'])) {

        // Xác thực bảo mật
        $nonce_value = isset($_POST['save-account-details-nonce']) ? $_POST['save-account-details-nonce'] : '';
        if (! wp_verify_nonce($nonce_value, 'save_account_details')) return;

        // Kiểm tra khớp mật khẩu
        $pass1 = $_POST['password_1'];
        $pass2 = isset($_POST['password_2']) ? $_POST['password_2'] : '';

        if ($pass1 !== $pass2) {
            wc_add_notice('Mật khẩu xác nhận không khớp.', 'error');
            return; // Dừng lại để hiện lỗi
        }

        // LƯU MẬT KHẨU VÀO BIẾN TẠM
        $relive_temp_new_pass = $pass1;

        // QUAN TRỌNG: Xóa sạch dữ liệu pass trong $_POST
        // Điều này khiến WooCommerce nghĩ rằng khách hàng KHÔNG đổi mật khẩu
        // => WooCommerce sẽ KHÔNG yêu cầu mật khẩu cũ nữa.
        unset($_POST['password_1']);
        unset($_POST['password_2']);
        unset($_POST['password_current']);
    }
}

// 2. Sau khi WooCommerce lưu xong các thông tin khác (Tên, Email...), ta mới lưu mật khẩu
add_action('woocommerce_save_account_details', 'relive_do_save_password_manual', 20, 1);

function relive_do_save_password_manual($user_id)
{
    global $relive_temp_new_pass;

    // Nếu có mật khẩu mới trong biến tạm -> Tiến hành lưu
    if (! empty($relive_temp_new_pass)) {

        // Cập nhật mật khẩu vào Database
        wp_set_password($relive_temp_new_pass, $user_id);

        // Đăng nhập lại ngay lập tức (Vì đổi pass xong WP sẽ đá user ra)
        $user = get_user_by('id', $user_id);
        wp_set_current_user($user_id, $user->user_login);
        wp_set_auth_cookie($user_id);
        do_action('wp_login', $user->user_login, $user);

        // Thông báo thành công
        wc_add_notice('Mật khẩu đã được thay đổi thành công.', 'success');

        // Xóa biến tạm
        $relive_temp_new_pass = null;
    }
}

/* =========================================
   TÙY BIẾN TRANG SỬA ĐỊA CHỈ (EDIT ADDRESS - FINAL)
   ========================================= */
add_filter('woocommerce_address_to_edit', 'relive_custom_address_edit_fields', 999, 2);

function relive_custom_address_edit_fields($fields, $load_address)
{
    // 1. Xóa trường thừa
    unset($fields['billing_company']);
    unset($fields['billing_country']);
    unset($fields['billing_postcode']);
    unset($fields['billing_state']);
    unset($fields['billing_address_2']);

    // 2. Sắp xếp & Class Layout
    $fields['billing_first_name']['class'] = array('form-row-first');
    $fields['billing_first_name']['priority'] = 10;

    $fields['billing_last_name']['class'] = array('form-row-last');
    $fields['billing_last_name']['priority'] = 20;

    $fields['billing_phone']['class'] = array('form-row-first');
    $fields['billing_phone']['priority'] = 30;

    $fields['billing_email']['class'] = array('form-row-last');
    $fields['billing_email']['priority'] = 40;

    // 3. Cấu hình 3 ô địa chỉ (Để trống options để JS nạp)
    $fields['billing_city'] = array(
        'type'        => 'select',
        'label'       => 'Tỉnh / Thành phố',
        'required'    => true,
        'class'       => array('form-row-wide', 'address-select-field'),
        'priority'    => 50,
        'options'     => array('' => 'Đang tải dữ liệu...'),
    );

    $fields['billing_district'] = array(
        'type'        => 'select',
        'label'       => 'Quận / Huyện',
        'required'    => true,
        'class'       => array('form-row-first', 'address-select-field'),
        'priority'    => 60,
        'options'     => array('' => 'Chọn Quận / Huyện'),
    );

    $fields['billing_ward'] = array(
        'type'        => 'select',
        'label'       => 'Phường / Xã',
        'required'    => true,
        'class'       => array('form-row-last', 'address-select-field'),
        'priority'    => 70,
        'options'     => array('' => 'Chọn Phường / Xã'),
    );

    $fields['billing_address_1']['class'] = array('form-row-wide');
    $fields['billing_address_1']['label'] = 'Địa chỉ cụ thể';
    $fields['billing_address_1']['priority'] = 80;

    // 4. TRUYỀN DỮ LIỆU ĐÃ LƯU SANG JS (Quan trọng)
    $user_id = get_current_user_id();
    $saved_data = array(
        'city'     => get_user_meta($user_id, 'billing_city', true),
        'district' => get_user_meta($user_id, '_billing_district', true), // Meta riêng ta đã lưu
        'ward'     => get_user_meta($user_id, '_billing_ward', true),     // Meta riêng ta đã lưu
    );

    // In thẳng script vào footer để JS đọc
    add_action('wp_footer', function () use ($saved_data) {
        echo '<script>var relive_saved_address = ' . json_encode($saved_data) . ';</script>';
    }, 99);

    return $fields;
}