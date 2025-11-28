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
    '/inc/helpers.php',
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
    // echo 'console.log("Swatches Loaded:", relive_swatches_json);'; // Bỏ comment nếu muốn debug
    echo '</script>';
}

/**
 * --- XÓA HẲN NÚT "RESET" (XÓA) CỦA BIẾN THỂ ---
 * Chặn không cho WooCommerce in mã HTML của nút này ra
 */
add_filter('woocommerce_reset_variations_link', '__return_empty_string');