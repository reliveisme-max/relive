<?php

/**
 * Footer Template (Updated with Cart Popup)
 */
?>
</div><?php get_template_part('template-parts/global/footer-content'); ?>

<div class="relive-modal-overlay" id="review-modal">
    <div class="relive-modal-content">
        <div class="rm-header">
            <h3 id="rm-title-text">Đánh giá & nhận xét</h3>
            <span class="rm-close" id="btn-close-review"><i class="fas fa-times"></i></span>
        </div>
        <div class="rm-body">
            <div class="rm-product-info">
                <?php
                if (is_singular('product')) {
                    global $post;
                    echo get_the_post_thumbnail($post->ID, 'thumbnail', array('style' => 'width:50px; height:50px; object-fit:contain;'));
                    echo '<span style="font-weight:600; font-size:14px; margin-left:10px;">' . get_the_title() . '</span>';
                }
                ?>
            </div>

            <form id="relive-review-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="relive_submit_review">
                <input type="hidden" name="product_id" value="<?php echo get_the_ID(); ?>">
                <input type="hidden" name="comment_parent" id="comment_parent" value="0">
                <input type="hidden" name="rating" id="rating-input" value="5">
                <?php wp_nonce_field('relive_review_nonce', 'security'); ?>

                <div class="rm-rating-select">
                    <p class="label" style="margin-bottom:5px; font-weight:600;">Đánh giá chung:</p>
                    <div class="star-widget">
                        <i class="fas fa-star active" data-val="1"></i>
                        <i class="fas fa-star active" data-val="2"></i>
                        <i class="fas fa-star active" data-val="3"></i>
                        <i class="fas fa-star active" data-val="4"></i>
                        <i class="fas fa-star active" data-val="5"></i>
                    </div>
                    <div class="rating-text">Tuyệt vời</div>
                </div>

                <div class="rm-form-group">
                    <textarea name="comment" id="review_comment_content"
                        placeholder="Xin mời chia sẻ một số cảm nhận về sản phẩm (nhập tối thiểu 15 ký tự)..." required
                        style="min-height:100px;"></textarea>
                </div>

                <div class="rm-form-group upload-group" style="margin-bottom:15px;">
                    <label for="review_image" class="btn-upload-img"
                        style="border:1px dashed #ccc; padding:8px; border-radius:4px; display:inline-block; cursor:pointer; width: 100%; text-align:center;">
                        <i class="fas fa-camera" style="color:#288ad6;"></i>
                        <span style="font-size:13px; color:#288ad6;">Gửi ảnh thực tế (tối đa 3 ảnh)</span>
                    </label>
                    <input type="file" name="review_image[]" id="review_image" accept="image/*" multiple
                        style="display:none;">

                    <div id="review-img-preview" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;"></div>
                </div>

                <div class="rm-row-inputs" style="display:flex; gap:10px;">
                    <input type="text" name="author" placeholder="Họ tên (bắt buộc)" required style="flex:1;">
                    <input type="tel" name="phone" placeholder="Số điện thoại (bắt buộc)" required pattern="[0-9]*"
                        inputmode="numeric" maxlength="10" style="flex:1;">
                </div>

                <div class="rm-footer-action">
                    <button type="submit" class="btn-submit-review">GỬI ĐÁNH GIÁ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="fpt-cart-popup" class="fpt-cart-overlay">
    <div class="fpt-cart-box">
        <div class="fc-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="fc-message">Sản phẩm đã được thêm vào giỏ hàng</div>
        <div class="fc-actions">
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="btn-view-cart">Xem giỏ hàng</a>
            <span class="btn-close-popup"
                onclick="document.getElementById('fpt-cart-popup').classList.remove('open')">Tiếp tục mua sắm</span>
        </div>
    </div>
</div>

</div><?php wp_footer(); ?>

</body>

</html>