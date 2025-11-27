<?php
function relive_widgets_init() {
    // 1. Sidebar cho Blog
    register_sidebar( array(
        'name'          => __( 'Blog Sidebar', 'relive' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Thêm các widget hiển thị ở trang tin tức.', 'relive' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s" style="margin-bottom: 30px;">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title" style="font-size: 18px; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">',
        'after_title'   => '</h3>',
    ) );

    // 2. Sidebar cho Shop (Lọc sản phẩm)
    register_sidebar( array(
        'name'          => __( 'Shop Sidebar', 'relive' ),
        'id'            => 'sidebar-shop',
        'description'   => __( 'Thêm các bộ lọc sản phẩm vào đây.', 'relive' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s" style="margin-bottom: 30px;">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title" style="font-size: 18px; font-weight: bold; margin-bottom: 15px;">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'relive_widgets_init' );