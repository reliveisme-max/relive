<?php

/**
 * Relive Theme Setup
 */

if (! function_exists('relive_setup')) {
    function relive_setup()
    {
        // Hỗ trợ cơ bản
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo');
        add_theme_support('woocommerce');

        // Đăng ký Menu
        register_nav_menus(array(
            'primary'  => __('Menu Chính (Ngang)', 'relive'),
            'vertical' => __('Menu Dọc (Danh mục)', 'relive'), // <--- MỚI THÊM
            'mobile'   => __('Menu Mobile', 'relive'),
        ));
    }
}
add_action('after_setup_theme', 'relive_setup');

/**
 * 1. Load CSS/JS cho Frontend (Khách xem)
 */
add_action('wp_enqueue_scripts', 'relive_scripts');
function relive_scripts()
{
    // Style gốc
    wp_enqueue_style('relive-style', get_stylesheet_uri());
    // Main CSS
    // SỬA: Thay RELIVE_VERSION bằng time() để ép trình duyệt tải lại CSS mới mỗi lần F5
    wp_enqueue_style('relive-main', RELIVE_URI . '/assets/css/main.css', array(), time());
    // Swiper CSS (Slider)
    wp_enqueue_style('swiper-css', get_template_directory_uri() . '/assets/vendor/swiper/swiper-bundle.min.css', array(), '11.0.0');

    // Load JS từ thư mục assets/vendor/swiper
    wp_enqueue_script('swiper-js', get_template_directory_uri() . '/assets/vendor/swiper/swiper-bundle.min.js', array(), '11.0.0', true);

    // Main JS theme
    wp_enqueue_script('relive-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), RELIVE_VERSION, true);
}

/**
 * 2. Load CSS cho Admin (Để làm đẹp Builder)
 */
add_action('admin_enqueue_scripts', 'relive_admin_styles');
function relive_admin_styles()
{
    // Load file CSS làm đẹp admin
    wp_enqueue_style('relive-admin-css', RELIVE_URI . '/assets/css/admin-builder.css', array(), time());

    // Ẩn khung soạn thảo văn bản cũ kỹ khi ở trang Page
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'page') {
        echo '<style>#postdivrich { display: none !important; }</style>';
    }
}

/**
 * 3. Dọn dẹp các Meta Box rác trong Admin
 */
add_action('admin_menu', 'relive_clean_admin');
function relive_clean_admin()
{
    remove_meta_box('postcustom', 'page', 'normal'); // Custom Fields
    remove_meta_box('commentstatusdiv', 'page', 'normal'); // Discussion
    remove_meta_box('commentsdiv', 'page', 'normal'); // Comments
    remove_meta_box('authordiv', 'page', 'normal'); // Author
    remove_meta_box('slugdiv', 'page', 'normal'); // Slug
}
