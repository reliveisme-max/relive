<?php

/**
 * Relive Theme Functions - Updated Final Version
 */

define('RELIVE_VERSION', '1.0.0');
define('RELIVE_DIR', get_template_directory());
define('RELIVE_URI', get_template_directory_uri());

$relive_includes = array(
    '/inc/setup.php',
    '/inc/builder-fields.php',
    '/inc/woocommerce-hooks.php',
    '/inc/theme-options.php',
    '/inc/widgets.php',
    '/inc/helpers.php',
);

foreach ($relive_includes as $file) {
    if (file_exists(RELIVE_DIR . $file)) require_once RELIVE_DIR . $file;
}

add_action('after_setup_theme', 'relive_boot_carbon_fields');
function relive_boot_carbon_fields()
{
    if (class_exists('\\Carbon_Fields\\Carbon_Fields')) \Carbon_Fields\Carbon_Fields::boot();
}

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
                        $val = carbon_get_term_meta($term->term_id, 'attribute_image');
                        $color = carbon_get_term_meta($term->term_id, 'attribute_color');
                        $img_url = '';
                        if (is_numeric($val) && $val > 0) $img_url = wp_get_attachment_image_url($val, 'thumbnail');
                        elseif (is_string($val)) $img_url = $val;
                        $data[$term->slug] = array('image' => $img_url, 'color' => $color, 'name' => $term->name);
                    }
                }
            }
        }
    }
    echo '<script>var relive_swatches_json = ' . json_encode($data) . ';</script>';
}

/* ==========================================================================
   REVIEW SYSTEM AJAX
   ========================================================================== */
add_action('wp_ajax_relive_load_reviews', 'relive_ajax_load_reviews');
add_action('wp_ajax_nopriv_relive_load_reviews', 'relive_ajax_load_reviews');

