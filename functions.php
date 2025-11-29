<?php

/**
 * Relive Theme Functions
 * Author: Relive Team
 */

// 1. Định nghĩa hằng số
define('RELIVE_VERSION', '1.0.0');
define('RELIVE_DIR', get_template_directory());
define('RELIVE_URI', get_template_directory_uri());

// 2. Load các file chức năng từ thư mục /inc/
$relive_includes = array(
    '/inc/setup.php',             // Cấu hình theme, CSS, JS, Admin UI
    '/inc/builder-fields.php',    // Khai báo Builder (Các ô nhập liệu)
    '/inc/woocommerce-hooks.php', // Tùy biến Woo (Nếu có file này)
    '/inc/theme-options.php',     // Phần 1
    '/inc/woocommerce-hooks.php', // Phần 2
    '/inc/widgets.php',           // Phần 3
    '/inc/helpers.php',           // Helpers
);

foreach ($relive_includes as $file) {
    $filepath = RELIVE_DIR . $file;
    if (file_exists($filepath)) {
        require_once $filepath;
    }
}

// 3. Khởi động Carbon Fields (Chỉ chạy khi đã cài Plugin)
add_action('after_setup_theme', 'relive_boot_carbon_fields');
function relive_boot_carbon_fields()
{
    if (class_exists('\\Carbon_Fields\\Carbon_Fields')) {
        \Carbon_Fields\Carbon_Fields::boot();
    }
}

/**
 * --- XUẤT DỮ LIỆU ẢNH/MÀU RA FRONTEND (FINAL FIX) ---
 */
add_action('wp_footer', 'relive_render_swatch_data_json', 99);
function relive_render_swatch_data_json()
{
    if (! is_product()) return;

    $product_id = get_queried_object_id();
    $product = wc_get_product($product_id);

    if (! $product) return;

    $data = array();
    $attributes = $product->get_attributes();

    if (! empty($attributes)) {
        foreach ($attributes as $attribute) {
            if ($attribute->is_taxonomy()) {
                $taxonomy = $attribute->get_name();
                $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => false));

                if (! is_wp_error($terms) && ! empty($terms)) {
                    foreach ($terms as $term) {
                        // Lấy dữ liệu từ Carbon Fields
                        $val    = carbon_get_term_meta($term->term_id, 'attribute_image');
                        $color  = carbon_get_term_meta($term->term_id, 'attribute_color');

                        $img_url = '';

                        // Logic thông minh: Tự check xem dữ liệu là ID (số) hay URL (chuỗi)
                        if (is_numeric($val) && $val > 0) {
                            // Nếu là ID -> Lấy ảnh thumbnail (nhẹ)
                            $img_url = wp_get_attachment_image_url($val, 'thumbnail');
                        } elseif (is_string($val) && !empty($val)) {
                            // Nếu lỡ là URL -> Dùng luôn
                            $img_url = $val;
                        }

                        $data[$term->slug] = array(
                            'image' => $img_url,
                            'color' => $color,
                            'name'  => $term->name
                        );
                    }
                }
            }
        }
    }

    echo '<script>';
    echo 'var relive_swatches_json = ' . json_encode($data) . ';';
    echo '</script>';
}

/**
 * --- XÓA HẲN NÚT "RESET" (XÓA) CỦA BIẾN THỂ ---
 */
add_filter('woocommerce_reset_variations_link', '__return_empty_string');


/* ==========================================================================
   REVIEW SYSTEM AJAX HANDLERS (FULL FEATURES)
   ========================================================================== */

/**
 * 1. AJAX LOAD REVIEWS (CÓ ẢNH & TRẢ LỜI)
 */
add_action('wp_ajax_relive_load_reviews', 'relive_ajax_load_reviews');
add_action('wp_ajax_nopriv_relive_load_reviews', 'relive_ajax_load_reviews');

