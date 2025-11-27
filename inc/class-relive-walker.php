<?php
// FILE: inc/class-relive-walker.php

class Relive_Mega_Walker extends Walker_Nav_Menu
{

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {

        if ($depth === 0) {

            // LẤY DỮ LIỆU TỪ ADMIN
            $brands   = carbon_get_nav_menu_item_meta($item->ID, 'mm_selected_brands'); // <-- Đã đổi tên field
            $cats     = carbon_get_nav_menu_item_meta($item->ID, 'mm_cats');
            $products = carbon_get_nav_menu_item_meta($item->ID, 'mm_products');

            if (!empty($brands) || !empty($cats) || !empty($products)) {

                $output .= '<div class="mega-menu-wrapper">';
                $output .= '<div class="mega-main-content">';

                // --- A. THƯƠNG HIỆU (Lấy từ Taxonomy) ---
                if (!empty($brands)) {
                    $output .= '<div class="mega-section mega-brands">';
                    $output .= '<div class="mega-header-row"><span class="mega-label">Thương hiệu</span><a href="' . $item->url . '" class="view-all">Xem tất cả</a></div>';
                    $output .= '<div class="brand-grid">';

                    foreach ($brands as $b) {
                        // $b['id'] là ID của term thương hiệu
                        $term = get_term($b['id']);
                        if (!$term || is_wp_error($term)) continue;

                        // Lấy Link thương hiệu
                        $link = get_term_link($term);

                        // Lấy Ảnh thương hiệu (Logic chuẩn của Woo & Plugin Brand)
                        // 1. Thử lấy meta 'thumbnail_id' (Chuẩn Woo)
                        $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);

                        // 2. Nếu không có, thử lấy meta 'image' (Một số plugin khác)
                        if (!$thumb_id) $thumb_id = get_term_meta($term->term_id, 'image', true);

                        $img_url = '';
                        if ($thumb_id) {
                            // Nếu ID là số -> Get URL
                            if (is_numeric($thumb_id)) {
                                $img_url = wp_get_attachment_image_url($thumb_id, 'full');
                            } else {
                                // Một số plugin lưu thẳng URL vào meta
                                $img_url = $thumb_id;
                            }
                        }

                        // Ảnh dự phòng nếu không tìm thấy logo
                        if (!$img_url) $img_url = 'https://placehold.co/100x40/f5f5f5/999?text=' . $term->name;

                        $output .= '<a href="' . $link . '" class="brand-item" title="' . $term->name . '">';
                        $output .= '<img src="' . $img_url . '" alt="' . $term->name . '">';
                        $output .= '</a>';
                    }
                    $output .= '</div></div>';
                }

                // --- B. DANH MỤC (Dòng HOT) ---
                if (!empty($cats)) {
                    $output .= '<div class="mega-section mega-sub-cats">';
                    $output .= '<span class="mega-label">Dòng sản phẩm HOT</span>';
                    $output .= '<div class="sub-cat-grid">';
                    foreach ($cats as $c) {
                        $term = get_term($c['id'], 'product_cat');
                        if (!$term || is_wp_error($term)) continue;

                        $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                        $img_url  = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                        if (!$img_url) $img_url = 'https://placehold.co/60x60/f9f9f9/ddd?text=' . mb_substr($term->name, 0, 1);

                        $output .= '<a href="' . get_term_link($term) . '" class="sub-cat-item">';
                        $output .= '<div class="sc-img"><img src="' . $img_url . '" alt="' . $term->name . '"></div>';
                        $output .= '<span>' . $term->name . '</span>';
                        $output .= '</a>';
                    }
                    $output .= '</div></div>';
                }

                $output .= '</div>'; // End content

                // --- C. SẢN PHẨM ---
                if (!empty($products)) {
                    $output .= '<div class="mega-sidebar-products">';
                    $output .= '<span class="mega-label">Sản phẩm giá sốc</span>';
                    $output .= '<div class="mega-prod-list">';
                    foreach ($products as $p) {
                        $prod = wc_get_product($p['id']);
                        if (!$prod) continue;
                        $output .= '
                        <a href="' . $prod->get_permalink() . '" class="mega-prod-item">
                            <div class="mp-img">' . $prod->get_image('thumbnail') . '</div>
                            <div class="mp-info">
                                <div class="mp-title">' . $prod->get_name() . '</div>
                                <div class="mp-price">' . $prod->get_price_html() . '</div>
                            </div>
                        </a>';
                    }
                    $output .= '</div></div>';
                }

                $output .= '</div>'; // End wrapper
            }
        }
        $output .= "</li>\n";
    }
}