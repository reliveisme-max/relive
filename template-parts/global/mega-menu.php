<?php

/**
 * Mega Menu: Production Version (Có Slider Danh mục con)
 */

// Lấy dữ liệu từ Theme Options
$menu_items = carbon_get_theme_option('mega_menu_items');
?>

<div class="header-cat-btn group-cat">
    <span class="icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M2 18C2 16.46 2 15.69 2.347 15.124C2.541 14.807 2.807 14.541 3.124 14.347C3.689 14 4.46 14 6 14C7.54 14 8.31 14 8.876 14.347C9.193 14.541 9.459 14.807 9.653 15.124C10 15.689 10 16.46 10 18C10 19.54 10 20.31 9.653 20.877C9.459 21.193 9.193 21.459 8.876 21.653C8.311 22 7.54 22 6 22C4.46 22 3.69 22 3.124 21.653C2.80735 21.4593 2.54108 21.1934 2.347 20.877C2 20.31 2 19.54 2 18ZM14 18C14 16.46 14 15.69 14.347 15.124C14.541 14.807 14.807 14.541 15.124 14.347C15.689 14 16.46 14 18 14C19.54 14 20.31 14 20.877 14.347C21.193 14.541 21.459 14.807 21.653 15.124C22 15.689 22 16.46 22 18C22 19.54 22 20.31 21.653 20.877C21.4589 21.1931 21.1931 21.4589 20.877 21.653C20.31 22 19.54 22 18 22C16.46 22 15.69 22 15.124 21.653C14.8073 21.4593 14.5411 21.1934 14.347 20.877C14 20.31 14 19.54 14 18ZM2 6C2 4.46 2 3.69 2.347 3.124C2.541 2.807 2.807 2.541 3.124 2.347C3.689 2 4.46 2 6 2C7.54 2 8.31 2 8.876 2.347C9.193 2.541 9.459 2.807 9.653 3.124C10 3.689 10 4.46 10 6C10 7.54 10 8.31 9.653 8.876C9.459 9.193 9.193 9.459 8.876 9.653C8.311 10 7.54 10 6 10C4.46 10 3.69 10 3.124 9.653C2.80724 9.45904 2.54096 9.19277 2.347 8.876C2 8.311 2 7.54 2 6ZM14 6C14 4.46 14 3.69 14.347 3.124C14.541 2.807 14.807 2.541 15.124 2.347C15.689 2 16.46 2 18 2C19.54 2 20.31 2 20.877 2.347C21.193 2.541 21.459 2.807 21.653 3.124C22 3.689 22 4.46 22 6C22 7.54 22 8.31 21.653 8.876C21.459 9.193 21.193 9.459 20.877 9.653C20.31 10 19.54 10 18 10C16.46 10 15.69 10 15.124 9.653C14.8072 9.45904 14.541 9.19277 14.347 8.876C14 8.311 14 7.54 14 6Z"
                stroke="#FAFAFA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
    </span>
    <span class="text">Danh mục</span>

    <div class="mega-menu-wrapper">
        <div class="mega-overlay"></div>

        <div class="mega-content-container">
            <div class="mobile-mega-header">
                <span class="m-title">Danh mục sản phẩm</span>
                <span class="m-close"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg></span>
            </div>

            <div class="split-layout">
                <ul class="cat-list-left">
                    <?php if (! empty($menu_items)) : ?>
                        <?php foreach ($menu_items as $index => $item) :
                            $active_class = ($index === 0) ? 'active' : '';
                            $unique_id = 'menu-item-' . $index;
                        ?>
                            <li class="cat-item-left <?php echo $active_class; ?>" data-id="<?php echo $unique_id; ?>">
                                <a href="<?php echo esc_url($item['link']); ?>">
                                    <span class="c-icon">
                                        <?php if ($item['icon_type'] == 'image' && !empty($item['icon_img'])) : ?>
                                            <?php echo wp_get_attachment_image($item['icon_img'], 'thumbnail'); ?>
                                        <?php elseif (!empty($item['icon_font'])) : ?>
                                            <i class="<?php echo esc_attr($item['icon_font']); ?>"></i>
                                        <?php else : ?>
                                            <i class="fas fa-circle" style="font-size: 8px;"></i>
                                        <?php endif; ?>
                                    </span>
                                    <span class="c-name"><?php echo esc_html($item['title']); ?></span>
                                    <span class="c-arrow"><i class="fas fa-chevron-right"></i></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li style="padding:15px; color: #999; font-size: 13px;">Vui lòng thêm menu trong Admin</li>
                    <?php endif; ?>
                </ul>

                <div class="cat-content-right">
                    <?php if (! empty($menu_items)) : ?>
                        <?php foreach ($menu_items as $index => $item) :
                            $active_class = ($index === 0) ? 'active' : '';
                            $unique_id = 'menu-item-' . $index;
                            $swiper_id = 'sub-swiper-' . $index; // ID riêng cho Slider
                        ?>
                            <div id="<?php echo $unique_id; ?>" class="cat-pane <?php echo $active_class; ?>">

                                <?php if (! empty($item['brands'])) : ?>
                                    <div class="pane-section">
                                        <h4 class="pane-title">Thương hiệu nổi bật</h4>
                                        <div class="brand-grid">
                                            <?php foreach ($item['brands'] as $brand) :
                                                if (empty($brand['brand_logo'])) continue;
                                                $b_logo = wp_get_attachment_image_url($brand['brand_logo'], 'full');
                                            ?>
                                                <a href="<?php echo esc_url($brand['brand_link']); ?>" class="brand-item">
                                                    <img src="<?php echo esc_url($b_logo); ?>" alt="Brand">
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (! empty($item['sub_cats'])) : ?>
                                    <div class="pane-section">
                                        <h4 class="pane-title">Danh mục sản phẩm</h4>

                                        <div class="sub-cat-swiper-wrap" style="position: relative;">
                                            <div class="swiper sub-cat-slider">
                                                <div class="swiper-wrapper">
                                                    <?php foreach ($item['sub_cats'] as $sub) :
                                                        $term = get_term($sub['id']);
                                                        if (! $term || is_wp_error($term)) continue;

                                                        // Lấy ảnh thumbnail danh mục
                                                        $thumb_id = get_term_meta($sub['id'], 'thumbnail_id', true);
                                                        $img_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');

                                                        // Ảnh mặc định nếu chưa có
                                                        if (! $img_url) {
                                                            $img_url = 'https://placehold.co/100x100/f1f1f1/999?text=Icon';
                                                        }
                                                    ?>
                                                        <div class="swiper-slide">
                                                            <a href="<?php echo get_term_link($term); ?>" class="sub-cat-item">
                                                                <div class="sc-img">
                                                                    <img src="<?php echo esc_url($img_url); ?>"
                                                                        alt="<?php echo esc_attr($term->name); ?>">
                                                                </div>
                                                                <span class="sc-name"><?php echo esc_html($term->name); ?></span>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <div class="swiper-button-next sc-next"></div>
                                            <div class="swiper-button-prev sc-prev"></div>
                                        </div>

                                    </div>
                                <?php endif; ?>

                                <?php if (! empty($item['manual_products'])) : ?>
                                    <div class="pane-section">
                                        <h4 class="pane-title">Sản phẩm nổi bật</h4>
                                        <div class="mini-prod-list">
                                            <?php foreach ($item['manual_products'] as $p_assoc) :
                                                $post_id = $p_assoc['id'];
                                                $product = wc_get_product($post_id);
                                                if (! $product) continue;
                                            ?>
                                                <a href="<?php echo get_permalink($post_id); ?>" class="mini-prod-item">
                                                    <div class="mp-img">
                                                        <?php echo $product->get_image('thumbnail'); ?>
                                                    </div>
                                                    <div class="mp-info">
                                                        <div class="mp-name"><?php echo $product->get_name(); ?></div>
                                                        <div class="mp-price"><?php echo $product->get_price_html(); ?></div>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="pane-footer" style="margin-top: 20px;">
                                    <a href="<?php echo esc_url($item['link']); ?>" class="btn-view-all">
                                        <?php echo !empty($item['view_all_text']) ? esc_html($item['view_all_text']) : 'Xem tất cả'; ?>
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>