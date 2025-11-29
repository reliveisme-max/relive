<?php

/**
 * Template: Nội dung chi tiết sản phẩm (Full Features: FPT Layout + Bought Together + Review Ajax)
 */
defined('ABSPATH') || exit;
global $product;

// -----------------------------------------------------------------------------
// 1. LẤY DỮ LIỆU TỪ CARBON FIELDS & WOOCOMMERCE
// -----------------------------------------------------------------------------
$custom_featured_img = carbon_get_the_post_meta('prod_featured_image');
$video_url           = carbon_get_the_post_meta('prod_video');
$fpt_promos          = carbon_get_the_post_meta('fpt_promotions');
$box_images_ids      = carbon_get_the_post_meta('box_images');
$real_images_ids     = carbon_get_the_post_meta('real_images');
$attachment_ids      = $product->get_gallery_image_ids();
$main_image_id       = $product->get_image_id();

$spec_groups         = carbon_get_the_post_meta('fpt_specs_groups');
$spec_feature_img    = carbon_get_the_post_meta('spec_feature_image');

// Dữ liệu sản phẩm mua kèm (Mới)
$bought_together     = carbon_get_the_post_meta('bought_together_ids');

// Helper Icon cho thông số
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

// Tách thông số nổi bật (Lấy tối đa 6 cái)
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
                                data-relive-gallery="product-gallery" class="zoom-trigger">
                                <img src="<?php echo esc_url($custom_featured_img ?: wp_get_attachment_image_url($main_image_id, 'full')); ?>"
                                    alt="Nổi bật">
                            </a>
                        </div>

                        <?php if (!empty($video_url)):
                            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video_url, $match);
                            $yt_id = isset($match[1]) ? $match[1] : '';
                            if ($yt_id):
                        ?>
                        <div class="swiper-slide video-slide" data-type="video">
                            <iframe id="prod-video-iframe" width="100%" height="100%"
                                src="https://www.youtube.com/embed/<?php echo $yt_id; ?>?enablejsapi=1&rel=0"
                                frameborder="0" allowfullscreen></iframe>
                        </div>
                        <?php endif;
                        endif; ?>

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
                                class="zoom-trigger">
                                <?php echo wp_get_attachment_image($box_id, 'full'); ?>
                            </a>
                        </div>
                        <?php }
                        endif; ?>

                        <?php if ($real_images_ids) : foreach ($real_images_ids as $real_id) {
                                $real_full = wp_get_attachment_image_url($real_id, 'full'); ?>
                        <div class="swiper-slide" data-type="real">
                            <a href="<?php echo esc_url($real_full); ?>" data-relive-gallery="product-gallery"
                                class="zoom-trigger">
                                <?php echo wp_get_attachment_image($real_id, 'full'); ?>
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
                    <?php if ($has_video = !empty($video_url)): ?>
                    <div class="g-item g-item-video" data-slide-index="1">
                        <div class="g-icon"><i class="fas fa-play-circle"></i></div><span>Video</span>
                    </div>
                    <?php endif; ?>

                    <?php
                    $video_offset = $has_video ? 1 : 0;
                    $count_album = is_array($attachment_ids) ? count($attachment_ids) : 0;
                    $count_box = is_array($box_images_ids) ? count($box_images_ids) : 0;
                    $idx_box_start = 1 + $video_offset + $count_album;
                    $idx_real_start = $idx_box_start + $count_box;

                    if ($attachment_ids) {
                        $max_thumb = 6;
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

                    <?php if (!empty($box_images_ids)): ?>
                    <div class="g-item" data-slide-index="<?php echo $idx_box_start; ?>">
                        <div class="g-icon"><i class="fas fa-box-open"></i></div><span>Mở hộp</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($real_images_ids)): ?>
                    <div class="g-item" data-slide-index="<?php echo $idx_real_start; ?>">
                        <div class="g-icon"><i class="fas fa-camera"></i></div><span>Thực tế</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (! empty($highlight_specs)) : ?>
            <div class="fpt-specs-box">
                <div class="head-specs">
                    <h3 class="title">Thông số nổi bật</h3>
                    <a href="javascript:;" class="btn-view-more-specs" id="btn-open-specs">Xem tất cả thông số</a>
                </div>
                <div class="specs-grid-list">
                    <?php foreach ($highlight_specs as $spec) : ?>
                    <div class="spec-item">
                        <div class="spec-icon"><i class="<?php echo esc_attr($spec['icon']); ?>"></i></div>
                        <div class="spec-content">
                            <span class="spec-label"><?php echo esc_html($spec['label']); ?></span>
                            <strong class="spec-val"><?php echo esc_html($spec['val']); ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="fpt-policy-box">
                <div class="policy-head">
                    <h3>Chính sách sản phẩm</h3><a href="#">Tìm hiểu thêm</a>
                </div>
                <div class="policy-list">
                    <div class="p-item"><i class="fas fa-shield-alt"></i> <span>Hư gì đổi nấy <b>12 tháng</b> tại 3000
                            siêu thị toàn quốc (miễn phí tháng đầu)</span></div>
                    <div class="p-item"><i class="fas fa-shipping-fast"></i> <span>Giao hàng nhanh toàn quốc, miễn phí
                            vận chuyển</span></div>
                    <div class="p-item"><i class="fas fa-user-headset"></i> <span>Kỹ thuật viên hỗ trợ trực tuyến</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col col-5 col-md-12">
            <h1 class="product_title entry-title" style="font-size: 22px; font-weight: 700; margin-bottom: 10px;">
                <?php the_title(); ?>
            </h1>
            <div style="color: #777; font-size: 13px; margin-bottom: 15px;">
                Mã SP: <?php echo $product->get_sku() ? $product->get_sku() : 'N/A'; ?> <span
                    style="margin: 0 5px;">|</span>
                <?php $review_count = $product->get_review_count();
                $average = $product->get_average_rating();
                if ($review_count > 0) : ?>
                Đánh giá: <i class="fas fa-star" style="color: #f5a623;"></i>
                <b><?php echo number_format($average, 1); ?></b> <span style="color:#999;">(<?php echo $review_count; ?>
                    đánh giá)</span>
                <?php else : ?> Chưa có đánh giá <?php endif; ?>
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
                        <div class="reward-points">
                            <i class="fas fa-coins" style="color: #b78a28;"></i> +<span
                                class="points-val"><?php echo number_format($current_price / 10000); ?></span> Điểm
                            thưởng
                        </div>
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
                                    <?php if (!empty($item['link'])): ?>
                                    <a href="<?php echo esc_url($item['link']); ?>" target="_blank"
                                        style="color:#288ad6; font-size: 12px;">Xem chi tiết</a>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endforeach;
                                    endif; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                    <div class="corner-ribbon"></div><i class="fas fa-check ribbon-icon"></i>
                </div>
                <?php endif; ?>
            </div>

            <div class="fpt-bot-action-group">
                <button type="button" class="btn-fpt-cart action-trigger" data-type="add-to-cart">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    <span>Thêm giỏ</span>
                </button>
                <button type="button" class="btn-fpt-buy action-trigger" data-type="buy-now">
                    <strong>MUA NGAY</strong>
                    <span>(Giao tận nơi hoặc lấy tại cửa hàng)</span>
                </button>
                <button type="button" class="btn-fpt-installment">
                    <strong>TRẢ GÓP 0%</strong>
                    <span>(Duyệt hồ sơ trong 5 phút)</span>
                </button>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 30px;">

        <div class="col col-7 col-md-12">
            <div class="white-box prod-content-box" id="prod-description" style="height: 100%;">
                <h2 class="section-title">Đặc điểm nổi bật</h2>

                <div class="content-body-wrap">
                    <div class="content-body" id="main-content-body">
                        <?php the_content(); ?>
                    </div>
                    <div class="content-gradient"></div>
                </div>

                <div class="content-action">
                    <button id="btn-expand-content" class="btn-view-more-content">
                        Xem thêm <i class="fas fa-caret-down"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col col-5 col-md-12">
            <?php if (! empty($bought_together)) : ?>
            <div class="bought-together-box">
                <div class="bt-header">
                    <span class="bt-icon"><i class="fas fa-fire"></i></span>
                    <h3>Mua kèm giá sốc</h3>
                    <a href="#" class="bt-view-all">Xem tất cả <i class="fas fa-chevron-right"></i></a>
                </div>

                <form class="bt-list-form">
                    <?php
                        foreach ($bought_together as $assoc) :
                            $p_id = $assoc['id'];
                            $p    = wc_get_product($p_id);
                            if (! $p || ! $p->is_visible()) continue;

                            $price_html = $p->get_price_html();
                            $thumb      = $p->get_image('thumbnail');
                        ?>
                    <div class="bt-item">
                        <div class="bt-img">
                            <a href="<?php echo get_permalink($p_id); ?>" target="_blank"><?php echo $thumb; ?></a>
                        </div>
                        <div class="bt-info">
                            <a href="<?php echo get_permalink($p_id); ?>" target="_blank"
                                class="bt-title"><?php echo $p->get_name(); ?></a>
                            <div class="bt-price"><?php echo $price_html; ?></div>
                            <?php if ($p->is_on_sale()): ?>
                            <div class="bt-promo-text">Giảm thêm
                                <?php echo round(100 - ($p->get_price() / $p->get_regular_price() * 100)); ?>%</div>
                            <?php endif; ?>
                        </div>
                        <div class="bt-action">
                            <label class="bt-checkbox-btn">
                                <input type="checkbox" name="add_bought_together[]"
                                    value="<?php echo esc_attr($p_id); ?>">
                                <span class="btn-select-add">Chọn thêm <i class="fas fa-plus"></i></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </form>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col col-12">
            <div class="white-box prod-review-box" id="prod-reviews">
                <h3 class="section-title">Đánh giá & Nhận xét <?php the_title(); ?></h3>

                <?php
                $rating_count = $product->get_rating_count();
                $average      = $product->get_average_rating();
                ?>

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
                    <div class="loading-reviews" style="text-align:center; padding:20px; display:none;">
                        <i class="fas fa-spinner fa-spin"></i> Đang tải đánh giá...
                    </div>
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
            <div class="sp-feature-img">
                <?php if ($spec_feature_img): ?><img src="<?php echo esc_url($spec_feature_img); ?>" alt="Cấu hình">
                <?php else: ?><img
                    src="<?php echo esc_url($custom_featured_img ?: wp_get_attachment_image_url($main_image_id, 'full')); ?>"
                    alt="Cấu hình"><?php endif; ?>
            </div>
            <div class="sp-nav-menu">
                <?php foreach ($spec_groups as $index => $group): ?>
                <a href="#spec-group-<?php echo $index; ?>"
                    class="sp-nav-item <?php echo $index === 0 ? 'active' : ''; ?>"><?php echo esc_html($group['group_name']); ?></a>
                <?php endforeach; ?>
            </div>
            <div class="sp-list-container">
                <?php foreach ($spec_groups as $index => $group): ?>
                <div id="spec-group-<?php echo $index; ?>" class="sp-group-section">
                    <h4 class="sp-group-title"><?php echo esc_html($group['group_name']); ?></h4>
                    <table class="table-specs-full">
                        <?php if (!empty($group['group_items'])): foreach ($group['group_items'] as $item): $val = isset($item['spec_val']) ? $item['spec_val'] : ''; ?>
                        <tr>
                            <th><?php echo esc_html($item['label']); ?></th>
                            <td><?php echo esc_html($val); ?></td>
                        </tr>
                        <?php endforeach;
                                endif; ?>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>