<?php

/**
 * Template: Nội dung chi tiết sản phẩm (Final FPT Style - Clean)
 */
defined('ABSPATH') || exit;
global $product;

// --- 1. LẤY DỮ LIỆU ---
$custom_featured_img = carbon_get_the_post_meta('prod_featured_image');
$video_url           = carbon_get_the_post_meta('prod_video');
$specs_manual        = carbon_get_the_post_meta('product_specs_table');
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

                        <?php if ($box_images_ids) : foreach ($box_images_ids as $box_id) {
                                $box_full = wp_get_attachment_image_url($box_id, 'full'); ?>
                        <div class="swiper-slide" data-type="box">
                            <a href="<?php echo esc_url($box_full); ?>" data-relive-gallery="product-gallery"
                                class="zoom-trigger"><?php echo wp_get_attachment_image($box_id, 'full'); ?></a>
                        </div>
                        <?php }
                        endif; ?>

                        <?php if ($real_images_ids) : foreach ($real_images_ids as $real_id) {
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
                    <?php if ($has_video): ?><div class="g-item g-item-video" data-slide-index="1">
                        <div class="g-icon"><i class="fas fa-play-circle"></i></div><span>Video</span>
                    </div><?php endif; ?>
                    <?php
                    $video_offset = $has_video ? 1 : 0;
                    $count_album = is_array($attachment_ids) ? count($attachment_ids) : 0;
                    $count_box   = is_array($box_images_ids) ? count($box_images_ids) : 0;
                    $idx_box_start  = 1 + $video_offset + $count_album;
                    $idx_real_start = $idx_box_start + $count_box;

                    if ($attachment_ids) {
                        $max_thumb = 5;
                        $c = 0;
                        foreach ($attachment_ids as $idx => $att_id) {
                            if ($c >= $max_thumb) break;
                            $slide_idx = $idx + 1 + $video_offset;
                            $is_last = ($c === $max_thumb - 1 && $count_album > $max_thumb);
                            $remain = $count_album - $max_thumb;
                    ?>
                    <div class="g-item g-thumb-img" data-slide-index="<?php echo $slide_idx; ?>">
                        <?php echo wp_get_attachment_image($att_id, 'thumbnail'); ?>
                        <?php if ($is_last) : ?><div class="more-count">+<?php echo $remain; ?></div><?php endif; ?>
                    </div>
                    <?php $c++;
                        }
                    } ?>
                    <?php if (!empty($box_images_ids)): ?><div class="g-item"
                        data-slide-index="<?php echo $idx_box_start; ?>">
                        <div class="g-icon"><i class="fas fa-box-open"></i></div><span>Mở hộp</span>
                    </div><?php endif; ?>
                    <?php if (!empty($real_images_ids)): ?><div class="g-item"
                        data-slide-index="<?php echo $idx_real_start; ?>">
                        <div class="g-icon"><i class="fas fa-camera"></i></div><span>Thực tế</span>
                    </div><?php endif; ?>
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

            <div class="prod-variations-box fpt-style-variations" style="margin-bottom: 20px; border-top: none;">
                <?php do_action('woocommerce_single_product_summary'); ?>
            </div>

            <?php
            if ($product->is_type('variable')) {
                $current_price = $product->get_variation_price('min', true);
                $regular_price = $product->get_variation_regular_price('min', true);
            } else {
                $current_price = $product->get_price();
                $regular_price = $product->get_regular_price();
            }
            $percent = 0;
            if ($regular_price > 0 && $current_price < $regular_price) {
                $percent = round((($regular_price - $current_price) / $regular_price) * 100);
            }
            $installment = $current_price > 0 ? ($current_price / 12) : 0;
            ?>

            <div class="fpt-main-box-yellow">
                <div class="fpt-price-block" id="fpt-price-dynamic">
                    <div class="fp-left">
                        <div class="current-price"><?php echo wc_price($current_price); ?></div>
                        <div class="old-price-wrap <?php echo ($percent <= 0) ? 'd-none' : ''; ?>">
                            <del class="regular-price"><?php echo wc_price($regular_price); ?></del>
                            <span class="percent-tag">-<?php echo $percent; ?>%</span>
                        </div>
                        <div class="reward-points"><i class="fas fa-coins" style="color: #b78a28;"></i> +<span
                                class="points-val"><?php echo number_format($current_price / 10000); ?></span> Điểm
                            thưởng</div>
                    </div>
                    <div class="fp-sep"><span>Hoặc</span></div>
                    <div class="fp-right">
                        <span class="lbl-gop">Trả góp</span>
                        <span class="val-gop"><strong
                                class="installment-price"><?php echo number_format($installment, 0, ',', '.'); ?>₫</strong>/tháng</span>
                    </div>
                </div>

                <?php if (! empty($fpt_promos)) : ?>
                <div class="fpt-promo-inner-red">
                    <?php foreach ($fpt_promos as $group) : ?>
                    <div class="promo-group-item">
                        <h4 class="promo-group-title"><?php echo esc_html($group['promo_title']); ?></h4>
                        <ul class="promo-list-ul">
                            <?php if (!empty($group['promo_items'])): foreach ($group['promo_items'] as $item): ?>
                            <li>
                                <i class="fas fa-circle"
                                    style="font-size: 6px; margin-right: 8px; margin-top: 8px; color: #555;"></i>
                                <div>
                                    <?php echo esc_html($item['content']); ?>
                                    <?php if (!empty($item['link'])): ?><a href="<?php echo esc_url($item['link']); ?>"
                                        target="_blank" style="color:#288ad6; font-size: 12px;">Xem chi
                                        tiết</a><?php endif; ?>
                                </div>
                            </li>
                            <?php endforeach;
                                    endif; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                    <div class="corner-ribbon"></div>
                    <i class="fas fa-check ribbon-icon"></i>
                </div>
                <?php endif; ?>
            </div>

            <div class="fpt-bot-action-group">
                <button type="button" class="btn-fpt-cart action-trigger" data-type="add-to-cart">
                    <div class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg></div>
                    <span>Thêm giỏ</span>
                </button>
                <button type="button" class="btn-fpt-buy action-trigger" data-type="buy-now">
                    <strong>MUA NGAY</strong><span>(Giao tận nơi hoặc lấy tại cửa hàng)</span>
                </button>
                <button type="button" class="btn-fpt-installment">
                    <strong>TRẢ GÓP 0%</strong><span>(Duyệt hồ sơ trong 5 phút)</span>
                </button>
            </div>

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