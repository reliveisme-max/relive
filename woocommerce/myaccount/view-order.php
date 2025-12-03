<?php

/**
 * View Order - FPT Style (Custom Template)
 * File: woocommerce/myaccount/view-order.php
 */

defined('ABSPATH') || exit;

$order = wc_get_order($order_id);

if (! $order) {
    return;
}
?>

<div class="fpt-view-order-page">

    <div class="vo-header"
        style="background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div class="vo-title">
            Chi tiết đơn hàng <strong style="color: #288ad6;">#<?php echo $order->get_order_number(); ?></strong>
            <span style="color: #777; font-size: 13px; margin-left: 5px;">- Ngày
                <?php echo wc_format_datetime($order->get_date_created()); ?></span>
        </div>
        <div class="vo-status">
            <span
                class="fpt-order-status status-<?php echo esc_attr($order->get_status()); ?>"><?php echo wc_get_order_status_name($order->get_status()); ?></span>
        </div>
    </div>

    <div class="row">

        <div class="col col-8 col-md-12">

            <div class="ty-box-section">
                <div class="ty-box-header">Sản phẩm</div>
                <div class="ty-prod-list">
                    <?php
                    $items = $order->get_items();
                    $is_open_group = false;
                    $parent_name = '';

                    foreach ($items as $item_id => $item) {
                        $product = $item->get_product();
                        $is_addon = $item->get_meta('relive_is_addon');

                        if (!$is_addon) {
                            if ($is_open_group) {
                                echo '</div></div>';
                                $is_open_group = false;
                            }
                            $parent_name = $item->get_name();
                    ?>
                    <div class="ty-prod-item ty-main">
                        <div class="ty-img">
                            <?php echo $product ? $product->get_image('thumbnail') : ''; ?>
                        </div>
                        <div class="ty-info">
                            <div class="ty-name">
                                <a href="<?php echo $product ? $product->get_permalink() : '#'; ?>"
                                    style="text-decoration: none; color: #333;">
                                    <?php echo $item->get_name(); ?>
                                </a>
                            </div>
                            <div class="ty-meta">
                                <?php echo wc_display_item_meta($item); ?>
                            </div>
                        </div>
                        <div class="ty-price-qty">
                            <div class="ty-price"><?php echo wc_price($order->get_item_total($item)); ?></div>
                            <div class="ty-qty">x<?php echo $item->get_quantity(); ?></div>
                        </div>
                    </div>
                    <?php
                        } else {
                            if (!$is_open_group) {
                                echo '<div class="ty-combo-box"><div class="ty-cb-header"><i class="fas fa-fire"></i> Gói Phụ kiện Chuẩn - ' . $parent_name . '</div><div class="ty-cb-body">';
                                $is_open_group = true;
                            }
                        ?>
                    <div class="ty-prod-item ty-addon">
                        <div class="ty-img"><?php echo $product ? $product->get_image('thumbnail') : ''; ?></div>
                        <div class="ty-info">
                            <div class="ty-name"><?php echo $item->get_name(); ?></div>
                        </div>
                        <div class="ty-price-qty">
                            <div class="ty-price"><?php echo wc_price($order->get_item_total($item)); ?></div>
                        </div>
                    </div>
                    <?php
                        }
                    }
                    if ($is_open_group) echo '</div></div>';
                    ?>
                </div>
            </div>

            <div class="ty-box-section">
                <div class="ty-box-header">Thông tin nhận hàng</div>
                <div class="ty-box-content">
                    <div style="margin-bottom: 8px;">
                        <strong><?php echo $order->get_formatted_billing_full_name(); ?></strong>
                    </div>
                    <div style="margin-bottom: 8px;">
                        <i class="fas fa-phone-alt" style="width: 20px; color: #999;"></i>
                        <?php echo $order->get_billing_phone(); ?>
                    </div>
                    <div>
                        <i class="fas fa-map-marker-alt" style="width: 20px; color: #999;"></i>
                        <?php echo $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address(); ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="col col-4 col-md-12">
            <div class="ty-sidebar-box" style="position: static;">
                <div class="ty-sb-header">Thanh toán</div>

                <div class="ty-sb-row">
                    <span>Tạm tính</span>
                    <strong><?php echo wc_price($order->get_subtotal()); ?></strong>
                </div>

                <?php if ($order->get_total_discount() > 0): ?>
                <div class="ty-sb-row">
                    <span>Giảm giá</span>
                    <strong style="color: #28a745;">-<?php echo wc_price($order->get_total_discount()); ?></strong>
                </div>
                <?php endif; ?>

                <?php if ($order->get_shipping_total() > 0): ?>
                <div class="ty-sb-row">
                    <span>Phí vận chuyển</span>
                    <strong><?php echo wc_price($order->get_shipping_total()); ?></strong>
                </div>
                <?php endif; ?>

                <div class="ty-sb-total">
                    <span>Tổng cộng</span>
                    <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                </div>

                <div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-radius: 6px; font-size: 13px;">
                    <div style="margin-bottom: 5px; font-weight: 600; color: #333;">Phương thức thanh toán:</div>
                    <div style="color: #555;"><?php echo $order->get_payment_method_title(); ?></div>
                </div>

                <?php if ($order->needs_payment()) : ?>
                <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="btn-home"
                    style="margin-top: 15px;">Thanh toán ngay</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>