function relive_ajax_load_reviews()
{
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $page       = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $star       = isset($_POST['star']) ? $_POST['star'] : 'all';
    $per_page   = 5;

    $args = array('post_id' => $product_id, 'status' => 'approve', 'type' => 'review', 'number' => $per_page, 'paged' => $page, 'parent' => 0);
    if ($star != 'all') {
        $args['meta_query'] = array(array('key' => 'rating', 'value' => intval($star), 'compare' => '=', 'type' => 'NUMERIC'));
    }

    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    $count_args = $args;
    unset($count_args['number'], $count_args['paged']);
    $count_args['count'] = true;
    $total = get_comments($count_args);
    $max_pages = ceil($total / $per_page);

    ob_start();
    if ($comments) {
        foreach ($comments as $comment) {
            $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
            $likes = intval(get_comment_meta($comment->comment_ID, 'likes', true));
            $phone = get_comment_meta($comment->comment_ID, 'phone', true);
            $img_ids = get_comment_meta($comment->comment_ID, 'review_image_id', false);
            $author_name = $comment->comment_author;
            $first_char = function_exists('mb_substr') ? mb_substr($author_name, 0, 1) : substr($author_name, 0, 1);

            $is_parent_admin = false;
            if ($comment->user_id > 0) {
                $user = get_userdata($comment->user_id);
                if ($user && array_intersect(['administrator', 'shop_manager', 'editor'], (array) $user->roles)) $is_parent_admin = true;
            }
            $children = get_comments(array('parent' => $comment->comment_ID, 'type' => 'review', 'status' => 'approve', 'order' => 'ASC'));
?>
<div class="review-item">
    <div class="ri-header">
        <div class="ri-avatar"><?php echo esc_html(strtoupper($first_char)); ?></div>
        <div class="ri-name"><?php echo esc_html($author_name); ?><?php if ($is_parent_admin): ?><span
                style="background:#cb1c22; color:#fff; font-size:10px; padding:2px 6px; border-radius:3px; margin-left:5px; font-weight:600;">QTV</span><?php endif; ?>
        </div>
        <?php if ($phone): ?><div class="ri-check"><i class="fas fa-check-circle"></i> Đã mua tại FPT Shop</div>
        <?php endif; ?>
    </div>
    <div class="ri-content">
        <div class="ri-stars">
            <?php for ($i = 1; $i <= 5; $i++) echo '<i class="fas fa-star ' . ($i <= $rating ? '' : 'text-muted') . '"></i>'; ?>
        </div>
        <div class="ri-text"><?php echo wpautop($comment->comment_content); ?></div>
        <?php if (!empty($img_ids)): ?>
        <div class="ri-gallery" style="display:flex; gap:10px; margin-bottom:10px; flex-wrap:wrap;">
            <?php foreach ($img_ids as $img_id): $url = wp_get_attachment_image_url($img_id, 'full');
                                $thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                                if (!$thumb) continue; ?>
            <a href="<?php echo esc_url($url); ?>" data-fancybox="review-gallery-<?php echo $comment->comment_ID; ?>"
                class="ri-img-item"><img src="<?php echo esc_url($thumb); ?>"
                    style="width:60px; height:60px; object-fit:cover; border-radius:4px; border:1px solid #eee;"></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="ri-actions">
            <span class="ri-action-btn btn-like-review" data-id="<?php echo $comment->comment_ID; ?>"><i
                    class="fas fa-thumbs-up"></i> <span>Thích
                    <?php echo ($likes > 0 ? "($likes)" : ""); ?></span></span>
            <span class="ri-action-btn btn-reply-trigger" data-id="<?php echo $comment->comment_ID; ?>"
                data-name="<?php echo esc_attr($author_name); ?>"><i class="fas fa-comment-alt"></i> Phản hồi</span>
            <span class="ri-date"><?php echo get_comment_date('d/m/Y', $comment->comment_ID); ?></span>
        </div>
        <?php if ($children): ?>
        <div class="ri-replies"
            style="margin-top:15px; background:#f9f9f9; padding:15px; border-radius:8px; position:relative;">
            <div
                style="position:absolute; top:-10px; left:20px; width:0; height:0; border-left:10px solid transparent; border-right:10px solid transparent; border-bottom:10px solid #f9f9f9;">
            </div>
            <?php foreach ($children as $child):
                                $is_child_admin = false;
                                if ($child->user_id > 0) {
                                    $u = get_userdata($child->user_id);
                                    if ($u && array_intersect(['administrator', 'shop_manager', 'editor'], (array) $u->roles)) $is_child_admin = true;
                                }
                                $child_img_ids = get_comment_meta($child->comment_ID, 'review_image_id', false);
                            ?>
            <div class="child-review"
                style="margin-bottom:12px; font-size:13px; border-bottom:1px solid #eee; padding-bottom:8px;">
                <strong style="color:#333;"><?php echo $child->comment_author; ?></strong>
                <?php if ($is_child_admin): ?><span
                    style="background:#cb1c22; color:#fff; font-size:9px; padding:2px 5px; border-radius:3px; margin-left:5px;">QTV</span><?php endif; ?>
                <div style="margin-top:4px; color:#555;"><?php echo wpautop($child->comment_content); ?></div>
                <?php if (!empty($child_img_ids)): ?>
                <div class="ri-gallery" style="display:flex; gap:5px; margin-top:5px; flex-wrap:wrap;">
                    <?php foreach ($child_img_ids as $c_img_id): $c_url = wp_get_attachment_image_url($c_img_id, 'full');
                                                $c_thumb = wp_get_attachment_image_url($c_img_id, 'thumbnail');
                                                if (!$c_thumb) continue; ?>
                    <a href="<?php echo esc_url($c_url); ?>"
                        data-fancybox="reply-gallery-<?php echo $child->comment_ID; ?>" class="ri-img-item"><img
                            src="<?php echo esc_url($c_thumb); ?>"
                            style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #eee;"></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php
        }
    } else {
        echo '<div style="text-align:center; color:#777; padding:30px 0;">Chưa có đánh giá nào.</div>';
    }
    $html = ob_get_clean();
    $pagination = '';
    if ($max_pages > 1) {
        $pagination = paginate_links(array('base' => '%_%', 'format' => '?paged=%#%', 'total' => $max_pages, 'current' => $page, 'type' => 'list', 'prev_text' => '<i class="fas fa-chevron-left"></i>', 'next_text' => '<i class="fas fa-chevron-right"></i>'));
        $pagination = str_replace('page-numbers', 'page-link page-numbers', $pagination);
        $pagination = str_replace('ul class="page-link page-numbers"', 'ul class="pagination"', $pagination);
    }
    wp_send_json_success(array('html' => $html, 'pagination' => $pagination));
}

add_action('wp_ajax_relive_submit_review', 'relive_ajax_submit_review');
add_action('wp_ajax_nopriv_relive_submit_review', 'relive_ajax_submit_review');

