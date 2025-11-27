<?php

/**
 * Relive Helper Functions
 * Chứa: Breadcrumbs, Phân trang, Cắt chuỗi
 */

/**
 * 1. CUSTOM BREADCRUMBS (Thanh điều hướng chuẩn SEO)
 * Hiển thị: Trang chủ / Danh mục / Tên bài viết
 */
function relive_breadcrumbs()
{
    // Không hiện ở trang chủ
    if (is_front_page()) return;

    echo '<nav class="breadcrumbs" style="font-size: 13px; color: #777; margin-bottom: 20px;">';

    // Link trang chủ
    echo '<a href="' . home_url() . '" style="color: #333;">Trang chủ</a>';
    echo ' <span class="divider">/</span> ';

    // Logic cho WooCommerce
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout())) {
        if (is_shop()) {
            echo '<span>Cửa hàng</span>';
        } elseif (is_product_category() || is_product_tag()) {
            $term = get_queried_object();
            echo '<span>' . $term->name . '</span>';
        } elseif (is_product()) {
            // Hiện danh mục cha của sản phẩm
            global $post;
            $terms = get_the_terms($post->ID, 'product_cat');
            if ($terms && ! is_wp_error($terms)) {
                $term = array_shift($terms);
                echo '<a href="' . get_term_link($term) . '" style="color: #333;">' . $term->name . '</a>';
                echo ' <span class="divider">/</span> ';
            }
            echo '<span style="color: #cb1c22; font-weight: bold;">' . get_the_title() . '</span>';
        } elseif (is_cart()) {
            echo '<span>Giỏ hàng</span>';
        } elseif (is_checkout()) {
            echo '<span>Thanh toán</span>';
        }
    }
    // Logic cho Blog
    elseif (is_category()) {
        single_cat_title();
    } elseif (is_single()) {
        // Hiện category của bài viết
        $cats = get_the_category();
        if ($cats) {
            echo '<a href="' . get_category_link($cats[0]->term_id) . '" style="color: #333;">' . $cats[0]->name . '</a>';
            echo ' <span class="divider">/</span> ';
        }
        echo '<span>' . get_the_title() . '</span>';
    } elseif (is_page()) {
        echo '<span>' . get_the_title() . '</span>';
    } elseif (is_search()) {
        echo '<span>Tìm kiếm: "' . get_search_query() . '"</span>';
    }

    echo '</nav>';
}

/**
 * 2. CUSTOM PAGINATION (Phân trang dạng số 1 2 3)
 * Thay thế cho "Previous / Next" mặc định
 */
function relive_pagination()
{
    global $wp_query;

    $big = 999999999; // Số ngẫu nhiên lớn để thay thế link

    $pages = paginate_links(array(
        'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format'    => '?paged=%#%',
        'current'   => max(1, get_query_var('paged')),
        'total'     => $wp_query->max_num_pages,
        'type'      => 'array',
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ));

    if (is_array($pages)) {
        echo '<ul class="pagination" style="display: flex; gap: 5px; justify-content: center; margin-top: 40px;">';
        foreach ($pages as $page) {
            // Style inline (Sau này đưa vào CSS)
            $active_style = strpos($page, 'current') !== false ? 'background: #cb1c22; color: #fff; border-color: #cb1c22;' : 'background: #fff; color: #333;';

            echo '<li class="page-item" style="list-style: none;">';
            echo str_replace('page-numbers', 'page-link', $page);
            echo '</li>';
        }
        echo '</ul>';

        // CSS Inline nhỏ cho phân trang đẹp luôn
        echo '<style>
            .pagination .page-link {
                display: block; padding: 8px 15px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; font-weight: bold;
            }
            .pagination .page-link.current {
                background: var(--primary-color, #cb1c22); color: #fff; border-color: var(--primary-color, #cb1c22);
            }
            .pagination .page-link:hover:not(.current) { background: #f0f0f0; }
        </style>';
    }
}

/**
 * HIỂN THỊ ICON TRONG MENU (FIX LỖI REGEX)
 */
add_filter('walker_nav_menu_start_el', 'relive_nav_menu_icon', 10, 4);

function relive_nav_menu_icon($item_output, $item, $depth, $args)
{
    // Chỉ áp dụng cho menu dọc (Vertical)
    if ($args->theme_location == 'vertical') {

        $icon_class = carbon_get_nav_menu_item_meta($item->ID, 'menu_icon_class');
        $icon_img   = carbon_get_nav_menu_item_meta($item->ID, 'menu_icon_img');

        $icon_html = '';

        // Ưu tiên ảnh trước
        if ($icon_img) {
            $icon_html = '<img src="' . esc_url($icon_img) . '" class="menu-icon-img" alt="" />';
        }
        // Sau đó đến FontAwesome
        elseif ($icon_class) {
            $icon_html = '<i class="' . esc_attr($icon_class) . ' menu-icon-fa"></i>';
        }

        // Chèn icon vào trước nội dung text
        if ($icon_html) {
            // FIX: Dùng dấu thăng # làm ranh giới thay vì dấu / để tránh lỗi với thẻ </a>
            return preg_replace(
                '#(<a[^>]*>)(.*?)(</a>)#i',
                '$1<span class="menu-icon-wrap">' . $icon_html . '</span><span class="menu-text">$2</span>$3',
                $item_output
            );
        }
    }
    return $item_output;
}
