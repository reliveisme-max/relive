<?php

/**
 * Template: Single Product (Khung sườn)
 * File: woocommerce/single-product.php
 */
if (! defined('ABSPATH')) exit;

get_header('shop'); ?>

<main id="main" class="site-main" style="background-color: #fff; padding-bottom: 40px;">

    <div class="container" style="padding-top: 15px;">
        <?php if (function_exists('relive_breadcrumbs')) relive_breadcrumbs(); ?>
    </div>

    <?php while (have_posts()) : the_post(); ?>
    <?php wc_get_template_part('content', 'single-product'); ?>
    <?php endwhile; ?>

</main>

<?php get_footer('shop'); ?>