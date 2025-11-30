<?php

/**
 * Template: Nội dung chi tiết sản phẩm (Full Features + Reviews + Full Width Description)
 */
defined('ABSPATH') || exit;
global $product;

// 1. LẤY DỮ LIỆU
$custom_featured_img = carbon_get_the_post_meta('prod_featured_image');
$video_url           = carbon_get_the_post_meta('prod_video');
$fpt_promos          = carbon_get_the_post_meta('fpt_promotions');
$box_images_ids      = carbon_get_the_post_meta('box_images');
$real_images_ids     = carbon_get_the_post_meta('real_images');
$attachment_ids      = $product->get_gallery_image_ids();
$main_image_id       = $product->get_image_id();

$spec_groups         = carbon_get_the_post_meta('fpt_specs_groups');
$spec_feature_img    = carbon_get_the_post_meta('spec_feature_image');

// Dữ liệu Mới
$bought_items        = carbon_get_the_post_meta('fpt_bought_together');
$coupons_data        = carbon_get_the_post_meta('product_coupons');

function get_fpt_icon($label)
{
    $l = mb_strtolower($label, 'UTF-8');
    if (strpos($l, 'chip') !== false) return 'fas fa-microchip';
    if (strpos($l, 'màn') !== false) return 'fas fa-mobile-alt';
    if (strpos($l, 'pin') !== false) return 'fas fa-battery-full';
    if (strpos($l, 'ram') !== false) return 'fas fa-memory';
    if (strpos($l, 'cam') !== false) return 'fas fa-camera';
    return 'fas fa-cog';
}

