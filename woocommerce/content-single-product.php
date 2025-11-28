<?php

/**
 * Template: Nội dung chi tiết sản phẩm (Final FPT Style - Price & Promo)
 */
defined('ABSPATH') || exit;
global $product;

// --- 1. LẤY DỮ LIỆU ---
$custom_featured_img = carbon_get_the_post_meta('prod_featured_image');
$video_url           = carbon_get_the_post_meta('prod_video');
$specs_manual        = carbon_get_the_post_meta('product_specs_table');
// [MỚI] Lấy dữ liệu khuyến mãi mới
$fpt_promos          = carbon_get_the_post_meta('fpt_promotions');

$box_images_ids      = carbon_get_the_post_meta('box_images');
$real_images_ids     = carbon_get_the_post_meta('real_images');
$attachment_ids      = $product->get_gallery_image_ids();
$main_image_id       = $product->get_image_id();

// --- 2. XỬ LÝ THÔNG SỐ NỔI BẬT ---
$highlight_specs = array();
if (! empty($specs_manual)) {
    foreach ($specs_manual as $row) {
        if (!empty($row['spec_label']) && !empty($row['spec_value'])) {
            if (!empty($row['is_highlight']) || count($highlight_specs) < 5) {
                $highlight_specs[] = array('label' => $row['spec_label'], 'val' => $row['spec_value'], 'icon' => 'fas fa-check-circle');
            }
        }
    }
}
if (empty($highlight_specs)) {
    $attributes = $product->get_attributes();
    $important_slugs = array('man-hinh', 'chip', 'ram', 'dung-luong-pin', 'camera-sau', 'camera-truoc');
    foreach ($attributes as $attribute) {
        if ($attribute->is_taxonomy()) {
            $tax_slug = str_replace('pa_', '', $attribute->get_name());
            $terms = wp_get_post_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
            if (is_wp_error($terms) || empty($terms)) continue;
            $icon_class = 'fas fa-check-circle';
            if (strpos($tax_slug, 'man-hinh') !== false) $icon_class = 'fas fa-mobile-alt';
            if (strpos($tax_slug, 'chip') !== false) $icon_class = 'fas fa-microchip';
            if (strpos($tax_slug, 'pin') !== false) $icon_class = 'fas fa-battery-full';
            if (in_array($tax_slug, $important_slugs) || count($highlight_specs) < 5) {
                $highlight_specs[] = array('label' => get_taxonomy($attribute->get_name())->labels->singular_name, 'val' => implode(', ', $terms), 'icon' => $icon_class);
            }
        }
    }
}
?>

