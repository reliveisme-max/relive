<?php

/**
 * My Account Navigation - FPT Style (2 Blocks)
 * File: woocommerce/myaccount/navigation.php
 */

if (! defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
// Lấy số điện thoại từ billing (nếu có)
$user_phone = get_user_meta($current_user->ID, 'billing_phone', true);
?>

<div class="fpt-account-sidebar">

    <div class="white-box user-info-box" style="padding: 20px; margin-bottom: 20px;">

        <div class="user-profile-header" style="display: flex; align-items: center; margin-bottom: 15px;">
            <div class="user-avatar">
                <?php echo get_avatar($current_user->ID, 50); ?>
            </div>
            <div class="user-meta" style="margin-left: 12px; overflow: hidden;">
                <strong class="user-name" style="display: block; font-size: 15px; color: #333; line-height: 1.2;">
                    <?php echo esc_html($current_user->display_name); ?>
                </strong>
                <?php if ($user_phone): ?>
                <span class="user-phone"
                    style="font-size: 13px; color: #777;"><?php echo esc_html($user_phone); ?></span>
                <?php else: ?>
                <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>"
                    style="font-size: 12px; color: #288ad6;">Cập nhật SĐT</a>
                <?php endif; ?>
            </div>
            <a href="<?php echo esc_url(wc_get_endpoint_url('edit-account')); ?>" class="link-profile"
                style="margin-left: auto; font-size: 12px; color: #288ad6; white-space: nowrap;">Xem hồ sơ</a>
        </div>

        <div class="member-banner"
            style="background: #fff0f0; border-radius: 8px; padding: 12px; position: relative; overflow: hidden;">
            <div style="position: relative; z-index: 2;">
                <p style="font-size: 12px; color: #333; margin-bottom: 5px; font-weight: 600;">Quý khách chưa
                    là<br>thành viên FPT Shop</p>
                <a href="#"
                    style="display: inline-block; background: #cb1c22; color: #fff; font-size: 11px; padding: 4px 10px; border-radius: 12px; font-weight: 600;">Đăng
                    ký ngay</a>
            </div>
            <img src="/wp-content/uploads/2025/12/not_member.png"
                style="position: absolute; right: -10px; bottom: -10px; height: 60px; opacity: 0.8; z-index: 1;">
        </div>

    </div>

    <div class="white-box menu-box" style="padding: 10px 0;">
        <nav class="woocommerce-MyAccount-navigation">
            <ul class="fpt-acc-menu">
                <?php foreach (wc_get_account_menu_items() as $endpoint => $label) : ?>
                <li class="<?php echo wc_get_account_menu_item_classes($endpoint); ?>">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
                        <?php
                            // Icon FontAwesome Pro (Regular - far)
                            $icon = 'far fa-circle';

                            switch ($endpoint) {
                                case 'dashboard':
                                    $icon = 'far fa-home';
                                    $label = 'Trang chủ tài khoản';
                                    break;
                                case 'orders':
                                    $icon = 'far fa-box-open';
                                    $label = 'Đơn hàng của tôi';
                                    break;
                                case 'downloads':
                                    $icon = 'far fa-download';
                                    break;
                                case 'edit-address':
                                    $icon = 'far fa-map-marker-alt';
                                    $label = 'Sổ địa chỉ nhận hàng';
                                    break;
                                case 'edit-account':
                                    $icon = 'far fa-user-cog';
                                    $label = 'Thông tin tài khoản';
                                    break;
                                case 'customer-logout':
                                    $icon = 'far fa-sign-out-alt';
                                    break;
                                default:
                                    $icon = 'far fa-file-alt';
                                    break;
                            }
                            ?>
                        <i class="<?php echo $icon; ?>"
                            style="width: 24px; text-align: center; margin-right: 10px; color: #555; font-size: 18px;"></i>
                        <span class="menu-text"><?php echo esc_html($label); ?></span>
                        <i class="fas fa-chevron-right" style="margin-left: auto; font-size: 10px; color: #ccc;"></i>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>

</div>