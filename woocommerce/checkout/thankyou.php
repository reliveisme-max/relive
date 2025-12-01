<?php

/**
 * Thank You Page - FPT Style (Removed Inner Container)
 * File: woocommerce/checkout/thankyou.php
 */
defined('ABSPATH') || exit;

if (!$order) return;
?>

<div class="fpt-thankyou-page">

    <div class="ty-banner">
        <div class="banner-content">
            <img src="/wp-content/uploads/2025/12/mascot-order-success.png" alt="Đặt hàng thành công"
                style="max-height: 150px; margin: 0 auto; display: block;">
            <h2 style="color: #00483d; font-size: 26px; margin-top: 20px; font-weight: 700;">Đặt hàng thành công!</h2>
            <p style="color: #555; margin-bottom: 0; font-size: 16px;">Cảm ơn bạn đã tin tưởng và mua hàng tại hệ thống.
            </p>
        </div>
    </div>

    <div class="row">

        <div class="col col-8 col-md-12">

            <div class="ty-box-section">
                <div class="ty-box-header">Sản phẩm trong đơn (<?php echo $order->get_item_count(); ?>)</div>
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
                        <div class="ty-img"><?php echo $product->get_image('thumbnail'); ?></div>
                        <div class="ty-info">
                            <div class="ty-name"><?php echo $item->get_name(); ?></div>
                            <div class="ty-meta">
                                <?php echo wc_display_item_meta($item); ?>
                            </div>
                        </div>
                        <div class="ty-price-qty">
                            <div class="ty-price"><?php echo wc_price($order->get_item_total($item)); ?></div>
                            <?php if ($order->get_item_subtotal($item) > $order->get_item_total($item)): ?>
                            <del class="ty-sub"><?php echo wc_price($order->get_item_subtotal($item)); ?></del>
                            <?php endif; ?>
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
                        <div class="ty-img"><?php echo $product->get_image('thumbnail'); ?></div>
                        <div class="ty-info">
                            <div class="ty-name"><?php echo $item->get_name(); ?></div>
                        </div>
                        <div class="ty-price-qty">
                            <div class="ty-price"><?php echo wc_price($order->get_item_total($item)); ?></div>
                            <del class="ty-sub"><?php echo wc_price($order->get_item_subtotal($item)); ?></del>
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
                <div class="ty-box-header">Người đặt hàng</div>
                <div class="ty-box-content">
                    <strong><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></strong>
                    <div><?php echo $order->get_billing_phone(); ?></div>
                    <div><?php echo $order->get_billing_email(); ?></div>
                </div>
            </div>

            <div class="ty-box-section">
                <div class="ty-box-header">Địa chỉ nhận hàng</div>
                <div class="ty-box-content">
                    <div class="ty-address-row">
                        <span class="label">Nhận tại:</span>
                        <strong><?php echo $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address(); ?></strong>
                    </div>
                </div>
            </div>

            <div class="ty-box-section">
                <div class="ty-box-header">Phương thức thanh toán</div>
                <div class="ty-box-content" style="display: flex; align-items: center; gap: 10px;">
                    <?php
                    $method = $order->get_payment_method();
                    $method_title = $order->get_payment_method_title();

                    $icon_class = 'fa-money-bill-wave';
                    $icon_color = '#28a745';

                    if ($method == 'bacs') {
                        $icon_class = 'fa-university';
                        $icon_color = '#288ad6';
                    }
                    if ($method == 'relive_vnpay') {
                        $icon_class = 'fa-qrcode';
                        $icon_color = '#005baa';
                    }
                    ?>
                    <div class="ty-pm-icon"
                        style="background: <?php echo $icon_color; ?>; color: #fff; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="fas <?php echo $icon_class; ?>"></i>
                    </div>
                    <div class="ty-pm-text">
                        <strong><?php echo $method_title; ?></strong>
                        <div style="font-size: 12px; color: #777;">
                            <?php
                            if ($order->is_paid()) {
                                echo '<span style="color:#28a745;">Đã thanh toán thành công</span>';
                            } else {
                                echo 'Trạng thái: ' . wc_get_order_status_name($order->get_status());
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col col-4 col-md-12">
            <div class="ty-sidebar-box">
                <div class="ty-sb-header">Thông tin đơn hàng</div>
                <div class="ty-sb-row">
                    <span>Mã đơn hàng</span>
                    <strong>#<?php echo $order->get_order_number(); ?></strong>
                </div>
                <div class="ty-sb-row">
                    <span>Tổng tiền</span>
                    <strong><?php echo wc_price($order->get_subtotal()); ?></strong>
                </div>

                <?php if ($order->get_total_discount() > 0): ?>
                <div class="ty-sb-row">
                    <span>Tổng khuyến mãi</span>
                    <strong style="color: #28a745;">-<?php echo wc_price($order->get_total_discount()); ?></strong>
                </div>
                <?php endif; ?>

                <?php if ($order->get_shipping_total() > 0): ?>
                <div class="ty-sb-row">
                    <span>Phí vận chuyển</span>
                    <strong><?php echo wc_price($order->get_shipping_total()); ?></strong>
                </div>
                <?php endif; ?>

                <?php
                $label_text = 'Cần thanh toán';
                if ($method == 'relive_vnpay' || $order->is_paid()) {
                    $label_text = 'Đã thanh toán';
                }
                ?>

                <div class="ty-sb-total">
                    <span><?php echo $label_text; ?></span>
                    <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                </div>

                <a href="<?php echo home_url(); ?>" class="btn-home">Về trang chủ</a>
            </div>
        </div>

    </div>
</div>