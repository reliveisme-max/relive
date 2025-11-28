<?php

/**
 * Template: Nội dung chi tiết sản phẩm (FPT Shop Style - Full Features)
 * File này được gọi từ single-product.php
 */
defined('ABSPATH') || exit;
global $product;

// --- 1. LẤY DỮ LIỆU TỪ CARBON FIELDS & WOOCOMMERCE ---
$video_url      = carbon_get_the_post_meta('prod_video');
$specs_data     = carbon_get_the_post_meta('product_specs_table'); // Bảng thông số nhập tay
$box_images     = carbon_get_the_post_meta('box_images'); // Ảnh mở hộp
$attachment_ids = $product->get_gallery_image_ids();
$main_image_id  = $product->get_image_id();

// --- 2. XỬ LÝ THÔNG SỐ NỔI BẬT (HIỆN DƯỚI ẢNH) ---
$highlight_specs = array();
if (! empty($specs_data)) {
    foreach ($specs_data as $row) {
        if (! empty($row['is_highlight'])) {
            $highlight_specs[] = $row;
        }
    }
    // Nếu không chọn cái nào nổi bật thì lấy 5 dòng đầu tiên
    if (empty($highlight_specs)) {
        $highlight_specs = array_slice($specs_data, 0, 5);
    }
}
?>

<div class="container fpt-product-detail" style="padding-top: 0;">

    <div class="prod-header-top">
        <h1 class="product_title entry-title"><?php the_title(); ?></h1>
        <div class="prod-rating">
            <?php echo wc_get_rating_html($product->get_average_rating()); ?>
            <span class="count-review">(<?php echo $product->get_review_count(); ?> đánh giá)</span>
        </div>
    </div>

    <div class="row">

        <div class="col col-7 col-md-12">

            <div class="product-gallery-wrap">
                <div class="swiper product-main-slider">
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <?php echo wp_get_attachment_image($main_image_id, 'full'); ?>

                            <?php if ($video_url): ?>
                            <a href="<?php echo esc_url($video_url); ?>" class="btn-play-video" target="_blank">
                                <i class="fas fa-play"></i> Xem video
                            </a>
                            <?php endif; ?>
                        </div>

                        <?php if ($attachment_ids) : foreach ($attachment_ids as $attachment_id) { ?>
                        <div class="swiper-slide">
                            <?php echo wp_get_attachment_image($attachment_id, 'full'); ?>
                        </div>
                        <?php }
                        endif; ?>

                    </div>
                    <div class="swiper-button-next p-next"></div>
                    <div class="swiper-button-prev p-prev"></div>
                </div>

                <div class="gallery-thumbs-nav-fpt">

                    <div class="g-item active" data-slide-index="0">
                        <div class="g-icon"><i class="fas fa-star"></i></div>
                        <span>Nổi bật</span>
                    </div>

                    <?php if ($video_url): ?>
                    <a href="<?php echo esc_url($video_url); ?>" target="_blank" class="g-item">
                        <div class="g-icon"><i class="fas fa-play-circle"></i></div>
                        <span>Video</span>
                    </a>
                    <?php endif; ?>

                    <?php if (count($attachment_ids) > 0): ?>
                    <div class="g-item" data-slide-index="1">
                        <div class="g-icon"><i class="fas fa-box-open"></i></div>
                        <span>Mở hộp</span>
                    </div>
                    <?php endif; ?>

                    <?php if (count($attachment_ids) > 2): ?>
                    <div class="g-item" data-slide-index="<?php echo count($attachment_ids); ?>">
                        <div class="g-icon"><i class="fas fa-camera"></i></div>
                        <span>Thực tế</span>
                    </div>
                    <?php endif; ?>

                    <div class="g-item" data-slide-index="0">
                        <div class="g-icon"><i class="fas fa-images"></i></div>
                        <span>Tất cả</span>
                    </div>

                </div>
            </div>

            <?php if (! empty($highlight_specs)) : ?>
            <div class="prod-specs-highlight">
                <h3 class="highlight-title">Thông số nổi bật</h3>
                <ul class="specs-list-ul">
                    <?php foreach ($highlight_specs as $spec) : ?>
                    <li>
                        <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                        <span class="s-label"><?php echo esc_html($spec['spec_label']); ?>:</span>
                        <strong><?php echo esc_html($spec['spec_value']); ?></strong>
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
                    <div class="p-text">Hư gì đổi nấy <b>12 tháng</b> tại 3000 siêu thị toàn quốc (miễn phí tháng đầu)
                    </div>
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
                <?php do_action('woocommerce_single_product_summary'); ?>
            </div>

            <div class="fpt-promo-box">
                <div class="promo-header"><i class="fas fa-gift"></i> ƯU ĐÃI THÊM</div>
                <div class="promo-content">
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Giảm thêm 5% khi mua cùng Apple Watch</li>
                        <li><i class="fas fa-check-circle"></i> Thu cũ đổi mới trợ giá đến 2 triệu</li>
                        <li><i class="fas fa-check-circle"></i> Cơ hội trúng 10 xe Honda Vision</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

    <div class="row" style="margin-top: 30px;">

        <div class="col col-8 col-md-12">
            <div class="white-box prod-content-box">
                <?php the_content(); ?>
            </div>
        </div>

        <div class="col col-4 col-md-12">
            <?php if (!empty($specs_data)): ?>
            <div class="white-box full-specs-box">
                <h3 class="section-title">Thông số kỹ thuật</h3>
                <table class="table-specs-sidebar">
                    <?php
                        // Chỉ hiện 8 dòng đầu tiên
                        $preview_specs = array_slice($specs_data, 0, 8);
                        foreach ($preview_specs as $row):
                        ?>
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

<?php if (!empty($specs_data)): ?>
<div class="specs-popup-overlay" id="specs-popup">
    <div class="specs-popup-content">
        <div class="sp-header">
            <h3>Thông số kỹ thuật</h3>
            <span class="sp-close" id="btn-close-specs"><i class="fas fa-times"></i></span>
        </div>
        <div class="sp-body">
            <div class="sp-prod-info">
                <?php echo wp_get_attachment_image($main_image_id, 'thumbnail'); ?>
                <strong><?php the_title(); ?></strong>
            </div>

            <table class="table-specs-full">
                <?php foreach ($specs_data as $row): ?>
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