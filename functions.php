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
 * --- XUẤT DỮ LIỆU ẢNH/MÀU RA FRONTEND ---
 * Hàm này in một biến JS chứa map: slug -> ảnh/màu để file main.js dùng
 */
add_action('wp_footer', 'relive_render_swatch_data_json');
function relive_render_swatch_data_json()
{
    if (! is_product()) return;

    global $product;
    $attributes = $product->get_variation_attributes();
    $data = array();

    // Duyệt qua các thuộc tính của sản phẩm hiện tại
    foreach ($attributes as $attribute_name => $options) {
        // attribute_name ví dụ: pa_mau-sac
        $terms = get_terms(array(
            'taxonomy' => $attribute_name,
            'hide_empty' => false,
        ));

        if (! is_wp_error($terms) && ! empty($terms)) {
            foreach ($terms as $term) {
                // Lấy dữ liệu từ Carbon Fields
                $img_id = carbon_get_term_meta($term->term_id, 'attribute_image');
                $color  = carbon_get_term_meta($term->term_id, 'attribute_color');

                // Lấy URL ảnh thumbnail (nhỏ nhẹ)
                $img_url = $img_id ? wp_get_attachment_image_url($img_id, 'thumbnail') : '';

                // Lưu vào mảng với key là slug (ví dụ: titan-sa-mac)
                $data[$term->slug] = array(
                    'image' => $img_url,
                    'color' => $color
                );
            }
        }
    }

    // In ra HTML
    echo '<script>var relive_swatches_json = ' . json_encode($data) . ';</script>';
}