<div class="container fpt-product-detail" style="padding-top: 0;">

    <div class="row">
        <div class="col col-7 col-md-12">
            <div class="product-gallery-wrap">
                <div class="swiper product-main-slider">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide" data-type="featured">
                            <?php $img_src = $custom_featured_img ? $custom_featured_img : wp_get_attachment_image_url($main_image_id, 'full'); ?>
                            <a href="<?php echo esc_url($img_src); ?>" data-relive-gallery="product-gallery"
                                class="zoom-trigger">
                                <img src="<?php echo esc_url($img_src); ?>" alt="Nổi bật">
                            </a>
                        </div>
                        <?php if (!empty($video_url)):
                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match);
                            $yt_id = isset($match[1]) ? $match[1] : '';
                        ?>
                        <div class="swiper-slide video-slide" data-type="video">
                            <iframe id="prod-video-iframe" width="100%" height="100%"
                                src="https://www.youtube.com/embed/<?php echo $yt_id; ?>?enablejsapi=1&rel=0"
                                frameborder="0" allowfullscreen></iframe>
                        </div>
                        <?php endif; ?>
                        <?php if ($attachment_ids) : foreach ($attachment_ids as $attachment_id) {
                                $img_full = wp_get_attachment_image_url($attachment_id, 'full'); ?>
                        <div class="swiper-slide" data-type="album">
                            <a href="<?php echo esc_url($img_full); ?>" data-relive-gallery="product-gallery"
                                class="zoom-trigger">
                                <?php echo wp_get_attachment_image($attachment_id, 'full'); ?>
                            </a>
                        </div>
                        <?php }
                        endif; ?>
                    </div>
                    <div class="swiper-button-next p-next"></div>
                    <div class="swiper-button-prev p-prev"></div>
                </div>

                <div class="gallery-thumbs-nav-fpt">
                    <div class="g-item active" data-slide-index="0">
                        <div class="g-icon"><i class="fas fa-star"></i></div><span>Nổi bật</span>
                    </div>
                    <?php if (!empty($video_url)): ?><div class="g-item g-item-video" data-slide-index="1">
                        <div class="g-icon"><i class="fas fa-play-circle"></i></div><span>Video</span>
                    </div><?php endif; ?>
                    <?php
                    if ($attachment_ids) {
                        $video_offset = !empty($video_url) ? 1 : 0;
                        $max_thumb = 5;
                        $count = 0;
                        $total_imgs = count($attachment_ids);
                        foreach ($attachment_ids as $idx => $att_id) {
                            if ($count >= $max_thumb) break;
                            $slide_idx = $idx + 1 + $video_offset;
                            $is_last = ($count === $max_thumb - 1 && $total_imgs > $max_thumb);
                            $remain = $total_imgs - $max_thumb;
                    ?>
                    <div class="g-item g-thumb-img" data-slide-index="<?php echo $slide_idx; ?>">
                        <?php echo wp_get_attachment_image($att_id, 'thumbnail'); ?>
                        <?php if ($is_last) : ?><div class="more-count">+<?php echo $remain; ?></div><?php endif; ?>
                    </div>
                    <?php $count++;
                        }
                    } ?>
                </div>
            </div>

            <?php if (! empty($highlight_specs)) : ?>
            <div class="prod-specs-highlight">
                <h3 class="highlight-title">Thông số nổi bật</h3>
                <ul class="specs-list-ul">
                    <?php foreach ($highlight_specs as $spec) : ?>
                    <li><i class="<?php echo esc_attr($spec['icon']); ?>" style="color: #4caf50;"></i> <span
                            class="s-label"><?php echo esc_html($spec['label']); ?>:</span>
                        <strong><?php echo esc_html($spec['val']); ?></strong></li>
                    <?php endforeach; ?>
                </ul>
                <a href="javascript:;" class="view-all-specs" id="btn-open-specs">Xem chi tiết cấu hình <i
                        class="fas fa-caret-down"></i></a>
            </div>
            <?php endif; ?>
        </div>

        <div class="col col-5 col-md-12">

            <h1 class="product_title entry-title" style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">
                <?php the_title(); ?></h1>
            <div style="color: #777; font-size: 13px; margin-bottom: 15px;">
                Mã SP: <?php echo $product->get_sku() ? $product->get_sku() : 'N/A'; ?>
                <span style="margin: 0 5px;">|</span>
                <?php
                $review_count = $product->get_review_count();
                $average      = $product->get_average_rating();
                if ($review_count > 0) : ?>
                Đánh giá: <i class="fas fa-star" style="color: #f5a623;"></i>
                <b><?php echo number_format($average, 1); ?></b> <span style="color:#999;">(<?php echo $review_count; ?>
                    đánh giá)</span>
                <?php else : ?>
                Chưa có đánh giá
                <?php endif; ?>
            </div>

            <div class="prod-variations-box fpt-style-variations" style="margin-bottom: 15px; border-top: none;">
                <?php do_action('woocommerce_single_product_summary'); ?>
            </div>

            <div class="prod-price-box-fpt">
                <?php
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_price(); // Lấy giá đang bán (đã sale)

                // Nếu là biến thể thì giá sẽ tự đổi bằng JS của Woo, ta chỉ cần tạo khung HTML chuẩn
                ?>
                <div class="price-main-fpt"><?php echo $product->get_price_html(); ?></div>
            </div>

            <?php if (! empty($fpt_promos)) : ?>
            <div class="fpt-promo-yellow-box">
                <div class="promo-header-yellow">Chọn 1 trong các khuyến mãi sau:</div>
                <?php foreach ($fpt_promos as $group) : ?>
                <div class="promo-group-item">
                    <h4 class="promo-group-title"><?php echo esc_html($group['promo_title']); ?></h4>
                    <ul class="promo-list-ul">
                        <?php if (!empty($group['promo_items'])):
                                    foreach ($group['promo_items'] as $item): ?>
                        <li>
                            <i class="fas fa-circle"
                                style="font-size: 6px; margin-right: 8px; margin-top: 8px; color: #555;"></i>
                            <div>
                                <?php echo esc_html($item['content']); ?>
                                <?php if (!empty($item['link'])): ?><a href="<?php echo esc_url($item['link']); ?>"
                                    target="_blank" style="color:#288ad6;">Xem chi tiết</a><?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach;
                                endif; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col col-8 col-md-12">
            <div class="white-box prod-content-box"><?php the_content(); ?></div>
        </div>
        <div class="col col-4 col-md-12">
            <?php if (!empty($specs_manual)): ?>
            <div class="white-box full-specs-box">
                <h3 class="section-title">Thông số kỹ thuật</h3>
                <table class="table-specs-sidebar">
                    <?php $preview_data = array_slice($specs_manual, 0, 8);
                        foreach ($preview_data as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row['spec_label']); ?></td>
                        <td><?php echo esc_html($row['spec_value']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <a href="javascript:;" class="btn-show-full-specs" id="btn-open-specs-2">Xem cấu hình chi tiết <i
                        class="fas fa-chevron-right"></i></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($specs_manual)): ?>
<div class="specs-popup-overlay" id="specs-popup">
    <div class="specs-popup-content">
        <div class="sp-header">
            <h3>Thông số kỹ thuật</h3><span class="sp-close" id="btn-close-specs"><i class="fas fa-times"></i></span>
        </div>
        <div class="sp-body">
            <table class="table-specs-full">
                <?php foreach ($specs_manual as $row): ?>
                <tr>
                    <th><?php echo esc_html($row['spec_label']); ?></th>
                    <td><?php echo esc_html($row['spec_value']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>