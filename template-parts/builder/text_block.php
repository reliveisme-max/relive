<?php
$data = $args['data'];
$mt = isset($data['mt']) ? $data['mt'] : '0';
$mb = isset($data['mb']) ? $data['mb'] : '30';
?>
<section class="section" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">
    <div class="container">
        <div class="white-box">
            <div class="entry-content">
                <?php echo apply_filters( 'the_content', $data['content'] ); ?>
            </div>
        </div>
    </div>
</section>