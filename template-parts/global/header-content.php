<?php
/**
 * Giao diện Header (Clean - No Prefix)
 */

// 1. LẤY DỮ LIỆU
$h_bg     = carbon_get_theme_option( 'header_bg_color' );
$h_height = carbon_get_theme_option( 'header_height' );
$h_text   = carbon_get_theme_option( 'header_text_color' );
$h_sticky = carbon_get_theme_option( 'header_sticky' );

// Default
if ( empty( $h_bg ) )     $h_bg = '#ffffff';
if ( empty( $h_height ) ) $h_height = 70;
if ( empty( $h_text ) )   $h_text = '#333333';

// 2. CLASS CHUẨN
$header_classes = 'header'; // Đã đổi từ re-header -> header
if ( $h_sticky ) $header_classes .= ' sticky';

// 3. STYLE
$header_style = "background-color: {$h_bg} !important;";
$inner_style  = "height: {$h_height}px !important;";
?>

<header id="masthead" class="<?php echo esc_attr($header_classes); ?>" style="<?php echo esc_attr( $header_style ); ?>">
    <div class="container"> <div class="header-inner" style="<?php echo esc_attr( $inner_style ); ?>"> <div class="site-branding">
                <?php $logo_url = carbon_get_theme_option( 'site_logo' ); ?>
                <?php if ( $logo_url ) : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php bloginfo( 'name' ); ?>" 
                             style="max-height: <?php echo intval($h_height) - 20; ?>px; width: auto;">
                    </a>
                <?php else : ?>
                    <p class="site-title" style="margin: 0;">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="font-weight: 900; font-size: 24px; text-transform: uppercase; color: <?php echo esc_attr($h_text); ?>;">
                            <?php bloginfo( 'name' ); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>

            <nav class="main-navigation" style="flex: 1; margin-left: 40px;">
                <?php wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'nav-menu', // Đã đổi re-nav-menu -> nav-menu
                    'fallback_cb'    => false,
                ) ); ?>
            </nav>

            <div class="header-actions" style="display: flex; align-items: center; gap: 15px;">
                <a href="#" class="header-icon search-toggle" style="color: <?php echo esc_attr($h_text); ?>;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </a>

                <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="header-icon cart-icon" style="position: relative; color: <?php echo esc_attr($h_text); ?>;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <?php 
                        $count = WC()->cart->get_cart_contents_count();
                        if ( $count > 0 ) echo '<span class="cart-count">' . esc_html( $count ) . '</span>';
                        ?>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</header>

<style>
    /* CSS Inline hỗ trợ đổi màu */
    .nav-menu > li > a { color: <?php echo esc_attr($h_text); ?> !important; }
</style>