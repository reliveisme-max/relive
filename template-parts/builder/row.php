<?php
$data = $args['data'];
$container = $data['container'] ? 'container' : 'container-fluid';
$bg = $data['bg_color'] ? 'background-color:'.$data['bg_color'].';' : '';
$mt = isset($data['mt']) ? $data['mt'] : '0';
$mb = isset($data['mb']) ? $data['mb'] : '30';
?>
<section class="section row-block" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px; <?php echo $bg; ?>">
    <div class="<?php echo $container; ?>">
        <div class="row">
            <?php foreach($data['columns'] as $col): ?>
                <div class="col col-<?php echo esc_attr($col['width']); ?>">
                    <div class="col-inner" style="display: flex; flex-direction: column; gap: 20px;">
                        <?php 
                        if(!empty($col['col_content'])) {
                            foreach($col['col_content'] as $block) {
                                // Truyền tham số margin top/bottom cho block con
                                $type = $block['_type'];
                                get_template_part('template-parts/builder/'.$type, null, array('data' => $block));
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>