function relive_ajax_submit_review()
{
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'relive_review_nonce')) wp_send_json_error(array('message' => 'Lỗi bảo mật.'));
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $comment = sanitize_textarea_field($_POST['comment']);
    $author = sanitize_text_field($_POST['author']);
    $phone = sanitize_text_field($_POST['phone']);
    $parent_id = isset($_POST['comment_parent']) ? intval($_POST['comment_parent']) : 0;

    if (!$comment || !$author || !$phone) wp_send_json_error(array('message' => 'Vui lòng điền đủ thông tin.'));

    $user = wp_get_current_user();
    if ($user->exists()) {
        $user_id = $user->ID;
        $email = $user->user_email;
    } else {
        $user_id = 0;
        $email = $phone . '@noemail.com';
    }

    $comment_id = wp_insert_comment(array(
        'comment_post_ID' => $product_id,
        'comment_author' => $author,
        'comment_author_email' => $email,
        'comment_content' => $comment,
        'comment_type' => 'review',
        'comment_parent' => $parent_id,
        'comment_approved' => 1,
        'user_id' => $user_id
    ));

    if ($comment_id) {
        update_comment_meta($comment_id, 'rating', $rating);
        update_comment_meta($comment_id, 'phone', $phone);
        update_comment_meta($comment_id, 'likes', 0);
        if (!empty($_FILES['review_image'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            $files = $_FILES['review_image'];
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array('name' => $files['name'][$key], 'type' => $files['type'][$key], 'tmp_name' => $files['tmp_name'][$key], 'error' => $files['error'][$key], 'size' => $files['size'][$key]);
                    $_FILES['single_review_image'] = $file;
                    $attachment_id = media_handle_upload('single_review_image', $product_id);
                    if (!is_wp_error($attachment_id)) add_comment_meta($comment_id, 'review_image_id', $attachment_id);
                }
            }
        }
        wp_send_json_success(array('message' => 'Thành công!'));
    } else {
        wp_send_json_error(array('message' => 'Lỗi hệ thống.'));
    }
}

add_action('wp_ajax_relive_like_review', 'relive_ajax_like_review');
add_action('wp_ajax_nopriv_relive_like_review', 'relive_ajax_like_review');
function relive_ajax_like_review()
{
    $comment_id = intval($_POST['comment_id']);
    $new_likes = (int) get_comment_meta($comment_id, 'likes', true) + 1;
    update_comment_meta($comment_id, 'likes', $new_likes);
    wp_send_json_success(array('count' => $new_likes));
}

/* ==========================================================================
   BỘ LỌC SẢN PHẨM & AJAX LOAD
   ========================================================================== */
add_action('woocommerce_product_query', 'relive_advanced_product_filter', 999);
function relive_advanced_product_filter($q)
{
    if (is_admin() || !$q->is_main_query() || (!is_shop() && !is_product_taxonomy())) return;
    $tax_query = $q->get('tax_query') ?: array();
    $found = false;
    foreach ($_GET as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $tax_query[] = array('taxonomy' => 'pa_' . str_replace('filter_', '', $key), 'field' => 'slug', 'terms' => is_array($value) ? $value : explode(',', $value), 'operator' => 'IN');
            $found = true;
        }
    }
    if ($found) {
        if (count($tax_query) > 1) $tax_query['relation'] = 'AND';
        $q->set('tax_query', $tax_query);
    }
}

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
    if (!empty($params['current_cat_id'])) {
        $tax_query[] = array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => intval($params['current_cat_id']), 'include_children' => true);
    }
    $query = new WP_Query(array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'tax_query' => $tax_query));
    wp_send_json_success(array('count' => $query->found_posts));
}

add_action('wp_ajax_relive_load_products', 'relive_ajax_load_products');
add_action('wp_ajax_nopriv_relive_load_products', 'relive_ajax_load_products');
function relive_ajax_load_products()
{
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    parse_str($_POST['form_data'], $params);
    $tax_query = array('relation' => 'AND');
    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $tax_query[] = array('taxonomy' => 'pa_' . str_replace('filter_', '', $key), 'field' => 'slug', 'terms' => $value, 'operator' => 'IN');
        }
    }
    if (!empty($params['current_cat_id'])) {
        $tax_query[] = array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => intval($params['current_cat_id']), 'include_children' => true);
    }
    $per_page = carbon_get_theme_option('shop_per_page') ? carbon_get_theme_option('shop_per_page') : 12;
    $args = array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => $per_page, 'paged' => $page, 'tax_query' => $tax_query);

    $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'date';
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

    $query = new WP_Query($args);
    ob_start();
    if ($query->have_posts()) {
        echo '<div class="shop-products-grid" style="display:contents;">';
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
        echo '</div>';
    } else {
        echo '<div class="white-box text-center" style="padding:50px; width:100%;">Không tìm thấy sản phẩm.</div>';
    }
    $products = ob_get_clean();

    $pagination = paginate_links(array('base' => '%_%', 'format' => '?paged=%#%', 'total' => $query->max_num_pages, 'current' => $page, 'type' => 'list', 'prev_text' => '<i class="fas fa-chevron-left"></i>', 'next_text' => '<i class="fas fa-chevron-right"></i>'));
    $pagination = str_replace('page-numbers', 'page-link page-numbers', $pagination);
    $pagination = str_replace('ul class="page-link page-numbers"', 'ul class="pagination"', $pagination);
    wp_send_json_success(array('products' => $products, 'pagination' => $pagination));
}

