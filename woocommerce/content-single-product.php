<?php

/**
 * Template: Nội dung chi tiết sản phẩm (Fixed Fancybox Double Open)
 */
defined('ABSPATH') || exit;
global $product;

// --- 1. LẤY DỮ LIỆU ---
$custom_featured_img = carbon_get_the_post_meta('prod_featured_image');
$video_url           = carbon_get_the_post_meta('prod_video');
$specs_manual        = carbon_get_the_post_meta('product_specs_table');
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
                        <?php
                        $has_video = !empty($video_url);
                        if ($has_video):
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
                        <?php
                        $box_start_index = 0;
                        if ($box_images_ids) :
                            $box_start_index = 1 + ($has_video ? 1 : 0) + count($attachment_ids);
                            foreach ($box_images_ids as $box_id) {
                                $box_full = wp_get_attachment_image_url($box_id, 'full'); ?>
                        <div class="swiper-slide" data-type="box">
                            <a href="<?php echo esc_url($box_full); ?>" data-relive-gallery="product-gallery"
                                class="zoom-trigger"><?php echo wp_get_attachment_image($box_id, 'full'); ?></a>
                        </div>
                        <?php }
                        endif; ?>
                        <?php
                        $real_start_index = 0;
                        if ($real_images_ids) :
                            $real_start_index = $box_start_index + count($box_images_ids ?: []);
                            foreach ($real_images_ids as $real_id) {
                                $real_full = wp_get_attachment_image_url($real_id, 'full'); ?>
                        <div class="swiper-slide" data-type="real">
                            <a href="<?php echo esc_url($real_full); ?>" data-relive-gallery="product-gallery"
                                class="zoom-trigger"><?php echo wp_get_attachment_image($real_id, 'full'); ?></a>
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
                    <?php if ($has_video): ?>
                    <div class="g-item g-item-video" data-slide-index="1">
                        <div class="g-icon"><i class="fas fa-play-circle"></i></div><span>Video</span>
                    </div>
                    <?php endif; ?>
                    <?php
                    if ($attachment_ids) {
                        $video_offset = $has_video ? 1 : 0;
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
                    }
                    ?>
                    <?php if (!empty($box_images_ids)): ?>
                    <div class="g-item" data-slide-index="<?php echo $box_start_index; ?>">
                        <div class="g-icon"><i class="fas fa-box-open"></i></div><span>Mở hộp</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($real_images_ids)): ?>
                    <div class="g-item" data-slide-index="<?php echo $real_start_index; ?>">
                        <div class="g-icon"><i class="fas fa-camera"></i></div><span>Thực tế</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (! empty($highlight_specs)) : ?>
            <div class="prod-specs-highlight">
                <h3 class="highlight-title">Thông số nổi bật</h3>
                <ul class="specs-list-ul">
                    <?php foreach ($highlight_specs as $spec) : ?>
                    <li><i class="<?php echo esc_attr($spec['icon']); ?>" style="color: #4caf50;"></i> <span
                            class="s-label"><?php echo esc_html($spec['label']); ?>:</span>
                        <strong><?php echo esc_html($spec['val']); ?></strong>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a href="javascript:;" class="view-all-specs" id="btn-open-specs">Xem chi tiết cấu hình <i
                        class="fas fa-caret-down"></i></a>
            </div>
            <?php endif; ?>
            <div class="prod-policy-box">
                <div class="policy-row">
                    <div class="p-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="p-text">Hư gì đổi nấy <b>12 tháng</b> tại 3000 siêu thị toàn quốc</div>
                </div>
                <div class="policy-row">
                    <div class="p-icon"><i class="fas fa-shipping-fast"></i></div>
                    <div class="p-text">Giao hàng nhanh toàn quốc, miễn phí vận chuyển</div>
                </div>
            </div>
        </div>
        <div class="col col-5 col-md-12">
            <div class="prod-price-box">
                <div class="price-show"><?php echo $product->get_price_html(); ?></div>
                <div class="installment-label">Trả góp 0%</div>
            </div>
            <div class="prod-variations-box fpt-style-variations">
                <?php do_action('woocommerce_single_product_summary'); ?></div>
            <div class="fpt-promo-box">
                <div class="promo-header"><i class="fas fa-gift"></i> ƯU ĐÃI THÊM</div>
                <div class="promo-content">
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Giảm thêm 5% khi mua cùng Apple Watch</li>
                        <li><i class="fas fa-check-circle"></i> Thu cũ đổi mới trợ giá đến 2 triệu</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 30px;">
        <div class="col col-8 col-md-12">
            <div class="white-box prod-content-box"><?php the_content(); ?></div>
        </div>
        <div class="col col-4 col-md-12">
            <?php $table_data = !empty($specs_manual) ? $specs_manual : $highlight_specs;
            if (!empty($table_data)): ?>
            <div class="white-box full-specs-box">
                <h3 class="section-title">Thông số kỹ thuật</h3>
                <table class="table-specs-sidebar">
                    <?php $preview_data = array_slice($table_data, 0, 8);
                        foreach ($preview_data as $row):
                            $lbl = isset($row['spec_label']) ? $row['spec_label'] : (isset($row['label']) ? $row['label'] : '');
                            $val = isset($row['spec_value']) ? $row['spec_value'] : (isset($row['val']) ? $row['val'] : '');
                            if (!$lbl) continue; ?>
                    <tr>
                        <td><?php echo esc_html($lbl); ?></td>
                        <td><?php echo esc_html($val); ?></td>
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

<?php if (!empty($table_data)): ?>
<div class="specs-popup-overlay" id="specs-popup">
    <div class="specs-popup-content">
        <div class="sp-header">
            <h3>Thông số kỹ thuật</h3><span class="sp-close" id="btn-close-specs"><i class="fas fa-times"></i></span>
        </div>
        <div class="sp-body">
            <table class="table-specs-full">
                <?php foreach ($table_data as $row):
                        $lbl = isset($row['spec_label']) ? $row['spec_label'] : (isset($row['label']) ? $row['label'] : '');
                        $val = isset($row['spec_value']) ? $row['spec_value'] : (isset($row['val']) ? $row['val'] : '');
                        if (!$lbl) continue; ?>
                <tr>
                    <th><?php echo esc_html($lbl); ?></th>
                    <td><?php echo esc_html($val); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>