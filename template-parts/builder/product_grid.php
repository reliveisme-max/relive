<?php
$data = $args['data'];
$cols = $data['col'] ? $data['col'] : 4;
$limit = $data['limit'] ? $data['limit'] : 8;
$filter_type = isset($data['filter_type']) ? $data['filter_type'] : 'auto';
$mt = isset($data['mt']) ? $data['mt'] : '0';
$mb = isset($data['mb']) ? $data['mb'] : '30';

$args_query = array('post_type' => 'product', 'posts_per_page' => $limit, 'status' => 'publish', 'ignore_sticky_posts' => 1);

if ($filter_type == 'cat' && !empty($data['selected_cats'])) {
    $cat_ids = wp_list_pluck($data['selected_cats'], 'id');
    $args_query['tax_query'] = array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $cat_ids));
} elseif ($filter_type == 'manual' && !empty($data['selected_ids'])) {
    $post_ids = wp_list_pluck($data['selected_ids'], 'id');
    $args_query['post__in'] = $post_ids; $args_query['orderby'] = 'post__in';
} else {
    $auto_type = isset($data['auto_type']) ? $data['auto_type'] : 'recent';
    if ($auto_type == 'sale') { $args_query['meta_query'] = WC()->query->get_meta_query(); $args_query['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale()); }
    elseif ($auto_type == 'best') { $args_query['meta_key'] = 'total_sales'; $args_query['orderby'] = 'meta_value_num'; }
    else { $args_query['orderby'] = 'date'; $args_query['order'] = 'DESC'; }
}
$products = new WP_Query($args_query);
?>
<section class="section" style="margin-top: <?php echo esc_attr($mt); ?>px; margin-bottom: <?php echo esc_attr($mb); ?>px;">
    <div class="container">
        <div class="white-box">
            <?php if(!empty($data['title'])): ?><h3 class="section-title"><?php echo esc_html($data['title']); ?></h3><?php endif; ?>
            <?php if($products->have_posts()): ?>
                <div class="prod-grid prod-grid-<?php echo intval($cols); ?>">
                    <?php while($products->have_posts()): $products->the_post(); ?>
                        <div class="prod-item"><?php wc_get_template_part('content', 'product'); ?></div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?><p style="text-align: center; color: #999; padding: 20px;">Không tìm thấy sản phẩm.</p><?php endif; wp_reset_postdata(); ?>
        </div>
    </div>
</section>