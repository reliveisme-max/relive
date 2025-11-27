<?php

/**
 * Block: Danh mục (Free Scroll Mode)
 */

if (empty($args['data'])) return;
$data = $args['data'];
$cats = isset($data['selected_cats']) ? $data['selected_cats'] : array();

if (empty($cats)) return;

$title    = !empty($data['title']) ? $data['title'] : '';
$rows     = !empty($data['cat_rows']) ? intval($data['cat_rows']) : 1;

$col_desk = !empty($data['col_desk']) ? intval($data['col_desk']) : 8;
$col_tab  = !empty($data['col_tab']) ? intval($data['col_tab']) : 6;
$col_mob  = !empty($data['col_mob']) ? intval($data['col_mob']) : 4;

$mt       = isset($data['mt']) ? $data['mt'] : '0';
$mb       = isset($data['mb']) ? $data['mb'] : '30';

$rand_id = 'cat-swiper-' . rand(1000, 9999);
?>

<section class="section cat-section"
    style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">
    <div class="container">
        <div class="white-box">

            <?php if ($title): ?>
                <h3 class="section-title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>

            <div class="cat-swiper-wrapper" style="position: relative;">
                <div id="<?php echo $rand_id; ?>" class="swiper cat-slider">
                    <div class="swiper-wrapper">
                        <?php foreach ($cats as $c):
                            $term = get_term($c['id']);
                            if (! $term || is_wp_error($term)) continue;
                            $thumb_id = get_term_meta($c['id'], 'thumbnail_id', true);
                            $img = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                            if (!$img) $img = 'https://placehold.co/100x100/f1f1f1/999?text=Icon';
                        ?>
                            <div class="swiper-slide">
                                <a href="<?php echo get_term_link($term); ?>" class="cat-item-box">
                                    <div class="cat-img-wrap">
                                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($term->name); ?>">
                                    </div>
                                    <span class="cat-name"><?php echo esc_html($term->name); ?></span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="swiper-pagination cat-pagination"></div>
                </div>

                <div class="swiper-button-next cat-next"></div>
                <div class="swiper-button-prev cat-prev"></div>
            </div>

        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swiper !== 'undefined') {
            new Swiper('#<?php echo $rand_id; ?>', {
                slidesPerView: <?php echo $col_mob; ?>,
                spaceBetween: 10,

                // --- KÍCH HOẠT FREE SCROLL (Lướt tự do) ---
                freeMode: {
                    enabled: true,
                    sticky: false, // false = lướt đến đâu dừng đó, true = dính vào slide gần nhất
                    momentumRatio: 0.5, // Quán tính (số càng cao lướt càng trôi xa)
                },
                speed: 500, // Tốc độ mượt mà hơn

                <?php if ($rows > 1): ?>
                    grid: {
                        rows: 2,
                        fill: 'row'
                    },
                <?php endif; ?>

                pagination: {
                    el: '#<?php echo $rand_id; ?> .cat-pagination',
                    clickable: true,
                },

                navigation: {
                    nextEl: '#<?php echo $rand_id; ?> + .cat-next',
                    prevEl: '#<?php echo $rand_id; ?> ~ .cat-prev',
                },

                breakpoints: {
                    768: {
                        slidesPerView: <?php echo $col_tab; ?>,
                        spaceBetween: 15,
                        freeMode: false, // Trên Tablet/PC nên tắt Free Mode để bấm nút Next/Prev chuẩn xác hơn
                        <?php if ($rows > 1): ?>grid: {
                            rows: 2,
                            fill: 'row'
                        },
                    <?php endif; ?>
                    },
                    1024: {
                        slidesPerView: <?php echo $col_desk; ?>,
                        spaceBetween: 20,
                        freeMode: false, // PC tắt Free Mode
                        <?php if ($rows > 1): ?>grid: {
                            rows: 2,
                            fill: 'row'
                        },
                    <?php endif; ?>
                    }
                }
            });
        }
    });
</script>