<footer id="colophon" class="footer">
    <div class="container">
        <div class="row">
            <div class="col col-4">
                <h3>Về chúng tôi</h3>
                <p><?php bloginfo('description'); ?></p>
            </div>
            <div class="col col-4">
                <h3>Liên hệ</h3>
                <p>Hotline: <?php echo carbon_get_theme_option('contact_phone'); ?></p>
            </div>
            <div class="col col-4">
                <h3>Kết nối</h3>
                <p>Facebook / Zalo</p>
            </div>
        </div>
        <div class="site-info" style="border-top: 1px solid #eee; margin-top: 30px; padding-top: 20px; text-align: center; color: #999;">
            <?php echo wp_kses_post( carbon_get_theme_option('footer_copyright') ); ?>
        </div>
    </div>
</footer>