<?php

/**
 * Template Name: Default Page
 */
get_header(); ?>

<main id="main" class="site-main" style="background-color: #f4f4f4; min-height: 60vh;">
    <?php
    while (have_posts()) :
        the_post();

        // 1. Kiểm tra xem trang này có dùng Builder không
        $blocks = function_exists('carbon_get_the_post_meta') ? carbon_get_the_post_meta('builder_blocks') : array();

        if (! empty($blocks)) {
            // Nếu có Builder -> Load Builder
            get_template_part('template-parts/relive-builder');
        } else {
            // 2. QUAN TRỌNG: Nếu không có Builder (như trang Giỏ hàng, Thanh toán) -> Load nội dung chuẩn
            // Đây là dòng giúp [woocommerce_cart] hiển thị
            echo '<div class="container" style="padding-top: 20px; padding-bottom: 40px;">';
            the_content();
            echo '</div>';
        }
    endwhile;
    ?>
</main>

<?php get_footer(); ?>