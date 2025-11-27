<?php
/**
 * Block: Slider Ảnh (Full Options + Hide Arrows Mobile)
 */

if ( empty( $args['data'] ) ) return;
$data = $args['data'];
$slides = isset( $data['slides'] ) ? $data['slides'] : array();
if ( empty( $slides ) ) return;

// Lấy các tùy chọn
$width_mode  = !empty( $data['width_mode'] ) ? $data['width_mode'] : 'container';
$height      = !empty( $data['height'] ) ? $data['height'] : '400';
$mt          = isset( $data['mt'] ) ? $data['mt'] : '0';
$mb          = isset( $data['mb'] ) ? $data['mb'] : '30';
$effect      = !empty( $data['effect'] ) ? $data['effect'] : 'slide';
$show_arrows = isset( $data['arrows'] ) ? $data['arrows'] : true;
$show_dots   = isset( $data['dots'] ) ? $data['dots'] : true;
$autoplay    = isset( $data['autoplay'] ) ? $data['autoplay'] : true;
$pagi_style  = !empty( $data['pagi_style'] ) ? $data['pagi_style'] : 'dots';

// MỚI: Lấy tùy chọn ẩn mũi tên mobile
$hide_arrow_mobile = isset( $data['arrows_hide_mobile'] ) ? $data['arrows_hide_mobile'] : true;
$mobile_arrow_class = $hide_arrow_mobile ? 'hide-arrows-mobile' : '';

$wrapper_class = ($width_mode == 'container') ? 'container' : 'container-fluid';
$wrapper_style = ($width_mode == 'full') ? 'padding: 0;' : '';

$rand_id = 'slider-' . rand(1000, 9999);
$thumb_id = $rand_id . '-thumbs';
?>

<section class="section slider-block"
    style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">

    <div class="<?php echo esc_attr($wrapper_class); ?>" style="<?php echo esc_attr($wrapper_style); ?>">

        <div class="slider-wrapper slider-style-<?php echo esc_attr($pagi_style); ?> <?php echo esc_attr($mobile_arrow_class); ?>"
            style="position: relative;">

            <div id="<?php echo $rand_id; ?>" class="swiper main-slider"
                style="border-radius: <?php echo ($width_mode == 'container') ? '8px' : '0'; ?>; overflow: hidden; position: relative;">
                <div class="swiper-wrapper">
                    <?php foreach ( $slides as $slide ) : 
                        $img_url = wp_get_attachment_image_url( $slide['image'], 'full' );
                        if ( ! $img_url ) $img_url = 'https://placehold.co/1200x400/e0e0e0/333?text=No+Image';
                    ?>
                    <div class="swiper-slide" style="height: <?php echo esc_attr($height); ?>px;">
                        <?php if ( ! empty( $slide['link'] ) ) : ?>
                        <a href="<?php echo esc_url( $slide['link'] ); ?>" class="slide-link-wrap">
                            <?php endif; ?>
                            <img src="<?php echo esc_url( $img_url ); ?>" class="slide-img-mobile" alt="Banner"
                                loading="lazy">
                            <div class="slide-bg" style="background-image: url('<?php echo esc_url( $img_url ); ?>');">
                            </div>
                            <?php if ( ! empty( $slide['link'] ) ) : ?>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if ( $show_dots ) : ?><div class="swiper-pagination"></div><?php endif; ?>

                <?php if ( $show_arrows ) : ?>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <?php endif; ?>
            </div>

            <?php if ( $pagi_style == 'thumbs_text' ) : ?>
            <div class="container-thumbs-absolute">
                <div id="<?php echo $thumb_id; ?>" class="swiper thumbs-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ( $slides as $slide ) : 
                                $title = !empty($slide['thumb_title']) ? $slide['thumb_title'] : 'Tiêu đề';
                                $desc = !empty($slide['thumb_desc']) ? $slide['thumb_desc'] : 'Mô tả ngắn';
                            ?>
                        <div class="swiper-slide thumb-item">
                            <div class="thumb-inner">
                                <span class="t-title"><?php echo esc_html($title); ?></span>
                                <span class="t-desc"><?php echo esc_html($desc); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        // 1. Thumbs Init
        var thumbsSwiper = null;
        <?php if ( $pagi_style == 'thumbs_text' ) : ?>
        thumbsSwiper = new Swiper('#<?php echo $thumb_id; ?>', {
            spaceBetween: 5,
            slidesPerView: 'auto',
            watchSlidesProgress: true,
            slideToClickedSlide: true,
            freeMode: true,
        });
        <?php endif; ?>

        // 2. Effect Config
        var effectConfig = {};
        <?php if($effect == 'creative'): ?>
        effectConfig = {
            limitProgress: 2,
            prev: {
                shadow: true,
                translate: ['-20%', 0, -1],
                opacity: 0.8
            },
            next: {
                translate: ['100%', 0, 0]
            },
        };
        <?php endif; ?>

        // 3. Main Init
        new Swiper('#<?php echo $rand_id; ?>', {
            loop: true,
            speed: 800,
            effect: '<?php echo esc_js($effect); ?>',
            creativeEffect: effectConfig,
            fadeEffect: {
                crossFade: true
            },
            watchSlidesProgress: true,
            autoplay: <?php echo $autoplay ? '{delay: 4000, disableOnInteraction: false}' : 'false'; ?>,
            pagination: {
                el: '#<?php echo $rand_id; ?> .swiper-pagination',
                clickable: true
            },
            navigation: {
                nextEl: '#<?php echo $rand_id; ?> .swiper-button-next',
                prevEl: '#<?php echo $rand_id; ?> .swiper-button-prev'
            },
            <?php if ( $pagi_style == 'thumbs_text' ) : ?>thumbs: {
                swiper: thumbsSwiper
            }
            <?php endif; ?>
        });
    }
});
</script>