/* ==========================================================================
   XỬ LÝ MUA KÈM + ÁP DỤNG COUPON TỰ ĐỘNG
   ========================================================================== */
add_action('wp_ajax_relive_add_multiple_to_cart', 'relive_ajax_add_multiple_to_cart');
add_action('wp_ajax_nopriv_relive_add_multiple_to_cart', 'relive_ajax_add_multiple_to_cart');

function relive_ajax_add_multiple_to_cart()
{
    $items = isset($_POST['items']) ? $_POST['items'] : array();
    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : ''; // Nhận mã từ JS

    if (empty($items)) {
        wp_send_json_error(array('message' => 'Chưa chọn sản phẩm nào.'));
    }

    $added_count = 0;

    // 1. Thêm sản phẩm vào giỏ
    foreach ($items as $index => $item) {
        $p_id = intval($item['id']);
        $quantity = isset($item['qty']) ? intval($item['qty']) : 1;
        $variation_id = isset($item['vid']) ? intval($item['vid']) : 0;

        if ($p_id > 0) {
            $cart_item_data = array();

            // Nếu không phải sản phẩm đầu tiên (index 0 là SP chính), đánh dấu là Addon
            if ($index > 0) {
                $cart_item_data['relive_is_addon'] = true;
                // Lưu ID của sản phẩm chính để tính giá
                $cart_item_data['relive_parent_id'] = intval($items[0]['id']);
            }

            if ($variation_id > 0) {
                $added = WC()->cart->add_to_cart($p_id, $quantity, $variation_id, array(), $cart_item_data);
            } else {
                $added = WC()->cart->add_to_cart($p_id, $quantity, 0, array(), $cart_item_data);
            }

            if ($added) $added_count++;
        }
    }

    // 2. Áp dụng Coupon (nếu có và hợp lệ)
    $coupon_applied = false;
    if ($added_count > 0 && !empty($coupon_code)) {
        if (!WC()->cart->has_discount($coupon_code)) {
            $result = WC()->cart->apply_coupon($coupon_code);
            if ($result === true) {
                $coupon_applied = $coupon_code;
            }
        }
    }

    if ($added_count > 0) {
        wp_send_json_success(array(
            'redirect' => wc_get_cart_url(),
            'coupon_applied' => $coupon_applied
        ));
    } else {
        wp_send_json_error(array('message' => 'Không thể thêm sản phẩm vào giỏ.'));
    }
}

/* ==========================================================================
   TÍNH GIÁ KHUYẾN MÃI CHO SẢN PHẨM MUA KÈM (DỰA TRÊN %)
   ========================================================================== */
add_action('woocommerce_before_calculate_totals', 'relive_apply_addon_discount', 10, 1);

function relive_apply_addon_discount($cart)
{
    if (is_admin() && !defined('DOING_AJAX')) return;

    foreach ($cart->get_cart() as $cart_item) {
        // 1. Kiểm tra xem sản phẩm này có phải là hàng mua kèm không
        if (isset($cart_item['relive_parent_id']) && $cart_item['relive_parent_id'] > 0) {

            $parent_id = $cart_item['relive_parent_id'];
            $child_id  = $cart_item['product_id'];

            // 2. Lấy danh sách cấu hình mua kèm từ sản phẩm CHA
            // Dùng carbon_get_post_meta vì lấy từ DB của cha
            $bought_items = carbon_get_post_meta($parent_id, 'fpt_bought_together');

            if (!empty($bought_items)) {
                foreach ($bought_items as $item) {
                    // Tìm xem sản phẩm con này nằm ở đâu trong list của cha
                    if (!empty($item['product_assoc']) && $item['product_assoc'][0]['id'] == $child_id) {

                        // 3. Lấy % giảm giá
                        $percent = isset($item['percent_sale']) ? intval($item['percent_sale']) : 0;

                        if ($percent > 0) {
                            $product = $cart_item['data'];
                            $price = floatval($product->get_price());

                            // 4. Tính giá mới
                            $new_price = $price * (100 - $percent) / 100;

                            // 5. Set giá mới cho item trong giỏ
                            $cart_item['data']->set_price($new_price);
                        }
                        break;
                    }
                }
            }
        }
    }
}