$highlight_specs = array();
if (! empty($spec_groups)) {
    foreach ($spec_groups as $group) {
        if (!empty($group['group_items'])) {
            foreach ($group['group_items'] as $item) {
                if (!empty($item['is_highlight']) && count($highlight_specs) < 6) {
                    $val = isset($item['spec_val']) ? $item['spec_val'] : '';
                    $icon = !empty($item['icon']) ? $item['icon'] : get_fpt_icon($item['label']);
                    $highlight_specs[] = array('label' => $item['label'], 'val' => $val, 'icon' => $icon);
                }
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
                            <a href="<?php echo esc_url($custom_featured_img ?: wp_get_attachment_image_url($main_image_id, 'full')); ?>"
                                class="zoom-trigger">
                                <img src="<?php echo esc_url($custom_featured_img ?: wp_get_attachment_image_url($main_image_id, 'full')); ?>"
                                    alt="Nổi bật">
                            </a>
                        </div>
                        <?php if (!empty($video_url)): preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match);
                            $yt_id = isset($match[1]) ? $match[1] : '';
                            if ($yt_id): ?>
                        <div class="swiper-slide video-slide" data-type="video"><iframe id="prod-video-iframe"
                                width="100%" height="100%"
                                src="https://www.youtube.com/embed/<?php echo $yt_id; ?>?enablejsapi=1&rel=0"
                                frameborder="0" allowfullscreen></iframe></div>
                        <?php endif;
                        endif; ?>
                        <?php if ($attachment_ids) : foreach ($attachment_ids as $attachment_id) { ?>
                        <div class="swiper-slide"><a href="#"
                                class="zoom-trigger"><?php echo wp_get_attachment_image($attachment_id, 'full'); ?></a>
                        </div>
                        <?php }
                        endif; ?>
                        <?php if ($box_images_ids) : foreach ($box_images_ids as $box_id) { ?>
                        <div class="swiper-slide" data-type="box"><a href="#"
                                class="zoom-trigger"><?php echo wp_get_attachment_image($box_id, 'full'); ?></a></div>
                        <?php }
                        endif; ?>
                        <?php if ($real_images_ids) : foreach ($real_images_ids as $real_id) { ?>
                        <div class="swiper-slide" data-type="real"><a href="#"
                                class="zoom-trigger"><?php echo wp_get_attachment_image($real_id, 'full'); ?></a></div>
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
                    <?php if ($has_video = !empty($video_url)): ?><div class="g-item g-item-video" data-slide-index="1">
                        <div class="g-icon"><i class="fas fa-play-circle"></i></div><span>Video</span>
                    </div><?php endif; ?>
                    <?php $video_offset = $has_video ? 1 : 0;
                    if ($attachment_ids) {
                        $c = 0;
                        foreach ($attachment_ids as $idx => $att_id) {
                            if ($c >= 6) break;
                            echo '<div class="g-item g-thumb-img" data-slide-index="' . ($idx + 1 + $video_offset) . '">' . wp_get_attachment_image($att_id, 'thumbnail') . '</div>';
                            $c++;
                        }
                    } ?>
                    <?php if (!empty($box_images_ids)): ?><div class="g-item"
                        data-slide-index="<?php echo 1 + $video_offset + (is_array($attachment_ids) ? count($attachment_ids) : 0); ?>">
                        <div class="g-icon"><i class="fas fa-box-open"></i></div><span>Mở hộp</span>
                    </div><?php endif; ?>
                    <?php if (!empty($real_images_ids)): ?><div class="g-item"
                        data-slide-index="<?php echo 1 + $video_offset + (is_array($attachment_ids) ? count($attachment_ids) : 0) + (is_array($box_images_ids) ? count($box_images_ids) : 0); ?>">
                        <div class="g-icon"><i class="fas fa-camera"></i></div><span>Thực tế</span>
                    </div><?php endif; ?>
                </div>
            </div>

            <?php if (! empty($highlight_specs)) : ?>
            <div class="fpt-specs-box">
                <div class="head-specs">
                    <h3 class="title">Thông số nổi bật</h3><a href="javascript:;" class="btn-view-more-specs"
                        id="btn-open-specs">Xem tất cả thông số</a>
                </div>
                <div class="specs-grid-list">
                    <?php foreach ($highlight_specs as $spec) : ?>
                    <div class="spec-item">
                        <div class="spec-icon"><i class="<?php echo esc_attr($spec['icon']); ?>"></i></div>
                        <div class="spec-content"><span
                                class="spec-label"><?php echo esc_html($spec['label']); ?></span><strong
                                class="spec-val"><?php echo esc_html($spec['val']); ?></strong></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="fpt-policy-box">
                <div class="policy-head">
                    <h3>Chính sách sản phẩm</h3>
                </div>
                <div class="policy-list">
                    <div class="p-item"><i class="fas fa-shield-alt"></i> <span>Hư gì đổi nấy <b>12 tháng</b></span>
                    </div>
                    <div class="p-item"><i class="fas fa-shipping-fast"></i> <span>Giao hàng nhanh toàn quốc</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col col-5 col-md-12">
            <h1 class="product_title entry-title" style="font-size: 22px; font-weight: 700; margin-bottom: 5px;">
                <?php the_title(); ?>
            </h1>

            <div class="product-meta-header"
                style="display: flex; align-items: center; font-size: 13px; color: #555; margin-bottom: 15px;">
                <span class="sku-wrapper">
                    Mã SP: <span class="sku"
                        style="color: #333; font-weight: 600;"><?php echo ($sku = $product->get_sku()) ? $sku : 'N/A'; ?></span>
                </span>
                <span style="margin: 0 8px; color: #ddd;">|</span>
                <span class="review-wrapper" style="display: flex; align-items: center;">
                    <?php
                    $rating_count = $product->get_rating_count();
                    $average      = $product->get_average_rating();
                    if ($rating_count > 0) : ?>
                    <span style="margin-right: 3px;">Đánh giá:</span>
                    <span
                        style="font-weight: 700; color: #f5a623; margin-right: 3px;"><?php echo number_format($average, 1); ?></span>
                    <i class="fas fa-star" style="color: #f5a623; font-size: 12px; margin-right: 5px;"></i>
                    <a href="#prod-reviews" style="color: #288ad6; text-decoration: none;">(<?php echo $rating_count; ?>
                        đánh giá)</a>
                    <?php else : ?>
                    <span>Chưa có đánh giá</span>
                    <?php endif; ?>
                </span>
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
                    </div>
                    <div class="fp-right">
                        <span class="lbl-gop">Trả góp</span>
                        <span class="val-gop"><strong
                                class="installment-price"><?php echo number_format($installment, 0, ',', '.'); ?>₫</strong>/tháng</span>
                    </div>
                </div>
                <?php if (! empty($fpt_promos)) : ?>
                <div class="fpt-promo-box">
                    <div class="promo-header">
                        <i class="fas fa-gift"></i> Khuyến mãi được hưởng
                    </div>
                    <div class="fpt-promo-inner-red">
                        <?php foreach ($fpt_promos as $group) : ?>
                        <div class="promo-group-item">
                            <ul class="promo-list-ul">
                                <?php if (!empty($group['promo_items'])): foreach ($group['promo_items'] as $item): ?>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <div class="promo-text">
                                        <?php echo esc_html($item['content']); ?>
                                        <?php if (!empty($item['link'])): ?>
                                        <a href="<?php echo esc_url($item['link']); ?>" target="_blank"
                                            class="promo-link">Xem chi tiết</a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach;
                                        endif; ?>
                            </ul>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (! empty($coupons_data)) : ?>
            <div class="fpt-coupon-section">
                <div class="c-title"><i class="fas fa-ticket-alt"></i> MÃ GIẢM GIÁ THÊM</div>
                <div class="coupon-list">
                    <?php foreach ($coupons_data as $c_item) :
                            $c_id = $c_item['id'];
                            $coupon = new WC_Coupon($c_id);
                            if (!$coupon || !$coupon->get_id()) continue;

                            $code = $coupon->get_code();
                            $amount = $coupon->get_amount();
                            $type = $coupon->get_discount_type();

                            $desc = '';
                            if ($type == 'percent') $desc = 'Giảm thêm ' . $amount . '%';
                            elseif ($type == 'fixed_cart' || $type == 'fixed_product') $desc = 'Giảm thêm ' . number_format($amount, 0, ',', '.') . 'đ';
                            else $desc = 'Nhập mã để nhận ưu đãi';
                        ?>
                    <div class="coupon-item">
                        <div class="c-info">
                            <span class="c-code"><?php echo esc_html(strtoupper($code)); ?></span>
                            <span class="c-desc"><?php echo esc_html($desc); ?></span>
                        </div>
                        <button class="btn-copy-code" data-code="<?php echo esc_attr($code); ?>">Sao chép</button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="fpt-bot-action-group">
                <button type="button" class="btn-fpt-cart action-trigger" data-type="add-to-cart">
                    <div class="icon"><i class="fas fa-cart-plus"></i></div><span>Thêm giỏ</span>
                </button>
                <button type="button" class="btn-fpt-buy action-trigger" data-type="buy-now"><strong>MUA
                        NGAY</strong><span>(Giao tận nơi hoặc lấy tại cửa hàng)</span></button>
                <button type="button" class="btn-fpt-installment"><strong>TRẢ GÓP 0%</strong><span>(Duyệt hồ sơ trong 5
                        phút)</span></button>
            </div>

            <?php if (! empty($bought_items)) : ?>
            <div class="bought-together-box" style="margin-top: 20px;">
                <div class="bt-header"><span class="bt-icon"><i class="fas fa-fire"></i></span>
                    <h3>Mua kèm giá sốc</h3>
                </div>
                <form class="bt-list-form">
                    <?php foreach ($bought_items as $item_data) :
                            $assoc = isset($item_data['product_assoc']) ? $item_data['product_assoc'] : array();
                            if (empty($assoc)) continue;
                            $p_id = $assoc[0]['id'];
                            $p = wc_get_product($p_id);
                            if (! $p || ! $p->is_visible()) continue;

                            $percent = isset($item_data['percent_sale']) ? intval($item_data['percent_sale']) : 0;
                            $regular_price = $p->get_price();
                            $sale_price = $regular_price;
                            if ($percent > 0) $sale_price = $regular_price * (100 - $percent) / 100;
                        ?>
                    <div class="bt-item">
                        <div class="bt-img"><a href="<?php echo get_permalink($p_id); ?>"
                                target="_blank"><?php echo $p->get_image('thumbnail'); ?></a></div>
                        <div class="bt-info">
                            <a href="<?php echo get_permalink($p_id); ?>" target="_blank"
                                class="bt-title"><?php echo $p->get_name(); ?></a>
                            <div class="bt-price"><?php if ($percent > 0): echo wc_price($sale_price); ?>
                                <del><?php echo wc_price($regular_price); ?></del><?php else: echo $p->get_price_html();
                                                                                            endif; ?>
                            </div>
                            <?php if ($percent > 0): ?><div class="bt-promo-text">Giảm thêm <?php echo $percent; ?>%
                            </div><?php endif; ?>
                        </div>
                        <div class="bt-action">
                            <label class="bt-checkbox-btn"><input type="checkbox" name="add_bought_together[]"
                                    value="<?php echo esc_attr($p_id); ?>"><span class="btn-select-add">Thêm <i
                                        class="fas fa-plus"></i></span></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">
        <div class="col col-12">
            <div class="white-box prod-content-box" id="prod-description">
                <h2 class="section-title">Đặc điểm nổi bật</h2>
                <div class="content-body-wrap">
                    <div class="content-body" id="main-content-body"><?php the_content(); ?></div>
                    <div class="content-gradient"></div>
                </div>
                <div class="content-action"><button id="btn-expand-content" class="btn-view-more-content">Xem thêm <i
                            class="fas fa-caret-down"></i></button></div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col col-12">
            <div class="white-box prod-review-box" id="prod-reviews">
                <h3 class="section-title">Đánh giá & Nhận xét <?php the_title(); ?></h3>
                <?php $rating_count = $product->get_rating_count();
                $average = $product->get_average_rating(); ?>
                <div class="fpt-rating-overview">
                    <div class="ro-left">
                        <div class="score"><?php echo number_format($average, 1); ?>/5</div>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++) echo '<i class="fas fa-star ' . ($i <= round($average) ? 'filled' : '') . '"></i>'; ?>
                        </div>
                        <div class="count-text"><strong><?php echo $rating_count; ?></strong> đánh giá</div>
                    </div>
                    <div class="ro-middle">
                        <div class="filter-stars-wrap">
                            <span class="filter-star-item active" data-star="all">Tất cả</span>
                            <span class="filter-star-item" data-star="5">5 Sao</span>
                            <span class="filter-star-item" data-star="4">4 Sao</span>
                            <span class="filter-star-item" data-star="3">3 Sao</span>
                            <span class="filter-star-item" data-star="2">2 Sao</span>
                            <span class="filter-star-item" data-star="1">1 Sao</span>
                        </div>
                    </div>
                    <div class="ro-right">
                        <button type="button" class="btn-write-review" id="btn-open-review">
                            <i class="fas fa-pencil-alt"></i> Gửi đánh giá
                        </button>
                    </div>
                </div>
                <div id="relive-reviews-container" data-product-id="<?php echo $product->get_id(); ?>">
                    <div class="loading-reviews" style="text-align:center; padding:20px; display:none;"><i
                            class="fas fa-spinner fa-spin"></i> Đang tải đánh giá...</div>
                    <div class="reviews-list-inner"></div>
                    <div class="reviews-pagination" style="text-align:center; margin-top:20px;"></div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php if (!empty($spec_groups)): ?>
<div class="specs-popup-overlay" id="specs-popup">
    <div class="specs-popup-content fpt-popup-style">
        <div class="sp-header">
            <h3>Thông số kỹ thuật</h3><span class="sp-close" id="btn-close-specs"><i class="fas fa-times"></i></span>
        </div>
        <div class="sp-body">
            <div class="sp-feature-img"><?php if ($spec_feature_img): ?><img
                    src="<?php echo esc_url($spec_feature_img); ?>" alt="Cấu hình"><?php endif; ?></div>
            <div class="sp-nav-menu"><?php foreach ($spec_groups as $index => $group): ?><a
                    href="#spec-group-<?php echo $index; ?>"
                    class="sp-nav-item <?php echo $index === 0 ? 'active' : ''; ?>"><?php echo esc_html($group['group_name']); ?></a><?php endforeach; ?>
            </div>
            <div class="sp-list-container"><?php foreach ($spec_groups as $index => $group): ?><div
                    id="spec-group-<?php echo $index; ?>" class="sp-group-section">
                    <h4 class="sp-group-title"><?php echo esc_html($group['group_name']); ?></h4>
                    <table class="table-specs-full">
                        <?php if (!empty($group['group_items'])): foreach ($group['group_items'] as $item): $val = isset($item['spec_val']) ? $item['spec_val'] : ''; ?>
                        <tr>
                            <th><?php echo esc_html($item['label']); ?></th>
                            <td><?php echo esc_html($val); ?></td>
                        </tr><?php endforeach;
                                                    endif; ?>
                    </table>
                </div><?php endforeach; ?></div>
        </div>
    </div>
</div>
<?php endif; ?>