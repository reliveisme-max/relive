<?php

/**
 * Giao diện Header (Có Menu Dọc - Mega Menu Style)
 */

// Lấy thông tin từ Theme Options
$h_bg     = carbon_get_theme_option('header_bg_color');
$h_height = carbon_get_theme_option('header_height');
$h_text   = carbon_get_theme_option('header_text_color');
$h_sticky = carbon_get_theme_option('header_sticky');

// Giá trị mặc định
if (empty($h_bg))     $h_bg = '#ffffff';
if (empty($h_height)) $h_height = 70;
if (empty($h_text))   $h_text = '#333333';

$header_classes = 'header';
if ($h_sticky) $header_classes .= ' sticky';
?>

<header id="masthead" class="<?php echo esc_attr($header_classes); ?>"
    style="background-color: <?php echo esc_attr($h_bg); ?> !important;">
    <div class="container">
        <div class="header-inner" style="height: <?php echo intval($h_height); ?>px;">

            <div class="site-branding" style="margin-right: 20px;">
                <?php $logo_url = carbon_get_theme_option('site_logo'); ?>
                <?php if ($logo_url) : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="<?php bloginfo('name'); ?>"
                        style="max-height: <?php echo intval($h_height) - 20; ?>px; width: auto;">
                </a>
                <?php else : ?>
                <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"
                        style="font-weight: 900; text-transform: uppercase; color: <?php echo esc_attr($h_text); ?>;"><?php bloginfo('name'); ?></a>
                </p>
                <?php endif; ?>
            </div>

            <div class="header-search" style="flex: 1; margin: 0 20px; position: relative;">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>"
                    style="display: flex;">
                    <input type="search" class="search-field" placeholder="Bạn cần tìm gì?..."
                        value="<?php echo get_search_query(); ?>" name="s"
                        style="width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 4px; height: 40px;">
                    <button type="submit" class="search-submit"
                        style="position: absolute; right: 5px; top: 5px; background: none; border: none; color: #777; height: 30px; cursor: pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="header-actions" style="display: flex; align-items: center; gap: 15px;">
                <?php if (class_exists('WooCommerce')) : ?>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="header-icon cart-icon"
                    style="color: <?php echo esc_attr($h_text); ?>;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?php
                        $count = WC()->cart->get_cart_contents_count();
                        if ($count > 0) echo '<span class="cart-count">' . esc_html($count) . '</span>';
                        ?>
                </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>