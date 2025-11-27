<?php
/**
 * Giao diện 1 ô sản phẩm (Card Product)
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Kiểm tra nếu sản phẩm rỗng hoặc không hiển thị thì bỏ qua
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}
?>

<div <?php wc_product_class( 'product-card-inner', $product ); ?>>
    
    <div class="product-image-wrap" style="position: relative; margin-bottom: 15px;">
        <a href="<?php the_permalink(); ?>">
            <?php 
            // 1. Hiển thị ảnh sản phẩm
            echo $product->get_image( 'woocommerce_thumbnail' ); 
            ?>
        </a>

        <?php if ( $product->is_on_sale() ) : ?>
            <?php 
            $regular = $product->get_regular_price();
            $sale = $product->get_sale_price();
            if ( $regular > 0 ) {
                $percent = round( ( ( $regular - $sale ) / $regular ) * 100 );
                echo '<span class="badge-sale" style="
                    position: absolute; top: 5px; right: 5px; 
                    background: #cb1c22; color: #fff; 
                    font-size: 11px; font-weight: bold; 
                    padding: 2px 6px; border-radius: 3px;
                ">-' . $percent . '%</span>';
            }
            ?>
        <?php endif; ?>
    </div>

    <div class="product-info">
        <h3 class="woocommerce-loop-product__title">
            <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: inherit;">
                <?php the_title(); ?>
            </a>
        </h3>

        <div class="price-wrap" style="margin-bottom: 10px;">
            <?php echo $product->get_price_html(); ?>
        </div>

        <?php
        echo apply_filters( 'woocommerce_loop_add_to_cart_link', 
            sprintf( '<a href="%s" data-quantity="1" class="%s" %s>%s</a>',
                esc_url( $product->add_to_cart_url() ),
                esc_attr( isset( $args['class'] ) ? $args['class'] : 'button add_to_cart_button' ),
                isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
                esc_html( $product->add_to_cart_text() )
            ),
        $product, $args );
        ?>
    </div>

</div>