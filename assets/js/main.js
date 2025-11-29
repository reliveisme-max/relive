jQuery(document).ready(function($) {

    /* GLOBAL UI */
    $(window).scroll(function() { if ($(this).scrollTop() > 50) $('.header.sticky').addClass('is-scrolling'); else $('.header.sticky').removeClass('is-scrolling'); });
    $('a[href*="#"]:not([href="#"])').click(function() { if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) { var target = $(this.hash); target = target.length ? target : $('[name=' + this.hash.slice(1) +']'); if (target.length) { $('html, body').animate({ scrollTop: target.offset().top - 80 }, 800); return false; } } });

    /* MEGA MENU & MOBILE */
    $('.header-cat-btn').on('click', function(e) { if ($(window).width() < 992) { e.preventDefault(); $('.mega-menu-wrapper').addClass('open'); $('body').addClass('no-scroll'); } });
    $(document).on('click', '.m-close, .mega-overlay', function(e) { e.preventDefault(); e.stopPropagation(); $('.mega-menu-wrapper').removeClass('open'); $('body').removeClass('no-scroll'); });
    $(document).on('mouseenter click', '.cat-item-left', function(e) { var isMobile = $(window).width() < 992; if (!isMobile && e.type === 'click') return; if (isMobile && e.type === 'mouseenter') return; if (isMobile && e.type === 'click') { if ($(this).hasClass('active')) return; e.preventDefault(); } $('.cat-item-left').removeClass('active'); $(this).addClass('active'); var uniqueID = $(this).data('id'); $('.cat-pane').removeClass('active'); $('#' + uniqueID).addClass('active'); });
    $(document).on('click', '.brand-item, .sub-cat-item, .mini-prod-item, .btn-view-all', function(e) { if ($(window).width() < 992) { var url = $(this).attr('href'); if (url && url !== '#' && url !== 'javascript:;') { window.location.href = url; } } });

    /* SLIDERS */
    if (typeof Swiper !== 'undefined') {
        if ($('.main-slider').length) new Swiper('.main-slider', { loop: true, speed: 800, autoplay: { delay: 4000 }, pagination: { el: '.swiper-pagination' }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } });
        if ($('.cat-banner-swiper').length) new Swiper('.cat-banner-swiper', { loop: true, autoplay: { delay: 4000 }, pagination: { el: '.cb-dots' }, navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' } });
        $('.sub-cat-swiper-wrap').each(function() { var $el = $(this).find('.sub-cat-slider'), $next = $(this).find('.sc-next'), $prev = $(this).find('.sc-prev'); if ($el.length) new Swiper($el[0], { slidesPerView: 3, spaceBetween: 10, observer: true, observeParents: true, navigation: { nextEl: $next[0], prevEl: $prev[0] }, breakpoints: { 1200: { slidesPerView: 5 }, 992: { slidesPerView: 4 }, 768: { slidesPerView: 3 } } }); });
        $('.cat-slider').each(function() { new Swiper(this, { slidesPerView: 4, spaceBetween: 10, navigation: { nextEl: '.cat-next', prevEl: '.cat-prev' }, breakpoints: { 1024: { slidesPerView: 8 }, 768: { slidesPerView: 6 } } }); });
    }

    /* FILTERS */
    $(document).on('click', '#btn-open-filter, .quick-btn', function(e) { e.preventDefault(); $('#filter-popup').addClass('open'); $('body').addClass('no-scroll'); var targetID = $(this).data('target'); if(targetID && $('#' + targetID).length) { $('.fp-group').hide(); $('#' + targetID).show(); $('.fp-header h3').text($(this).text().trim()); } else { $('.fp-group').show(); $('.fp-header h3').text('Tất cả bộ lọc'); } });
    $(document).on('click', '#btn-close-filter, .fp-overlay', function(e) { e.preventDefault(); $('#filter-popup').removeClass('open'); $('body').removeClass('no-scroll'); });
    var filterTimer;
    $(document).on('change', '.fp-content input', function() { var $btn = $('.fp-submit-btn'); $btn.css('opacity', '0.7').text('Đang tính...'); clearTimeout(filterTimer); filterTimer = setTimeout(function() { $.post(relive_ajax.url, { action: 'relive_get_filter_count', form_data: $('.fp-content').serialize(), nonce: relive_ajax.nonce }, function(res) { if(res.success) $btn.text(res.data.count > 0 ? 'Xem '+res.data.count+' kết quả' : 'Không có kết quả').css('opacity', '1').prop('disabled', res.data.count <= 0); }); }, 300); });
    $('#btn-reset-filter').on('click', function(e) { e.preventDefault(); $('.fp-option-item input').prop('checked', false); $('.fp-submit-btn').text('Xem kết quả').prop('disabled', false); });

    /* PRODUCT DETAIL SLIDER */
    if ($('.product-main-slider').length && typeof Swiper !== 'undefined') {
        var productSwiper = new Swiper('.product-main-slider', { loop: false, spaceBetween: 10, navigation: { nextEl: '.p-next', prevEl: '.p-prev' }, on: { slideChange: function () { var idx = this.activeIndex; $('.gallery-thumbs-nav-fpt .g-item').removeClass('active'); var btn = $('.gallery-thumbs-nav-fpt .g-item[data-slide-index="'+idx+'"]'); if(btn.length) btn.addClass('active'); else $('.gallery-thumbs-nav-fpt .g-item').first().addClass('active'); var iframe = $('#prod-video-iframe'); if(iframe.length && !$(this.slides).eq(idx).hasClass('video-slide')) { try { iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); } catch(e){} } } } });
        $('.gallery-thumbs-nav-fpt .g-item').on('click', function(e) { if($(this).attr('href')) return; e.preventDefault(); var idx = $(this).data('slide-index'); if(idx !== undefined) productSwiper.slideTo(idx); });
        $(document).on('click', '.zoom-trigger', function(e) { e.preventDefault(); if ($.fancybox) { $.fancybox.open($('[data-relive-gallery="product-gallery"]'), { loop: true, buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"] }, $('[data-relive-gallery="product-gallery"]').index(this)); } });
    }

    /* VARIATIONS */
    if ($('.variations_form').length > 0) {
        var $form = $('.variations_form');
        $form.find('.variations tr').each(function() { var $select = $(this).find('select'); if ($select.length === 0) return; var attributeName = $select.attr('id'); var $swatchWrap = $('<div class="relive-swatches-wrap" data-attribute="'+attributeName+'"></div>'); $select.find('option').each(function() { var val = $(this).val(); if (!val) return; var text = $(this).text(); var $btn = $('<div class="swatch-item" data-value="' + val + '"></div>'); if (typeof relive_swatches_json !== 'undefined' && relive_swatches_json[val]) { var meta = relive_swatches_json[val]; if (meta.image) { $btn.addClass('has-image').append('<span class="swatch-img"><img src="'+meta.image+'" /></span><span class="swatch-text">'+text+'</span>'); } else if (meta.color) { $btn.addClass('has-color').append('<span class="swatch-dot" style="background:'+meta.color+'"></span><span class="swatch-text">'+text+'</span>'); } else $btn.text(text); } else $btn.text(text); $swatchWrap.append($btn); }); $select.after($swatchWrap).hide(); $swatchWrap.on('click', '.swatch-item', function() { if (!$(this).hasClass('disabled')) { $(this).hasClass('selected') ? ($(this).removeClass('selected'), $select.val('').trigger('change')) : ($(this).addClass('selected').siblings().removeClass('selected'), $select.val($(this).data('value')).trigger('change')); } }); });
        $form.on('woocommerce_update_variation_values', function() { $form.find('.variations select').each(function() { var $select = $(this); var $swatchWrap = $select.next('.relive-swatches-wrap'); $swatchWrap.find('.swatch-item').each(function() { var val = $(this).data('value'); var $option = $select.find('option[value="' + val + '"]'); ($option.length === 0 || $option.is(':disabled')) ? $(this).addClass('disabled').removeClass('selected') : $(this).removeClass('disabled'); }); }); });
        var $priceBlock = $('#fpt-price-dynamic'); function formatMoney(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫'; }
        $form.on('found_variation', function(event, variation) { var price = variation.display_price; var regular = variation.display_regular_price; $priceBlock.find('.current-price').html(formatMoney(price)); if (regular > price) { var percent = Math.round(((regular - price) / regular) * 100); $priceBlock.find('.regular-price').html(formatMoney(regular)); $priceBlock.find('.percent-tag').text('-' + percent + '%'); $priceBlock.find('.old-price-wrap').removeClass('d-none'); } else { $priceBlock.find('.old-price-wrap').addClass('d-none'); } $priceBlock.find('.points-val').text(Math.floor(price / 10000).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")); $priceBlock.find('.installment-price').text(formatMoney(Math.floor(price / 12))); });
        $('.reset_variations').on('click', function(){ $('.swatch-item').removeClass('selected disabled'); });
    }

    $('#btn-expand-content').on('click', function(e) { e.preventDefault(); var $content = $('#main-content-body'); if ($content.hasClass('expanded')) { $content.removeClass('expanded'); $(this).html('Xem thêm <i class="fas fa-caret-down"></i>'); $('html, body').animate({ scrollTop: $('#prod-description').offset().top - 80 }, 500); } else { $content.addClass('expanded'); $(this).html('Thu gọn <i class="fas fa-caret-up"></i>'); } });
    $(document).on('click', '#btn-open-specs, #btn-open-specs-2', function(e) { e.preventDefault(); $('#specs-popup').addClass('open'); $('body').addClass('no-scroll'); });
    $(document).on('click', '#btn-close-specs, .sp-close, .specs-popup-overlay', function(e) { if (e.target === this || $(this).hasClass('sp-close')) { e.preventDefault(); $('#specs-popup').removeClass('open'); $('body').removeClass('no-scroll'); } });
    $(document).on('click', '.sp-nav-item', function(e) { e.preventDefault(); $('.sp-nav-item').removeClass('active'); $(this).addClass('active'); var $target = $($(this).attr('href')); if ($target.length) $('.sp-body').animate({ scrollTop: $target.offset().top - $('.sp-body').offset().top + $('.sp-body').scrollTop() - 50 }, 400); });

    /* [FIXED] NÚT MUA KÈM (Chỉ Check, KHÔNG Ajax) */
    $(document).off('change', '.bt-checkbox-btn input').on('change', '.bt-checkbox-btn input', function() {
        var $input = $(this); var $btn = $input.next('.btn-select-add');
        if($input.is(':checked')) { $btn.html('Đã chọn <i class="fas fa-check"></i>').addClass('added').css({'background': '#28a745', 'color': '#fff', 'border-color': '#28a745'}); } 
        else { $btn.html('Thêm <i class="fas fa-plus"></i>').removeClass('added').removeAttr('style'); }
    });

    /* COUPON LOCAL STORAGE */
    if($('body').hasClass('single-product')) localStorage.removeItem('relive_active_coupon');
    $(document).on('click', '.btn-copy-code', function(e) {
        e.preventDefault(); var $btn = $(this); var code = $btn.data('code');
        navigator.clipboard.writeText(code).then(function() {
            localStorage.setItem('relive_active_coupon', code);
            $btn.text('Đã Lưu').addClass('copied').css({'background':'#28a745', 'border-color':'#28a745'});
            setTimeout(function() { $btn.text('Sao chép').removeClass('copied').removeAttr('style'); }, 2000);
        });
    });

    /* MUA NGAY (GOM HÀNG) */
    $(document).on('click', '.action-trigger', function(e) {
        e.preventDefault(); var type = $(this).data('type'); var $form = $('.variations_form'); var $simpleBtn = $('button[name="add-to-cart"]'); 
        var mainProductID = 0; var variationID = 0; var quantity = $('input.qty').val() ? parseInt($('input.qty').val()) : 1;
        if ($form.length > 0) { mainProductID = $form.find('input[name="product_id"]').val(); variationID = $form.find('input[name="variation_id"]').val(); if (!variationID || variationID == 0) { alert('Vui lòng chọn đầy đủ Màu sắc/Phiên bản!'); return; } } 
        else if ($simpleBtn.length > 0) { mainProductID = $simpleBtn.val(); } 
        else { mainProductID = $('input[name="add-to-cart"]').val(); }
        if (!mainProductID) { alert('Lỗi: Không tìm thấy ID sản phẩm.'); return; }

        var productIDs = [];
        productIDs.push({ id: mainProductID, qty: quantity, vid: variationID });
        $('input[name="add_bought_together[]"]:checked').each(function() { productIDs.push({ id: $(this).val(), qty: 1, vid: 0 }); });

        var appliedCoupon = localStorage.getItem('relive_active_coupon') || '';
        var $btn = $(this); var originalText = $btn.html(); $btn.css('opacity', '0.7').text('Đang xử lý...');

        $.ajax({
            url: relive_ajax.url, type: 'POST',
            data: { action: 'relive_add_multiple_to_cart', items: productIDs, coupon_code: appliedCoupon, nonce: relive_ajax.nonce },
            success: function(res) {
                if (res.success) {
                    if (type === 'buy-now') { window.location.href = res.data.redirect; } 
                    else { var msg = 'Đã thêm vào giỏ hàng!'; if(res.data.coupon_applied) msg += '\nMã giảm giá ' + res.data.coupon_applied + ' đã được áp dụng.'; alert(msg); localStorage.removeItem('relive_active_coupon'); location.reload(); }
                } else { alert(res.data.message || 'Có lỗi xảy ra.'); }
                $btn.css('opacity', '1').html(originalText);
            },
            error: function() { alert('Lỗi kết nối máy chủ.'); $btn.css('opacity', '1').html(originalText); }
        });
    });

    /* REVIEW FORM */
    $('#relive-review-form').on('submit', function(e) {
        e.preventDefault(); var author = $('input[name="author"]').val().trim(); var phone = $('input[name="phone"]').val().trim();
        if(author.length < 2) { alert('Vui lòng nhập Họ tên hợp lệ.'); $('input[name="author"]').focus(); return; }
        if (!/^(0)(3|5|7|8|9)[0-9]{8}$/.test(phone)) { alert('Số điện thoại không hợp lệ.'); $('input[name="phone"]').focus(); return; }
        var formData = new FormData(this); var $btn = $('.btn-submit-review'); $btn.text('Đang gửi...').prop('disabled', true);
        $.ajax({
            url: relive_ajax.url, type: 'POST', data: formData, processData: false, contentType: false,
            success: function(res) {
                if(res.success) { alert('Gửi thành công! Đánh giá sẽ được kiểm duyệt.'); resetReviewForm(); loadReviews(1, 'all'); } 
                else { alert(res.data.message || 'Lỗi xảy ra.'); }
                $btn.text('GỬI ĐÁNH GIÁ').prop('disabled', false);
            },
            error: function() { alert('Lỗi kết nối.'); $btn.text('GỬI ĐÁNH GIÁ').prop('disabled', false); }
        });
    });
    $(document).on('click', '.btn-like-review', function(e) {
        e.preventDefault(); var $btn = $(this); if($btn.hasClass('liked')) return;
        $.post(relive_ajax.url, { action: 'relive_like_review', comment_id: $btn.data('id'), nonce: relive_ajax.nonce }, function(res) { if(res.success) { $btn.addClass('liked').css('color', '#cb1c22').find('span').text('Thích (' + res.data.count + ')'); } });
    });
    $(document).off('click', '.qty-btn').on('click', '.qty-btn', function(e) {
        e.preventDefault(); e.stopPropagation(); var $btn = $(this); var $wrap = $btn.closest('.ci-qty-wrap'); var $input = $btn.siblings('.qty-input'); var val = parseInt($input.val());
        $wrap.addClass('loading');
        if ($btn.hasClass('plus')) { $input.val(val + 1); } else { if (val > 1) { $input.val(val - 1); } else { $wrap.removeClass('loading'); return; } }
        $input.trigger('change'); $('button[name="update_cart"]').prop('disabled', false).trigger('click');
    });
});