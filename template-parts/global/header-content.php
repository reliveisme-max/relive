<?php

/**
 * Giao diện Header (Full: Logo - Menu - Search - Shortcuts - Cart)
 */

$h_bg     = carbon_get_theme_option('header_bg_color');
$h_height = carbon_get_theme_option('header_height');
$h_text   = carbon_get_theme_option('header_text_color'); // Màu chữ chung
$h_sticky = carbon_get_theme_option('header_sticky');

// Nếu background là màu đỏ (như Di Động Việt), ép màu chữ shortcut thành trắng cho nổi
// Logic: Nếu chưa set màu text thì mặc định là đen, nhưng shortcut cần nổi bật.
// Ở đây mình sẽ dùng CSS để tô màu trắng cho giống ảnh mẫu.

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

            <?php get_template_part('template-parts/global/mega-menu'); ?>

            <div class="header-search search-desktop"
                style="flex: 1; margin: 0 15px; position: relative; max-width: 400px;">
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

            <div class="header-shortcuts pc-only" style="display: flex; gap: 15px; margin-right: 15px;">

                <a href="tel:18006018" class="shortcut-item">
                    <div class="sc-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.05 12.05 0 0 0 .57 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.03 12.03 0 0 0 2.81.57A2 2 0 0 1 22 16.92z">
                            </path>
                        </svg>
                    </div>
                    <div class="sc-text">
                        <span>Đặt hàng</span>
                        <strong>1800 6018</strong>
                    </div>
                </a>

                <a href="#" class="shortcut-item">
                    <div class="sc-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </div>
                    <div class="sc-text">
                        <span>Cửa hàng</span>
                        <strong>Gần bạn</strong>
                    </div>
                </a>

                <a href="#" class="shortcut-item">
                    <div class="sc-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="sc-text">
                        <span>Tra cứu</span>
                        <strong>Đơn hàng</strong>
                    </div>
                </a>

                <a href="#" class="shortcut-item hide-on-laptop">
                    <div class="sc-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="5" x2="5" y2="19"></line>
                            <circle cx="6.5" cy="6.5" r="2.5"></circle>
                            <circle cx="17.5" cy="17.5" r="2.5"></circle>
                        </svg>
                    </div>
                    <div class="sc-text">
                        <span>Voucher</span>
                        <strong>Khuyến mãi</strong>
                    </div>
                </a>

            </div>

            <div class="header-actions" style="display: flex; align-items: center; gap: 15px; margin-left: auto;">
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

        <div class="header-search-mobile search-mobile" style="padding-bottom: 10px; display: none;">
            <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>"
                style="position: relative;">
                <input type="search" class="search-field" placeholder="Bạn cần tìm gì?..."
                    value="<?php echo get_search_query(); ?>" name="s"
                    style="width: 100%; padding: 0 15px; border: 1px solid #ddd; border-radius: 20px; height: 40px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <button type="submit" class="search-submit"
                    style="position: absolute; right: 10px; top: 0; height: 40px; background: none; border: none; color: #cb1c22; cursor: pointer; display: flex; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>

    </div>
</header>