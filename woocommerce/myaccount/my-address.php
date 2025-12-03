<?php

/**
 * My Addresses - FPT Style (Single Address Mode)
 * File: woocommerce/myaccount/my-address.php
 */
defined('ABSPATH') || exit;

$customer_id = get_current_user_id();

// Mặc định chọn 'billing' làm địa chỉ chính để hiển thị
$name = 'billing';
$address = wc_get_account_formatted_address($name);
$col = 1;
?>

<p style="margin-bottom: 20px; color: #555; font-size: 14px;">
    <?php echo apply_filters('woocommerce_my_account_my_address_description', __('Địa chỉ bên dưới sẽ được sử dụng mặc định khi bạn đặt hàng.', 'woocommerce')); ?>
</p>

<div class="fpt-address-grid" style="grid-template-columns: 1fr;">

    <div class="fpt-address-box">
        <header class="fpt-addr-header">
            <div class="addr-title">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Sổ địa chỉ nhận hàng</h3>
            </div>
            <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', $name)); ?>" class="edit-addr-btn">
                <i class="fas fa-pencil-alt"></i> <?php echo $address ? 'Sửa địa chỉ' : 'Thêm địa chỉ'; ?>
            </a>
        </header>

        <div class="fpt-addr-body">
            <?php if ($address): ?>
            <address><?php echo wp_kses_post($address); ?></address>
            <?php else: ?>
            <p class="no-addr">Bạn chưa thiết lập địa chỉ nhận hàng.</p>
            <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', $name)); ?>" class="btn-add-addr">Thêm
                ngay</a>
            <?php endif; ?>
        </div>
    </div>

</div>