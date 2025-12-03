<?php

/**
 * My Account Orders - FPT Style (Custom Table & Badges)
 * File: woocommerce/myaccount/orders.php
 */
defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders);

if ($has_orders) : ?>

<div class="fpt-orders-table-wrap">
    <table
        class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
        <thead>
            <tr>
                <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
                <th
                    class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr($column_id); ?>">
                    <span class="nobr"><?php echo esc_html($column_name); ?></span>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php
                foreach ($customer_orders->orders as $customer_order) {
                    $order      = wc_get_order($customer_order);
                    $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                ?>
            <tr
                class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($order->get_status()); ?> order">
                <?php foreach (wc_get_account_orders_columns() as $column_id => $column_name) : ?>
                <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr($column_id); ?>"
                    data-title="<?php echo esc_attr($column_name); ?>">

                    <?php if (has_action('woocommerce_my_account_my_orders_column_' . $column_id)) : ?>
                    <?php do_action('woocommerce_my_account_my_orders_column_' . $column_id, $order); ?>

                    <?php elseif ('order-number' === $column_id) : ?>
                    <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="fpt-order-number">
                        #<?php echo $order->get_order_number(); ?>
                    </a>

                    <?php elseif ('order-date' === $column_id) : ?>
                    <time datetime="<?php echo esc_attr($order->get_date_created()->date('c')); ?>"
                        style="color:#777; font-size:13px;">
                        <?php echo esc_html(wc_format_datetime($order->get_date_created())); ?>
                    </time>

                    <?php elseif ('order-status' === $column_id) : ?>
                    <?php
                                    // Tạo Badge màu sắc theo trạng thái
                                    $status = $order->get_status(); // pending, processing, on-hold, completed...
                                    $status_name = wc_get_order_status_name($status);
                                    ?>
                    <span class="fpt-order-status status-<?php echo esc_attr($status); ?>">
                        <?php echo esc_html($status_name); ?>
                    </span>

                    <?php elseif ('order-total' === $column_id) : ?>
                    <span style="color:#cb1c22; font-weight:700;">
                        <?php echo $order->get_formatted_order_total(); ?>
                    </span>
                    <div style="font-size:11px; color:#999;">
                        <?php echo $item_count; ?> sản phẩm
                    </div>

                    <?php elseif ('order-actions' === $column_id) : ?>
                    <?php
                                    $actions = wc_get_account_orders_actions($order);
                                    if (! empty($actions)) {
                                        foreach ($actions as $key => $action) {
                                            echo '<a href="' . esc_url($action['url']) . '" class="btn-order-action ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a>';
                                        }
                                    }
                                    ?>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php
                }
                ?>
        </tbody>
    </table>
</div>

<?php do_action('woocommerce_before_account_orders_pagination'); ?>

<?php if (1 < $customer_orders->max_num_pages) : ?>
<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
    <?php if (1 !== $current_page) : ?>
    <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button"
        href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">Trước</a>
    <?php endif; ?>

    <?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
    <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button"
        href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">Sau</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else : ?>

<div style="text-align:center; padding: 50px 20px;">
    <img src="https://cdn2.cellphones.com.vn/x,webp/media/wysiwyg/Search-Empty.png" alt="Empty"
        style="width:120px; margin-bottom:15px; margin-left:auto; margin-right:auto;">
    <p style="color:#555; font-size:15px; margin-bottom:20px;">Bạn chưa có đơn hàng nào.</p>
    <a class="button"
        href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>"
        style="background:#cb1c22; color:#fff; padding:10px 25px; border-radius:4px; text-decoration:none;">
        Bắt đầu mua sắm
    </a>
</div>

<?php endif; ?>