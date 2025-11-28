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

    echo '<nav class="breadcrumbs" style="font-size: 13px; color: #777; ">';

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
function relive_pagination($custom_query = null)
{
    // Nếu không truyền query thì lấy query chính (cho load lần đầu)
    if (! $custom_query) {
        global $wp_query;
        $custom_query = $wp_query;
    }

    $total_pages = $custom_query->max_num_pages;
    if ($total_pages <= 1) return;

    // Lấy trang hiện tại
    $current = max(1, get_query_var('paged'), get_query_var('page'));

    // NẾU LÀ AJAX: Lấy trang từ biến query (vì get_query_var có thể sai trong ajax)
    if (isset($custom_query->query_vars['paged']) && $custom_query->query_vars['paged'] > 0) {
        $current = $custom_query->query_vars['paged'];
    }

    $pages = paginate_links(array(
        'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'    => '?paged=%#%',
        'current'   => $current,
        'total'     => $total_pages,
        'type'      => 'array',
        'prev_text' => '<i class="fas fa-chevron-left"></i>',
        'next_text' => '<i class="fas fa-chevron-right"></i>',
        'mid_size'  => 2,
    ));

    if (is_array($pages)) {
        echo '<div class="shop-pagination-wrap"><ul class="pagination">';
        foreach ($pages as $page) {
            // Fix class active cho đẹp
            $page = str_replace('page-numbers', 'page-link', $page);
            echo '<li class="page-item">' . $page . '</li>';
        }
        echo '</ul></div>';
    }
}


// Thêm vào functions.php
function get_youtube_id_from_url($url)
{
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? $match[1] : '';
}