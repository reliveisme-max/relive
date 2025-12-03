<?php

/**
 * My Account Page - FPT Style (Final Fixed: Remove Duplicate User Info)
 * File: woocommerce/myaccount/my-account.php
 */

defined('ABSPATH') || exit;
?>

<div class="fpt-account-page" style="margin-bottom: 50px;">
    <div class="row">

        <div class="col col-3 col-md-12">
            <div class="acc-sidebar-area">
                <?php
                /**
                 * My Account navigation.
                 * @since 2.6.0
                 */
                do_action('woocommerce_account_navigation');
                ?>
            </div>
        </div>

        <div class="col col-9 col-md-12">
            <div class="acc-content-wrap white-box" style="min-height: 400px; padding: 25px;">

                <div class="fpt-notices-wrapper">
                    <?php wc_print_notices(); ?>
                </div>

                <?php
                /**
                 * My Account content.
                 * @since 2.6.0
                 */
                do_action('woocommerce_account_content');
                ?>
            </div>
        </div>

    </div>
</div>