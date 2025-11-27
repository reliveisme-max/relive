<?php
$data = $args['data'];
$cats = $data['selected_cats'];
$mt = isset($data['mt']) ? $data['mt'] : '0';
$mb = isset($data['mb']) ? $data['mb'] : '30';
?>
<?php if ( ! empty($cats) ): ?>
<section class="section" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">
    <div class="container">
        <div class="white-box">
            <?php if($data['title']): ?><h3 class="section-title"><?php echo esc_html($data['title']); ?></h3><?php endif; ?>
            <div class="cat-grid">
                <?php foreach($cats as $c): 
                    $term = get_term($c['id']);
                    $thumb_id = get_term_meta($c['id'], 'thumbnail_id', true);
                    $img = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                ?>
                    <a href="<?php echo get_term_link($term); ?>" class="cat-item">
                        <div class="cat-img"><img src="<?php echo $img ? $img : 'placeholder.png'; ?>" alt=""></div>
                        <span class="cat-title"><?php echo $term->name; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>