function relive_ajax_load_reviews()
{
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $page       = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $star       = isset($_POST['star']) ? $_POST['star'] : 'all';
    $per_page   = 5;

    $args = array(
        'post_id' => $product_id,
        'status' => 'approve',
        'type' => 'review',
        'number' => $per_page,
        'paged' => $page,
        'parent' => 0
    );

    if ($star != 'all') {
        $args['meta_query'] = array(array('key' => 'rating', 'value' => intval($star), 'compare' => '=', 'type' => 'NUMERIC'));
    }

    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query($args);

    ob_start();
    if ($comments) {
        foreach ($comments as $comment) {
            $rating = intval(get_comment_meta($comment->comment_ID, 'rating', true));
            $phone  = get_comment_meta($comment->comment_ID, 'phone', true);
            $likes  = intval(get_comment_meta($comment->comment_ID, 'likes', true));

            // Lấy nhiều ảnh
            $img_ids = get_comment_meta($comment->comment_ID, 'review_image_id', false);

            $author_name = $comment->comment_author;
            $first_char = function_exists('mb_substr') ? mb_substr($author_name, 0, 1) : substr($author_name, 0, 1);

            $children_args = array('parent' => $comment->comment_ID, 'type' => 'review', 'status' => 'approve', 'order' => 'ASC');
            $children = get_comments($children_args);
?>
<div class="review-item">
    <div class="ri-header">
        <div class="ri-avatar"><?php echo esc_html(strtoupper($first_char)); ?></div>
        <div class="ri-name"><?php echo esc_html($author_name); ?></div>
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
            <?php foreach ($img_ids as $img_id):
                                $img_thumb = wp_get_attachment_image_url($img_id, 'thumbnail');
                                $img_full = wp_get_attachment_image_url($img_id, 'full');
                                if (!$img_thumb) continue;
                            ?>
            <a href="<?php echo esc_url($img_full); ?>"
                data-fancybox="review-gallery-<?php echo $comment->comment_ID; ?>" class="ri-img-item">
                <img src="<?php echo esc_url($img_thumb); ?>" alt="Ảnh review"
                    style="width:60px; height:60px; object-fit:cover; border-radius:4px; border:1px solid #eee;">
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="ri-actions">
            <span class="ri-action-btn btn-like-review" data-id="<?php echo $comment->comment_ID; ?>">
                <i class="fas fa-thumbs-up"></i> <span>Thích <?php echo ($likes > 0 ? "($likes)" : ""); ?></span>
            </span>
            <span class="ri-action-btn btn-reply-trigger" data-id="<?php echo $comment->comment_ID; ?>"
                data-name="<?php echo esc_attr($author_name); ?>">
                <i class="fas fa-comment-alt"></i> Phản hồi
            </span>
            <span class="ri-date"><?php echo get_comment_date('d/m/Y', $comment->comment_ID); ?></span>
        </div>

        <?php if ($children): ?>
        <div class="ri-replies"
            style="margin-top:15px; background:#f9f9f9; padding:15px; border-radius:8px; position:relative;">
            <div
                style="position:absolute; top:-10px; left:20px; width:0; height:0; border-left:10px solid transparent; border-right:10px solid transparent; border-bottom:10px solid #f9f9f9;">
            </div>
            <?php foreach ($children as $child): ?>
            <div class="child-review"
                style="margin-bottom:12px; font-size:13px; border-bottom:1px solid #eee; padding-bottom:8px;">
                <strong style="color:#333;"><?php echo $child->comment_author; ?></strong>
                <span
                    style="background:#cb1c22; color:#fff; font-size:9px; padding:2px 5px; border-radius:3px; margin-left:5px;">QTV</span>
                <div style="margin-top:4px; color:#555;"><?php echo wpautop($child->comment_content); ?></div>
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
    wp_send_json_success(array('html' => $html, 'pagination' => ''));
}

/**
 * 2. SUBMIT REVIEW (NHIỀU ẢNH + PARENT ID)
 */
add_action('wp_ajax_relive_submit_review', 'relive_ajax_submit_review');
add_action('wp_ajax_nopriv_relive_submit_review', 'relive_ajax_submit_review');

function relive_ajax_submit_review()
{
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'relive_review_nonce')) {
        wp_send_json_error(array('message' => 'Lỗi bảo mật. F5 lại trang.'));
    }

    $product_id = intval($_POST['product_id']);
    $rating     = intval($_POST['rating']);
    $comment    = sanitize_textarea_field($_POST['comment']);
    $author     = sanitize_text_field($_POST['author']);
    $phone      = sanitize_text_field($_POST['phone']);
    $parent_id  = isset($_POST['comment_parent']) ? intval($_POST['comment_parent']) : 0;

    if (!$comment || !$author || !$phone) {
        wp_send_json_error(array('message' => 'Vui lòng điền đủ thông tin.'));
    }

    // Tạo email ảo từ SĐT
    $email = $phone . '@noemail.com';

    $data = array(
        'comment_post_ID' => $product_id,
        'comment_author' => $author,
        'comment_author_email' => $email,
        'comment_content' => $comment,
        'comment_type' => 'review',
        'comment_parent' => $parent_id,
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($data);

    if ($comment_id) {
        update_comment_meta($comment_id, 'rating', $rating);
        update_comment_meta($comment_id, 'phone', $phone);
        update_comment_meta($comment_id, 'likes', 0);

        // Upload Nhiều Ảnh
        if (! empty($_FILES['review_image'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $files = $_FILES['review_image'];
            // Loop qua từng file
            foreach ($files['name'] as $key => $value) {
                if ($files['name'][$key]) {
                    $file = array(
                        'name' => $files['name'][$key],
                        'type' => $files['type'][$key],
                        'tmp_name' => $files['tmp_name'][$key],
                        'error' => $files['error'][$key],
                        'size' => $files['size'][$key]
                    );
                    $_FILES['single_review_image'] = $file;
                    $attachment_id = media_handle_upload('single_review_image', $product_id);
                    if (! is_wp_error($attachment_id)) {
                        add_comment_meta($comment_id, 'review_image_id', $attachment_id);
                    }
                }
            }
        }
        wp_send_json_success(array('message' => 'Thành công!'));
    } else {
        wp_send_json_error(array('message' => 'Lỗi hệ thống.'));
    }
}

/**
 * 3. LIKE REVIEW
 */
add_action('wp_ajax_relive_like_review', 'relive_ajax_like_review');
add_action('wp_ajax_nopriv_relive_like_review', 'relive_ajax_like_review');

function relive_ajax_like_review()
{
    $comment_id = intval($_POST['comment_id']);
    $current_likes = (int) get_comment_meta($comment_id, 'likes', true);
    $new_likes = $current_likes + 1;
    update_comment_meta($comment_id, 'likes', $new_likes);
    wp_send_json_success(array('count' => $new_likes));
}

/* ==========================================================================
   BỘ LỌC SẢN PHẨM & AJAX LOAD (SHOP/ARCHIVE PAGE)
   ========================================================================== */

/**
 * 4. LOGIC LỌC KHI TẢI TRANG (GET REQUEST)
 */
add_action('woocommerce_product_query', 'relive_advanced_product_filter');
function relive_advanced_product_filter($q)
{
    if (is_admin() || ! $q->is_main_query()) return;
    if (!is_shop() && !is_product_taxonomy()) return;

    $tax_query = $q->get('tax_query');
    if (! $tax_query) $tax_query = array();

    foreach ($_GET as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $slug = str_replace('filter_', '', $key);
            $taxonomy = 'pa_' . $slug;

            if (is_string($value)) $terms = explode(',', $value);
            elseif (is_array($value)) $terms = $value;
            else continue;

            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            );
        }
    }

    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }

    $q->set('tax_query', $tax_query);
}

