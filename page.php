<?php
/**
 * Template Name: Default Page
 */
get_header(); ?>

<main id="main" class="site-main">
    <?php
    while ( have_posts() ) :
        the_post();
        // Gọi Engine xử lý Builder
        get_template_part( 'template-parts/relive-builder' );
    endwhile;
    ?>
</main>

<?php get_footer(); ?>