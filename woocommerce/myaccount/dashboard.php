<?php

/**
 * My Account Dashboard - FPT Style (Icon Grid)
 * File: woocommerce/myaccount/dashboard.php
 */
defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
?>

<div class="fpt-dashboard-content">
    <p class="hello-text">
        Xin chào <strong><?php echo esc_html($current_user->display_name); ?></strong>
        <span style="color:#999; font-size:13px;">(Không phải <?php echo esc_html($current_user->display_name); ?>? <a
                href="<?php echo esc_url(wc_logout_url()); ?>" style="color:#cb1c22;">Đăng xuất</a>)</span>
    </p>

    <p style="margin-bottom: 30px; color: #555;">
        Từ bảng điều khiển tài khoản, bạn có thể xem <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>"
            style="color:#288ad6;">các đơn hàng gần đây</a>, quản lý <a
            href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" style="color:#288ad6;">địa chỉ giao
            hàng</a> và <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" style="color:#288ad6;">sửa
            mật khẩu, thông tin tài khoản</a>.
    </p>

    <div class="dashboard-grid-links">

        <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="dash-box-item">
            <div class="db-icon"><i class="fas fa-shopping-bag"></i></div>
            <div class="db-info">
                <h3>Đơn hàng</h3>
                <span>Xem lịch sử mua hàng</span>
            </div>
        </a>

        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address')); ?>" class="dash-box-item">
            <div class="db-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="db-info">
                <h3>Địa chỉ</h3>
                <span>Sửa địa chỉ nhận hàng</span>
            </div>
        </a>

        <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" class="dash-box-item">
            <div class="db-icon"><i class="fas fa-user-cog"></i></div>
            <div class="db-info">
                <h3>Tài khoản</h3>
                <span>Sửa thông tin cá nhân</span>
            </div>
        </a>

        <a href="<?php echo esc_url(wc_logout_url()); ?>" class="dash-box-item">
            <div class="db-icon"><i class="fas fa-sign-out-alt"></i></div>
            <div class="db-info">
                <h3>Đăng xuất</h3>
                <span>Thoát khỏi hệ thống</span>
            </div>
        </a>

    </div>
</div>