/**
 * 5. AJAX ĐẾM SỐ LƯỢNG (CHO NÚT "ĐANG TÍNH...")
 */
add_action('wp_ajax_relive_get_filter_count', 'relive_ajax_get_filter_count');
add_action('wp_ajax_nopriv_relive_get_filter_count', 'relive_ajax_get_filter_count');

function relive_ajax_get_filter_count()
{
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : '';
    parse_str($form_data, $params);

    $tax_query = array('relation' => 'AND');

    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $slug = str_replace('filter_', '', $key);
            $taxonomy = 'pa_' . $slug;
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $value,
                'operator' => 'IN'
            );
        }
    }

    if (!empty($params['current_cat_id'])) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => intval($params['current_cat_id']),
            'include_children' => true
        );
    }

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Đếm tất cả
        'fields'         => 'ids',
        'tax_query'      => $tax_query
    );

    $query = new WP_Query($args);
    wp_send_json_success(array('count' => $query->found_posts));
}

/**
 * 6. AJAX LOAD SẢN PHẨM (CHO PHÂN TRANG KHÔNG LOAD LẠI)
 */
add_action('wp_ajax_relive_load_products', 'relive_ajax_load_products');
add_action('wp_ajax_nopriv_relive_load_products', 'relive_ajax_load_products');

function relive_ajax_load_products()
{
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $form_data = isset($_POST['form_data']) ? $_POST['form_data'] : '';
    $orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'date';

    parse_str($form_data, $params);

    $tax_query = array('relation' => 'AND');

    foreach ($params as $key => $value) {
        if (strpos($key, 'filter_') === 0 && !empty($value)) {
            $slug = str_replace('filter_', '', $key);
            $taxonomy = 'pa_' . $slug;
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $value,
                'operator' => 'IN'
            );
        }
    }

    if (!empty($params['current_cat_id'])) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => intval($params['current_cat_id']),
            'include_children' => true
        );
    }

    $per_page = carbon_get_theme_option('shop_per_page') ? carbon_get_theme_option('shop_per_page') : 12;

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'tax_query'      => $tax_query
    );

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
        echo '<div class="col-12"><div class="white-box text-center" style="padding:50px;"><p>Không tìm thấy sản phẩm nào phù hợp.</p></div></div>';
    }
    $products = ob_get_clean();

    $pagination = paginate_links(array(
        'base'      => '%_%',
        'format'    => '?paged=%#%',
        'total'     => $query->max_num_pages,
        'current'   => $page,
        'type'      => 'list',
        'prev_text' => '<i class="fas fa-chevron-left"></i>',
        'next_text' => '<i class="fas fa-chevron-right"></i>',
    ));

    $pagination = str_replace('page-numbers', 'page-link', $pagination);
    $pagination = str_replace('ul class="page-link"', 'ul class="pagination"', $pagination);

    wp_send_json_success(array('products' => $products, 'pagination' => $pagination));
}