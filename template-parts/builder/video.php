<?php
$data = $args['data'];
$mt = isset($data['mt']) ? $data['mt'] : '0';
$mb = isset($data['mb']) ? $data['mb'] : '30';
?>
<?php if ( $data['video_url'] ) : ?>
    <section class="section" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">
        <div class="container">
            <div class="video-wrap" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px;">
                <?php echo wp_oembed_get( $data['video_url'], array( 'width' => 1200 ) ); ?>
            </div>
        </div>
    </section>
    <style>.video-wrap iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }</style>
<?php endif; ?>