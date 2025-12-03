<?php

/**
 * Edit account form - FPT Style (Cleaned)
 * File: woocommerce/myaccount/form-edit-account.php
 */
defined('ABSPATH') || exit;

do_action('woocommerce_before_edit_account_form'); ?>

<form class="woocommerce-EditAccountForm edit-account" action="" method="post"
    <?php do_action('woocommerce_edit_account_form_tag'); ?> enctype="multipart/form-data">

    <?php do_action('woocommerce_edit_account_form_start'); ?>

    <div class="account-avatar-wrap" style="text-align: center; margin-bottom: 30px;">
        <div class="avatar-preview">
            <?php
            $user = wp_get_current_user();
            echo get_avatar($user->ID, 100);
            ?>
        </div>
        <label for="account_avatar" class="btn-change-avatar">Thay đổi ảnh đại diện</label>
        <input type="file" name="account_avatar" id="account_avatar" accept="image/*" style="display:none;">
    </div>

    <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
        <label for="account_first_name"><?php esc_html_e('First name', 'woocommerce'); ?>&nbsp;<span
                class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name"
            id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" />
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
        <label for="account_last_name"><?php esc_html_e('Last name', 'woocommerce'); ?>&nbsp;<span
                class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name"
            id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" />
    </p>
    <div class="clear"></div>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="account_display_name"><?php esc_html_e('Display name', 'woocommerce'); ?>&nbsp;<span
                class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name"
            id="account_display_name" value="<?php echo esc_attr($user->display_name); ?>" />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="account_phone">Số điện thoại</label>
        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="account_phone"
            id="account_phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'billing_phone', true)); ?>" />
    </p>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="account_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span
                class="required">*</span></label>
        <input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email"
            id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" />
    </p>

    <fieldset>
        <legend><?php esc_html_e('Password change', 'woocommerce'); ?></legend>

        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label
                for="password_1"><?php esc_html_e('New password (leave blank to leave unchanged)', 'woocommerce'); ?></label>
            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1"
                id="password_1" autocomplete="new-password" />
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="password_2"><?php esc_html_e('Confirm new password', 'woocommerce'); ?></label>
            <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2"
                id="password_2" autocomplete="new-password" />
        </p>
    </fieldset>
    <div class="clear"></div>

    <?php do_action('woocommerce_edit_account_form'); ?>

    <p>
        <?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
        <button type="submit" class="woocommerce-Button button btn-fpt-primary" name="save_account_details"
            value="<?php esc_attr_e('Save changes', 'woocommerce'); ?>"><?php esc_html_e('Save changes', 'woocommerce'); ?></button>
        <input type="hidden" name="action" value="save_account_details" />
    </p>

    <?php do_action('woocommerce_edit_account_form_end'); ?>
</form>