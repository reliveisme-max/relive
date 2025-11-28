<?php

/**
 * Template Danh mục sản phẩm (Final Logic: Hide Filter if Empty)
 */
defined('ABSPATH') || exit;

get_header('shop');
?>

<main id="main" class="site-main" style="background-color: #f4f4f4; padding-bottom: 40px; min-height: 100vh;">

    <div class="container">

        <div class="shop-breadcrumbs" style="padding: 15px 0;">
            <?php if (function_exists('relive_breadcrumbs')) relive_breadcrumbs(); ?>
        </div>

        <?php
        if (is_product_category()) {
            $term_id = get_queried_object_id();
            $slides = carbon_get_term_meta($term_id, 'cat_banner_slider');
            if (! empty($slides)) :
        ?>
        <div class="cat-slider-wrap"
            style="margin-bottom: 20px; border-radius: 12px; overflow: hidden; position: relative;">
            <div class="swiper cat-banner-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($slides as $slide) :
                                $image = $slide['img_pc'];
                                $link = !empty($slide['link']) ? $slide['link'] : '#';
                                if (empty($image)) continue;
                            ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url($link); ?>" class="cat-banner-link" style="display: block;">
                            <img src="<?php echo esc_url($image); ?>" class="banner-responsive" alt="Banner">
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next cb-next"></div>
                <div class="swiper-button-prev cb-prev"></div>
                <div class="swiper-pagination cb-dots"></div>
            </div>
        </div>
        <?php endif;
        } ?>

        <?php
        // KIỂM TRA: NẾU CÓ SẢN PHẨM THÌ MỚI HIỆN BỘ LỌC VÀ LƯỚI SẢN PHẨM
        if (have_posts()) :
        ?>

        <div class="shop-filter-bar">
            <div class="filter-scroll-wrap">

                <button class="filter-btn main-filter-btn" id="btn-open-filter">
                    <i class="fas fa-filter"></i> Lọc
                </button>

                <?php
                    $attribute_taxonomies = wc_get_attribute_taxonomies();
                    // Tạo link reset về danh mục gốc
                    $current_url = is_product_category() ? get_term_link(get_queried_object()) : get_permalink(wc_get_page_id('shop'));

                    if ($attribute_taxonomies) :
                        foreach ($attribute_taxonomies as $tax) :
                            // Ẩn bớt thuộc tính không cần thiết
                            if (!in_array($tax->attribute_name, ['hang', 'thuong-hieu', 'dung-luong', 'mau-sac', 'ram', 'bo-nho'])) {
                                // continue; 
                            }

                            $taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
                            $label = $tax->attribute_label;
                            $filter_key = 'filter_' . $tax->attribute_name;
                            $is_active = isset($_GET[$filter_key]);
                    ?>
                <div class="quick-filter-item">
                    <button class="filter-btn quick-btn <?php echo $is_active ? 'active' : ''; ?>"
                        data-target="filter-group-<?php echo esc_attr($tax->attribute_name); ?>">
                        <?php echo esc_html($label); ?> <i class="fas fa-caret-down"></i>
                    </button>
                </div>
                <?php endforeach;
                    endif; ?>

            </div>

            <div class="tgdd-sort-bar">
                <span class="sort-label">Sắp xếp theo:</span>
                <?php
                    $orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'date';
                    function relive_sort_link($sort_value)
                    {
                        return add_query_arg('orderby', $sort_value);
                    }
                    ?>
                <a href="<?php echo esc_url(relive_sort_link('popularity')); ?>"
                    class="<?php echo $orderby == 'popularity' ? 'active' : ''; ?>"><i class="fas fa-fire"></i> Bán
                    chạy</a>
                <a href="<?php echo esc_url(relive_sort_link('price')); ?>"
                    class="<?php echo $orderby == 'price' ? 'active' : ''; ?>">Giá thấp - cao</a>
                <a href="<?php echo esc_url(relive_sort_link('price-desc')); ?>"
                    class="<?php echo $orderby == 'price-desc' ? 'active' : ''; ?>">Giá cao - thấp</a>
                <a href="<?php echo esc_url(relive_sort_link('date')); ?>"
                    class="<?php echo $orderby == 'date' ? 'active' : ''; ?>">Mới nhất</a>
            </div>
        </div>

        <div class="tgdd-filter-popup" id="filter-popup">
            <div class="fp-overlay"></div>
            <form action="<?php echo esc_url($current_url); ?>" method="GET" class="fp-content">
                <div class="fp-header">
                    <h3>Tất cả bộ lọc</h3>
                    <span class="fp-close" id="btn-close-filter"><i class="fas fa-times"></i> Đóng</span>
                </div>
                <div class="fp-body">
                    <?php
                        if ($attribute_taxonomies) :
                            foreach ($attribute_taxonomies as $tax) :
                                $taxonomy_name = wc_attribute_taxonomy_name($tax->attribute_name);
                                $label = $tax->attribute_label;
                                $terms = get_terms(array('taxonomy' => $taxonomy_name, 'hide_empty' => true));
                                if (empty($terms)) continue;
                                $filter_key = 'filter_' . $tax->attribute_name;
                                $current_values = isset($_GET[$filter_key]) ? (is_array($_GET[$filter_key]) ? $_GET[$filter_key] : explode(',', $_GET[$filter_key])) : array();
                        ?>
                    <div class="fp-group" id="filter-group-<?php echo esc_attr($tax->attribute_name); ?>">
                        <h4 class="fp-group-title"><?php echo esc_html($label); ?></h4>
                        <div class="fp-options">
                            <?php foreach ($terms as $term) :
                                            $checked = in_array($term->slug, $current_values) ? 'checked' : '';
                                        ?>
                            <label class="fp-option-item">
                                <input type="checkbox" name="<?php echo esc_attr($filter_key); ?>[]"
                                    value="<?php echo esc_attr($term->slug); ?>" <?php echo $checked; ?>>
                                <span class="fp-text"><?php echo esc_html($term->name); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach;
                        endif; ?>
                </div>
                <div class="fp-footer-action">
                    <button type="button" class="fp-btn-reset" id="btn-reset-filter">Bỏ chọn</button>
                    <button type="submit" class="fp-submit-btn">Xem kết quả</button>
                </div>
            </form>
        </div>

        <div class="shop-products-grid">
            <?php while (have_posts()) : the_post();
                    wc_get_template_part('content', 'product');
                endwhile; ?>
        </div>

        <div class="shop-pagination" style="margin-top: 30px; text-align: center;">
            <?php if (function_exists('relive_pagination')) relive_pagination();
                else the_posts_pagination(); ?>
        </div>

        <?php else : ?>

        <div class="white-box text-center"
            style="padding: 50px; display: flex; flex-direction: column; align-items: center; justify-content: center;">

            <img src="https://cdn2.cellphones.com.vn/x,webp/media/wysiwyg/Search-Empty.png" alt="Không tìm thấy"
                style="width: 120px; margin-bottom: 15px;">

            <p style="color: #777; font-size: 14px; margin-bottom: 15px;">Không tìm thấy sản phẩm phù hợp.</p>

            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="button"
                style="background: #cb1c22; color: #fff; border-radius: 4px; padding: 5px 20px; text-decoration: none;">
                Quay lại cửa hàng
            </a>

        </div>

        <?php endif; ?>
    </div>
</main>

<?php get_footer('shop'); ?>