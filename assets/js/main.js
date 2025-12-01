jQuery(document).ready(function($) {

    /* ==========================================================================
       1. CÁC HÀM HỖ TRỢ (FUNCTIONS)
       ========================================================================== */

    // Hàm Review: Xem trước ảnh
    var dt = new DataTransfer(); 
    function renderImagePreview() {
        var $container = $('#review-img-preview'); $container.empty();
        $.each(dt.files, function(i, file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var html = `<div class="preview-item" style="position:relative; width:60px; height:60px;"><img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:4px; border:1px solid #ddd;"><span class="remove-img-btn" data-index="${i}" style="position:absolute; top:-5px; right:-5px; background:red; color:#fff; border-radius:50%; width:16px; height:16px; font-size:10px; display:flex; align-items:center; justify-content:center; cursor:pointer;">&times;</span></div>`;
                $container.append(html);
            }
            reader.readAsDataURL(file);
        });
        if(dt.items.length > 0) $('.btn-upload-img span').text('Đã chọn ' + dt.items.length + ' ảnh');
        else $('.btn-upload-img span').text('Gửi ảnh thực tế (tối đa 5 ảnh)');
    }

    // Hàm Review: Reset Form
    function resetReviewForm() {
        $('#relive-review-form')[0].reset(); dt = new DataTransfer(); $('#review-img-preview').empty();
        $('.btn-upload-img span').text('Gửi ảnh thực tế (tối đa 5 ảnh)'); $('#review-modal').removeClass('open'); $('body').removeClass('no-scroll'); $('.star-widget i').addClass('active'); $('#rating-input').val(5);
    }

    // Hàm Review: Tải danh sách
    function loadReviews(page, star) {
        var $container = $('#relive-reviews-container'); if($container.length === 0) return;
        var prodId = $container.data('product-id');
        $('.loading-reviews').show(); $('.reviews-list-inner').css('opacity', 0.5);
        $.ajax({
            url: relive_ajax.url, type: 'POST',
            data: { action: 'relive_load_reviews', product_id: prodId, page: page, star: star, nonce: relive_ajax.nonce },
            success: function(res) {
                $('.loading-reviews').hide();
                $('.reviews-list-inner').css('opacity', 1).html(res.data.html);
                $('.reviews-pagination').html(res.data.pagination);
            }
        });
    }

    // Hàm Cart: Xóa sản phẩm (Ajax)
    function removeCartItem(key, $row) {
        $row.css('opacity', '0.5').css('pointer-events', 'none');
        $('.cart-sidebar').css('opacity', '0.5');
        $.ajax({
            url: relive_ajax.url, type: 'POST', dataType: 'json',
            data: { action: 'relive_remove_cart_item', cart_item_key: key, nonce: relive_ajax.cart_nonce },
            success: function(res) {
                if (res.success) {
                    if (res.data.is_empty) { window.location.reload(); } else {
                        $row.fadeOut(300, function() { $(this).remove(); });
                        $('.cart-sidebar').html(res.data.sidebar_html).css('opacity', '1');
                        $('.cart-count').text(res.data.cart_count);
                        $('.cart-item-block span').first().text('Giỏ hàng (' + res.data.cart_count + ' sản phẩm)');
                    }
                } else { alert(res.data.message || 'Lỗi không xác định'); window.location.reload(); }
            },
            error: function() { alert('Lỗi kết nối server.'); window.location.reload(); }
        });
    }

    /* ==========================================================================
       2. LOGIC POPUP XÁC NHẬN XÓA (FPT STYLE)
       ========================================================================== */
    var pendingDelete = { key: null, row: null, checkbox: null };

    function openDeleteModal(key, $row, $checkbox = null) {
        pendingDelete.key = key; pendingDelete.row = $row; pendingDelete.checkbox = $checkbox;
        $('#fpt-delete-modal').addClass('open');
    }
    function closeDeleteModal() {
        $('#fpt-delete-modal').removeClass('open');
        if (pendingDelete.checkbox) $(pendingDelete.checkbox).prop('checked', true);
        pendingDelete = { key: null, row: null, checkbox: null };
    }

    $(document).on('click', '.ajax-remove-item', function(e) {
        e.preventDefault();
        var key = $(this).data('key');
        var $row = $(this).closest('.cart-item-block');
        if ($row.length === 0) $row = $(this).closest('.cart-item-addon');
        openDeleteModal(key, $row, null);
    });

    $(document).on('change', '.cart-checkbox-remove', function(e) {
        if (!$(this).is(':checked')) {
            var key = $(this).data('key');
            var $row = $(this).closest('.cart-item-block');
            openDeleteModal(key, $row, this);
        }
    });

    $('#btn-cancel-delete, #btn-close-delete-modal').on('click', function() { closeDeleteModal(); });
    $('#fpt-delete-modal').on('click', function(e) { if (e.target === this) closeDeleteModal(); });
    $('#btn-confirm-delete').on('click', function() {
        if (pendingDelete.key && pendingDelete.row) {
            removeCartItem(pendingDelete.key, pendingDelete.row);
            $('#fpt-delete-modal').removeClass('open');
            pendingDelete = { key: null, row: null, checkbox: null };
        }
    });

    /* ==========================================================================
       3. POPUP THÔNG SỐ & SCROLLSPY (TỐC ĐỘ CAO)
       ========================================================================== */
    
    // Hàm kích hoạt theo dõi cuộn (ScrollSpy)
    function attachScrollSpy() {
        var $container = $('.sp-body');
        var $sections = $('.sp-group-section');
        var $menu = $('.sp-nav-menu');
        var isTicking = false;

        $container.off('scroll.spy'); // Reset trước khi gán

        $container.on('scroll.spy', function() {
            if (!isTicking) {
                window.requestAnimationFrame(function() {
                    var containerTop = $container.offset().top;
                    var triggerPoint = containerTop + 60; 

                    $sections.each(function() {
                        var $sec = $(this);
                        var secTop = $sec.offset().top;
                        var secBottom = secTop + $sec.outerHeight();

                        if (secTop <= triggerPoint && secBottom > triggerPoint) {
                            var id = $sec.attr('id');
                            var $activeLink = $('.sp-nav-item[href="#' + id + '"]');
                            
                            if (!$activeLink.hasClass('active')) {
                                $('.sp-nav-item').removeClass('active');
                                $activeLink.addClass('active');
                                
                                // Tự cuộn menu ngang
                                if ($activeLink.length) {
                                    var leftPos = $activeLink.position().left + $menu.scrollLeft() - ($menu.width() / 2) + ($activeLink.width() / 2);
                                    $menu.stop(true, false).animate({ scrollLeft: leftPos }, 150);
                                }
                            }
                            return false; 
                        }
                    });
                    isTicking = false;
                });
                isTicking = true;
            }
        });
    }

    // Xử lý Click Tab (Tốc độ 200ms - Rất nhanh)
    $(document).on('click', '.sp-nav-item', function(e) {
        e.preventDefault();
        var targetId = $(this).attr('href');
        var $target = $(targetId);
        var $container = $('.sp-body');

        if ($target.length && $container.length) {
            $container.off('scroll.spy'); // Tắt Spy để tránh xung đột
            $('.sp-nav-item').removeClass('active');
            $(this).addClass('active');

            // Tính toán vị trí chính xác
            var scrollPoint = $target.offset().top - $container.offset().top + $container.scrollTop() - 45;
            
            // Animate nhanh (200ms)
            $container.stop().animate({ scrollTop: scrollPoint }, 200, function() {
                attachScrollSpy(); // Bật lại Spy
            });
        }
    });

    // Mở Popup
    $(document).on('click', '#btn-open-specs, #btn-open-specs-2', function(e) { 
        e.preventDefault(); 
        $('#specs-popup').addClass('open'); 
        $('body').addClass('no-scroll'); 
        setTimeout(attachScrollSpy, 200); 
    });
    
    // Đóng Popup
    $(document).on('click', '#btn-close-specs, .sp-close, .specs-popup-overlay', function(e) { 
        if (e.target === this || $(this).hasClass('sp-close')) { 
            e.preventDefault(); 
            $('#specs-popup').removeClass('open'); 
            $('body').removeClass('no-scroll'); 
            $('.sp-body').off('scroll.spy'); 
        } 
    });


    /* ==========================================================================
       4. GLOBAL UI EVENTS
       ========================================================================== */
    
    $(window).scroll(function() { if ($(this).scrollTop() > 50) $('.header.sticky').addClass('is-scrolling'); else $('.header.sticky').removeClass('is-scrolling'); });
    $('.header-cat-btn').on('click', function(e) { if ($(window).width() < 992) { e.preventDefault(); $('.mega-menu-wrapper').addClass('open'); $('body').addClass('no-scroll'); } });
    $(document).on('click', '.m-close, .mega-overlay', function(e) { e.preventDefault(); e.stopPropagation(); $('.mega-menu-wrapper').removeClass('open'); $('body').removeClass('no-scroll'); });
    $(document).on('mouseenter click', '.cat-item-left', function(e) { var isMobile = $(window).width() < 992; if (!isMobile && e.type === 'click') return; if (isMobile && e.type === 'mouseenter') return; if (isMobile && e.type === 'click') { if ($(this).hasClass('active')) return; e.preventDefault(); } $('.cat-item-left').removeClass('active'); $(this).addClass('active'); var uniqueID = $(this).data('id'); $('.cat-pane').removeClass('active'); $('#' + uniqueID).addClass('active'); });

    if (typeof Swiper !== 'undefined') {
        if ($('.main-slider').length) new Swiper('.main-slider', { loop: true, speed: 800, autoplay: { delay: 4000 }, pagination: { el: '.swiper-pagination' }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } });
        if ($('.cat-banner-swiper').length) new Swiper('.cat-banner-swiper', { loop: true, autoplay: { delay: 4000 }, pagination: { el: '.cb-dots' }, navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' } });
        $('.sub-cat-swiper-wrap').each(function() { var $el = $(this).find('.sub-cat-slider'), $next = $(this).find('.sc-next'), $prev = $(this).find('.sc-prev'); if ($el.length) new Swiper($el[0], { slidesPerView: 3, spaceBetween: 10, observer: true, observeParents: true, navigation: { nextEl: $next[0], prevEl: $prev[0] }, breakpoints: { 1200: { slidesPerView: 5 }, 992: { slidesPerView: 4 }, 768: { slidesPerView: 3 } } }); });
        $('.cat-slider').each(function() { new Swiper(this, { slidesPerView: 4, spaceBetween: 10, navigation: { nextEl: '.cat-next', prevEl: '.cat-prev' }, breakpoints: { 1024: { slidesPerView: 8 }, 768: { slidesPerView: 6 } } }); });
        if ($('.product-main-slider').length) {
            var productSwiper = new Swiper('.product-main-slider', { loop: false, spaceBetween: 10, navigation: { nextEl: '.p-next', prevEl: '.p-prev' }, on: { slideChange: function () { var idx = this.activeIndex; $('.gallery-thumbs-nav-fpt .g-item').removeClass('active'); var btn = $('.gallery-thumbs-nav-fpt .g-item[data-slide-index="'+idx+'"]'); if(btn.length) btn.addClass('active'); else $('.gallery-thumbs-nav-fpt .g-item').first().addClass('active'); var iframe = $('#prod-video-iframe'); if(iframe.length && !$(this.slides).eq(idx).hasClass('video-slide')) { try { iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); } catch(e){} } } } });
            $('.gallery-thumbs-nav-fpt .g-item').on('click', function(e) { if($(this).attr('href')) return; e.preventDefault(); var idx = $(this).data('slide-index'); if(idx !== undefined) productSwiper.slideTo(idx); });
        }
    }
    $(document).on('click', '.zoom-trigger', function(e) { e.preventDefault(); if ($.fancybox) { $.fancybox.open($('[data-relive-gallery="product-gallery"]'), { loop: true, buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"] }, $('[data-relive-gallery="product-gallery"]').index(this)); } });

    // Filter & Search
    $(document).on('click', '#btn-open-filter, .quick-btn', function(e) { e.preventDefault(); $('#filter-popup').addClass('open'); $('body').addClass('no-scroll'); var targetID = $(this).data('target'); if(targetID && $('#' + targetID).length) { $('.fp-group').hide(); $('#' + targetID).show(); $('.fp-header h3').text($(this).text().trim()); } else { $('.fp-group').show(); $('.fp-header h3').text('Tất cả bộ lọc'); } });
    $(document).on('click', '#btn-close-filter, .fp-overlay', function(e) { e.preventDefault(); $('#filter-popup').removeClass('open'); $('body').removeClass('no-scroll'); });
    var filterTimer;
    $(document).on('change', '.fp-content input', function() { var $btn = $('.fp-submit-btn'); $btn.css('opacity', '0.7').text('Đang tính...'); clearTimeout(filterTimer); filterTimer = setTimeout(function() { $.post(relive_ajax.url, { action: 'relive_get_filter_count', form_data: $('.fp-content').serialize(), nonce: relive_ajax.nonce }, function(res) { if(res.success) $btn.text(res.data.count > 0 ? 'Xem '+res.data.count+' kết quả' : 'Không có kết quả').css('opacity', '1').prop('disabled', res.data.count <= 0); }); }, 300); });
    $('#btn-reset-filter').on('click', function(e) { e.preventDefault(); $('.fp-option-item input').prop('checked', false); $('.fp-submit-btn').text('Xem kết quả').prop('disabled', false); });
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault(); var url = $(this).attr('href'); var page = 1;
        var match = url.match(/page\/(\d+)/); if(match) page = match[1]; else { var p = new URLSearchParams(url.split('?')[1]); if(p.has('paged')) page = p.get('paged'); }
        $('.shop-products-grid').css('opacity', 0.4); $('html, body').animate({ scrollTop: $('.shop-filter-bar').offset().top - 100 }, 500);
        $.post(relive_ajax.url, { action: 'relive_load_products', page: page, form_data: $('.fp-content').serialize(), orderby: (new URLSearchParams(window.location.search)).get('orderby') || 'date', nonce: relive_ajax.nonce }, function(res) { if(res.success) { $('.shop-products-grid').html(res.data.products).css('opacity', 1); $('.shop-pagination').html(res.data.pagination); window.history.pushState({path: url}, '', url); } });
    });

    // Variations
    if ($('.variations_form').length > 0) {
        var $form = $('.variations_form');
        $form.find('.variations tr').each(function() {
            var $select = $(this).find('select'); if ($select.length === 0) return;
            var attributeName = $select.attr('id'); var $swatchWrap = $('<div class="relive-swatches-wrap" data-attribute="'+attributeName+'"></div>');
            $select.find('option').each(function() { var val = $(this).val(); if (!val) return; var text = $(this).text(); var $btn = $('<div class="swatch-item" data-value="' + val + '"></div>'); if (typeof relive_swatches_json !== 'undefined' && relive_swatches_json[val]) { var meta = relive_swatches_json[val]; if (meta.image) $btn.addClass('has-image').append('<span class="swatch-img"><img src="'+meta.image+'" /></span><span class="swatch-text">'+text+'</span>'); else if (meta.color) $btn.addClass('has-color').append('<span class="swatch-dot" style="background:'+meta.color+'"></span><span class="swatch-text">'+text+'</span>'); else $btn.text(text); } else { $btn.text(text); } $swatchWrap.append($btn); });
            $select.after($swatchWrap).hide(); var defaultVal = $select.val(); if (defaultVal) $swatchWrap.find('.swatch-item[data-value="'+defaultVal+'"]').addClass('selected');
            $swatchWrap.on('click', '.swatch-item', function() { if (!$(this).hasClass('disabled')) { if ($(this).hasClass('selected')) { $(this).removeClass('selected'); $select.val('').trigger('change'); } else { $(this).addClass('selected').siblings().removeClass('selected'); $select.val($(this).data('value')).trigger('change'); } } });
        });
        $form.on('woocommerce_update_variation_values', function() { $form.find('.variations select').each(function() { var $select = $(this); var $swatchWrap = $select.next('.relive-swatches-wrap'); $swatchWrap.find('.swatch-item').each(function() { var val = $(this).data('value'); var $option = $select.find('option[value="' + val + '"]'); if ($option.length === 0 || $option.is(':disabled')) $(this).addClass('disabled').removeClass('selected'); else $(this).removeClass('disabled'); }); }); });
        var $priceBlock = $('#fpt-price-dynamic'); function formatMoney(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫'; }
        $form.on('found_variation', function(event, variation) { var price = variation.display_price; var regular = variation.display_regular_price; $priceBlock.find('.current-price').html(formatMoney(price)); if (regular > price) { var percent = Math.round(((regular - price) / regular) * 100); $priceBlock.find('.regular-price').html(formatMoney(regular)); $priceBlock.find('.percent-tag').text('-' + percent + '%'); $priceBlock.find('.old-price-wrap').removeClass('d-none'); } else { $priceBlock.find('.old-price-wrap').addClass('d-none'); } $priceBlock.find('.points-val').text(Math.floor(price / 10000).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")); $priceBlock.find('.installment-price').text(formatMoney(Math.floor(price / 12))); if (variation.image && variation.image.src) { var $mainImg = $('.product-main-slider .swiper-slide[data-type="featured"] img'); if ($mainImg.length) $mainImg.attr('src', variation.image.src); } });
        $('.reset_variations').on('click', function(){ $('.swatch-item').removeClass('selected disabled'); });
    }

    if($('body').hasClass('single-product')) localStorage.removeItem('relive_active_coupon');
    $(document).on('click', '.btn-copy-code', function(e) { e.preventDefault(); var $btn = $(this); var code = $btn.data('code'); navigator.clipboard.writeText(code).then(function() { localStorage.setItem('relive_active_coupon', code); $btn.text('Đã Lưu').addClass('copied').css({'background':'#28a745', 'border-color':'#28a745'}); setTimeout(function() { $btn.text('Sao chép').removeClass('copied').removeAttr('style'); }, 2000); }); });
    var $variationForm = $('.variations_form'); var $btInputs = $('.bt-checkbox-btn input'); var $btLabels = $('.bt-checkbox-btn'); var $btBtns = $('.btn-select-add');
    function resetBoughtTogether() { $btInputs.prop('disabled', true).prop('checked', false).trigger('change'); $btLabels.css({'opacity': '0.5', 'pointer-events': 'none', 'cursor': 'not-allowed'}); $btBtns.html('Thêm <i class="fas fa-plus"></i>').removeClass('added').removeAttr('style'); }
    function activateBoughtTogether() { $btInputs.prop('disabled', false); $btLabels.css({'opacity': '1', 'pointer-events': 'auto', 'cursor': 'pointer'}); $btInputs.prop('checked', true).trigger('change'); }
    if ($variationForm.length > 0) { resetBoughtTogether(); $variationForm.on('found_variation', function(event, variation) { if (variation.is_purchasable) activateBoughtTogether(); else resetBoughtTogether(); }); $variationForm.on('reset_data', function() { resetBoughtTogether(); }); }
    $(document).off('change', '.bt-checkbox-btn input').on('change', '.bt-checkbox-btn input', function() { var $input = $(this); var $btn = $input.next('.btn-select-add'); if($input.is(':checked')) $btn.html('Đã chọn <i class="fas fa-check"></i>').addClass('added').css({'background': '#28a745', 'color': '#fff', 'border-color': '#28a745'}); else $btn.html('Thêm <i class="fas fa-plus"></i>').removeClass('added').removeAttr('style'); });

    $(document).on('click', '.action-trigger', function(e) { e.preventDefault(); var $btn = $(this); var type = $btn.data('type'); var originalText = $btn.html(); var $form = $('.variations_form'); var $simpleBtn = $('button[name="add-to-cart"]'); var mainProductID = 0; var variationID = 0; var quantity = $('input.qty').val() ? parseInt($('input.qty').val()) : 1; if ($form.length > 0) { mainProductID = $form.find('input[name="product_id"]').val(); variationID = $form.find('input[name="variation_id"]').val(); if (!variationID || variationID == 0) { alert('Vui lòng chọn đầy đủ Màu sắc/Phiên bản!'); return; } } else if ($simpleBtn.length > 0) { mainProductID = $simpleBtn.val(); } else { mainProductID = $('input[name="add-to-cart"]').val(); } if (!mainProductID) { console.log('Lỗi: Không tìm thấy ID sản phẩm.'); return; } var productIDs = []; productIDs.push({ id: mainProductID, qty: quantity, vid: variationID }); $('input[name="add_bought_together[]"]:checked').each(function() { productIDs.push({ id: $(this).val(), qty: 1, vid: 0 }); }); var appliedCoupon = localStorage.getItem('relive_active_coupon') || ''; $btn.css('opacity', '0.7').html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...'); $.ajax({ url: relive_ajax.url, type: 'POST', dataType: 'json', data: { action: 'relive_add_multiple_to_cart', items: productIDs, coupon_code: appliedCoupon, nonce: relive_ajax.nonce }, success: function(res) { if (res.success) { if (type === 'buy-now') window.location.href = res.data.redirect; else { $('#fpt-cart-popup').addClass('open'); $(document.body).trigger('wc_fragment_refresh'); $(document.body).trigger('added_to_cart'); setTimeout(function(){ $('#fpt-cart-popup').removeClass('open'); }, 3000); localStorage.removeItem('relive_active_coupon'); } } else { alert(res.data.message || 'Có lỗi xảy ra, vui lòng thử lại.'); } $btn.css('opacity', '1').html(originalText); }, error: function(xhr) { console.log('AJAX Error:', xhr.responseText); alert('Lỗi kết nối.'); $btn.css('opacity', '1').html(originalText); } }); });
    $(document).off('click', '.qty-btn').on('click', '.qty-btn', function(e) { e.preventDefault(); e.stopPropagation(); var $btn = $(this); var $wrap = $btn.closest('.ci-qty-wrap'); var $input = $btn.siblings('.qty-input'); var val = parseInt($input.val()); $wrap.addClass('loading'); if ($btn.hasClass('plus')) { $input.val(val + 1); } else { if (val > 1) { $input.val(val - 1); } else { $wrap.removeClass('loading'); return; } } $input.trigger('change'); $('button[name="update_cart"]').prop('disabled', false).trigger('click'); });

    if($('#relive-reviews-container').length) loadReviews(1, 'all');
    $(document).on('click', '.filter-star-item', function(e) { e.preventDefault(); $('.filter-star-item').removeClass('active'); $(this).addClass('active'); loadReviews(1, $(this).data('star')); });
    $(document).on('click', '.reviews-pagination a', function(e) { e.preventDefault(); var url = $(this).attr('href'); var page = 1; var match = url.match(/[?&]paged=(\d+)/); if (!match) match = url.match(/\/page\/(\d+)/); if (!match) match = url.match(/\/comment-page-(\d+)/); if (match) page = match[1]; loadReviews(page, $('.filter-star-item.active').data('star') || 'all'); $('html, body').animate({ scrollTop: $('#prod-reviews').offset().top - 100 }, 500); });
    $('#review_image').on('change', function(e) { var files = e.target.files; for (var i = 0; i < files.length; i++) { if (!files[i].type.match('image.*')) continue; if (dt.items.length >= 5) { alert("Tối đa 5 ảnh."); break; } dt.items.add(files[i]); } this.files = dt.files; renderImagePreview(); });
    $(document).on('click', '.remove-img-btn', function() { dt.items.remove($(this).data('index')); $('#review_image')[0].files = dt.files; renderImagePreview(); });
    $('#btn-open-review').on('click', function(e) { e.preventDefault(); $('#review-modal').addClass('open'); $('body').addClass('no-scroll'); $('#comment_parent').val(0); $('#rm-title-text').text('Đánh giá & Nhận xét'); $('.rm-rating-select').show(); });
    $('#btn-close-review, .relive-modal-overlay').on('click', function(e) { if (e.target === this || $(this).attr('id') === 'btn-close-review') { resetReviewForm(); $('#rm-title-text').text('Đánh giá & Nhận xét'); $('.rm-rating-select').show(); $('#comment_parent').val(0); } });
    $(document).on('click', '.star-widget i', function() { var rating = $(this).data('val'); $('#rating-input').val(rating); $('.star-widget i').removeClass('active'); $('.star-widget i').each(function() { if ($(this).data('val') <= rating) $(this).addClass('active'); }); var ratingTexts = { 1: 'Rất tệ', 2: 'Không hài lòng', 3: 'Bình thường', 4: 'Hài lòng', 5: 'Tuyệt vời' }; $('.rating-text').text(ratingTexts[rating]); });
    $('#relive-review-form').on('submit', function(e) { e.preventDefault(); var author = $('input[name="author"]').val().trim(); var phone = $('input[name="phone"]').val().trim(); if (author.length < 2) { alert('Vui lòng nhập Họ tên hợp lệ.'); $('input[name="author"]').focus(); return; } if (!/^(0)(3|5|7|8|9)[0-9]{8}$/.test(phone)) { alert('Số điện thoại không hợp lệ.'); $('input[name="phone"]').focus(); return; } var formData = new FormData(this); var $btn = $('.btn-submit-review'); $btn.text('Đang gửi...').prop('disabled', true); $.ajax({ url: relive_ajax.url, type: 'POST', data: formData, processData: false, contentType: false, success: function(res) { if (res.success) { $('#review-modal').removeClass('open'); $('body').removeClass('no-scroll'); $('#fpt-review-popup').addClass('open'); setTimeout(function() { $('#fpt-review-popup').removeClass('open'); }, 3000); resetReviewForm(); loadReviews(1, 'all'); } else { alert(res.data.message || 'Lỗi xảy ra.'); } $btn.text('GỬI ĐÁNH GIÁ').prop('disabled', false); }, error: function() { alert('Lỗi kết nối.'); $btn.text('GỬI ĐÁNH GIÁ').prop('disabled', false); } }); });
    $(document).on('click', '.btn-reply-trigger', function(e) { e.preventDefault(); var parentId = $(this).data('id'); var authorName = $(this).data('name'); $('#review-modal').addClass('open'); $('body').addClass('no-scroll'); $('#comment_parent').val(parentId); $('#rm-title-text').text('Trả lời: ' + authorName); $('.rm-rating-select').hide(); $('#review_comment_content').val('@' + authorName + ' ').focus(); });
    $(document).on('click', '.btn-like-review', function(e) { e.preventDefault(); var $btn = $(this); if ($btn.hasClass('liked')) return; $.post(relive_ajax.url, { action: 'relive_like_review', comment_id: $btn.data('id'), nonce: relive_ajax.nonce }, function(res) { if (res.success) { $btn.addClass('liked').css('color', '#cb1c22').find('span').text('Thích (' + res.data.count + ')'); } }); });

    $('#btn-expand-content').on('click', function(e) { e.preventDefault(); var $content = $('#main-content-body'); if ($content.hasClass('expanded')) { $content.removeClass('expanded'); $(this).html('Xem thêm <i class="fas fa-caret-down"></i>'); $('html, body').animate({ scrollTop: $('#prod-description').offset().top - 80 }, 500); } else { $content.addClass('expanded'); $(this).html('Thu gọn <i class="fas fa-caret-up"></i>'); } });
});