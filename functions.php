<?php
/**
 * Relive Theme Functions
 * Author: Relive Team
 */

// 1. Định nghĩa hằng số
define( 'RELIVE_VERSION', '1.0.0' );
define( 'RELIVE_DIR', get_template_directory() );
define( 'RELIVE_URI', get_template_directory_uri() );

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

foreach ( $relive_includes as $file ) {
    $filepath = RELIVE_DIR . $file;
    if ( file_exists( $filepath ) ) {
        require_once $filepath;
    }
}

// 3. Khởi động Carbon Fields (Chỉ chạy khi đã cài Plugin)
add_action( 'after_setup_theme', 'relive_boot_carbon_fields' );
function relive_boot_carbon_fields() {
    if ( class_exists( '\\Carbon_Fields\\Carbon_Fields' ) ) {
        \Carbon_Fields\Carbon_Fields::boot();
    }
}