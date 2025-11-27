<?php
$data = $args['data'];
$img = wp_get_attachment_image_url($data['bg_image'], 'full');
$height = $data['height'] != 'auto' ? 'height:'.$data['height'] : '';
$mt = isset($data['mt']) ? $data['mt'] : '0';
$mb = isset($data['mb']) ? $data['mb'] : '30';
?>
<?php if($img): ?>
<section class="section" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">
    <div class="container">
        <a href="<?php echo esc_url($data['link']); ?>" style="display: block; border-radius: 8px; overflow: hidden;">
            <img src="<?php echo esc_url($img); ?>" style="width: 100%; <?php echo $height; ?>; object-fit: cover;">
        </a>
    </div>
</section>
<?php endif; ?>