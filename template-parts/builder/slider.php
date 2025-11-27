<?php
/**
 * Block: Slider Ảnh (Fixed Layout)
 */

if ( empty( $args['data'] ) ) return;
$data = $args['data'];
$slides = isset( $data['slides'] ) ? $data['slides'] : array();
if ( empty( $slides ) ) return;

// Lấy các tùy chọn
$height      = !empty( $data['height'] ) ? $data['height'] : '400';
$mt          = isset( $data['mt'] ) ? $data['mt'] : '0';
$mb          = isset( $data['mb'] ) ? $data['mb'] : '30';
$effect      = !empty( $data['effect'] ) ? $data['effect'] : 'slide';
$show_arrows = isset( $data['arrows'] ) ? $data['arrows'] : true;
$show_dots   = isset( $data['dots'] ) ? $data['dots'] : true;
$autoplay    = isset( $data['autoplay'] ) ? $data['autoplay'] : true;
$pagi_style  = !empty( $data['pagi_style'] ) ? $data['pagi_style'] : 'dots';

$rand_id = 'slider-' . rand(1000, 9999);
?>

<div class="slider-wrapper slider-style-<?php echo esc_attr($pagi_style); ?>" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px; position: relative;">
        
    <div id="<?php echo $rand_id; ?>" class="swiper main-slider" style="border-radius: 8px; overflow: hidden; position: relative;">
        <div class="swiper-wrapper">
            <?php foreach ( $slides as $slide ) : 
                $img_id = $slide['image'];
                $img_url = wp_get_attachment_image_url( $img_id, 'full' );
                if ( ! $img_url ) $img_url = 'https://placehold.co/1200x400/e0e0e0/333?text=No+Image';
            ?>
                <div class="swiper-slide" style="height: <?php echo esc_attr($height); ?>px;">
                    <?php if ( ! empty( $slide['link'] ) ) : ?>
                        <a href="<?php echo esc_url( $slide['link'] ); ?>" style="display: block; width: 100%; height: 100%;">
                    <?php endif; ?>
                        
                        <div class="slide-bg" style="background-image: url('<?php echo esc_url( $img_url ); ?>'); width: 100%; height: 100%; background-size: cover; background-position: center;"></div>
                    
                    <?php if ( ! empty( $slide['link'] ) ) : ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ( $show_dots ) : ?>
            <div class="swiper-pagination"></div>
        <?php endif; ?>

        <?php if ( $show_arrows ) : ?>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        <?php endif; ?>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swiper !== 'undefined') {
        new Swiper('#<?php echo $rand_id; ?>', {
            loop: true,
            speed: 800,
            effect: '<?php echo esc_js($effect); ?>',
            <?php if($effect == 'creative'): ?>
            creativeEffect: { prev: { shadow: true, translate: [0, 0, -400] }, next: { translate: ['100%', 0, 0] } },
            <?php endif; ?>
            autoplay: <?php echo $autoplay ? '{delay: 4000, disableOnInteraction: false}' : 'false'; ?>,
            pagination: { el: '#<?php echo $rand_id; ?> .swiper-pagination', clickable: true },
            navigation: { nextEl: '#<?php echo $rand_id; ?> .swiper-button-next', prevEl: '#<?php echo $rand_id; ?> .swiper-button-prev' },
        });
    }
});
</script>