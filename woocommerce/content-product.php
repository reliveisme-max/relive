<?php

/**
 * Template: Giao diện 1 ô sản phẩm (Card Product - Clean)
 */
defined('ABSPATH') || exit;
global $product;

if (empty($product) || ! $product->is_visible()) return;
?>

<div <?php wc_product_class('product-card-inner', $product); ?>>

    <div class="product-image-wrap">
        <a href="<?php the_permalink(); ?>">
            <?php echo $product->get_image('woocommerce_thumbnail'); ?>
        </a>

        <?php if ($product->is_on_sale()) :
            $regular = $product->get_regular_price();
            $sale    = $product->get_sale_price();
            if ($regular > 0) :
                $percent = round((($regular - $sale) / $regular) * 100);
        ?>
        <span class="badge-sale">-<?php echo $percent; ?>%</span>
        <?php endif;
        endif; ?>
    </div>

    <div class="product-info">
        <h3 class="woocommerce-loop-product__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="price-wrap">
            <?php echo $product->get_price_html(); ?>
        </div>

        <?php
        // Nút mua hàng/xem chi tiết mặc định
        echo apply_filters(
            'woocommerce_loop_add_to_cart_link',
            sprintf(
                '<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
                esc_url($product->add_to_cart_url()),
                esc_attr(isset($args['class']) ? $args['class'] : 'button add_to_cart_button'),
                isset($args['attributes']) ? wc_implode_html_attributes($args['attributes']) : '',
                esc_html($product->add_to_cart_text())
            ),
            $product,
            $args
        );
        ?>
    </div>

</div>