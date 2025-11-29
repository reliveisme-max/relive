jQuery(document).ready(function($) {

    /* ==========================================================================
       1. GLOBAL UI
       ========================================================================== */
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) $('.header.sticky').addClass('is-scrolling');
        else $('.header.sticky').removeClass('is-scrolling');
    });

    $('a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
            if (target.length) {
                $('html, body').animate({ scrollTop: target.offset().top - 80 }, 800);
                return false;
            }
        }
    });

    /* ==========================================================================
       2. MEGA MENU & MOBILE
       ========================================================================== */
    $('.header-cat-btn').on('click', function(e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            $('.mega-menu-wrapper').addClass('open');
            $('body').addClass('no-scroll');
        }
    });

    $(document).on('click', '.m-close, .mega-overlay', function(e) {
        e.preventDefault(); e.stopPropagation();
        $('.mega-menu-wrapper').removeClass('open');
        $('body').removeClass('no-scroll');
    });

    $(document).on('mouseenter click', '.cat-item-left', function(e) {
        var isMobile = $(window).width() < 992;
        if (!isMobile && e.type === 'click') return; 
        if (isMobile && e.type === 'mouseenter') return;
        
        if (isMobile && e.type === 'click') {
            if ($(this).hasClass('active')) return;
            e.preventDefault();
        }
        $('.cat-item-left').removeClass('active');
        $(this).addClass('active');
        var uniqueID = $(this).data('id');
        $('.cat-pane').removeClass('active');
        $('#' + uniqueID).addClass('active');
    });

    $(document).on('click', '.brand-item, .sub-cat-item, .mini-prod-item, .btn-view-all', function(e) {
        if ($(window).width() < 992) {
            var url = $(this).attr('href');
            if (url && url !== '#' && url !== 'javascript:;') {
                window.location.href = url;
            }
        }
    });

    /* ==========================================================================
       3. SLIDERS (SWIPER)
       ========================================================================== */
    if (typeof Swiper !== 'undefined') {
        if ($('.main-slider').length) {
            new Swiper('.main-slider', { loop: true, speed: 800, autoplay: { delay: 4000 }, pagination: { el: '.swiper-pagination' }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } });
        }
        if ($('.cat-banner-swiper').length) {
            new Swiper('.cat-banner-swiper', { loop: true, autoplay: { delay: 4000 }, pagination: { el: '.cb-dots' }, navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' } });
        }
        $('.sub-cat-swiper-wrap').each(function() {
            var $el = $(this).find('.sub-cat-slider');
            var $next = $(this).find('.sc-next');
            var $prev = $(this).find('.sc-prev');
            if ($el.length) {
                new Swiper($el[0], {
                    slidesPerView: 3, spaceBetween: 10, observer: true, observeParents: true,
                    navigation: { nextEl: $next[0], prevEl: $prev[0] },
                    breakpoints: { 1200: { slidesPerView: 5 }, 992: { slidesPerView: 4 }, 768: { slidesPerView: 3 } }
                });
            }
        });
        if ($('.cat-slider').length) {
            $('.cat-slider').each(function() {
                new Swiper(this, {
                    slidesPerView: 4, spaceBetween: 10, navigation: { nextEl: '.cat-next', prevEl: '.cat-prev' },
                    breakpoints: { 1024: { slidesPerView: 8 }, 768: { slidesPerView: 6 } }
                });
            });
        }
    }

    /* ==========================================================================
       4. PRODUCT FILTERS (POPUP LỌC - FIXED LOGIC HIỆN 1 MỤC)
       ========================================================================== */
    
    // A. BẤM NÚT LỌC CHÍNH (Hiện tất cả)
    $(document).on('click', '#btn-open-filter', function(e) {
        e.preventDefault(); 
        $('#filter-popup').addClass('open'); 
        $('body').addClass('no-scroll');
        
        // Hiện lại tất cả các nhóm (vì nút Lọc chính là xem hết)
        $('.fp-group').show();
        $('.fp-header h3').text('Tất cả bộ lọc');
    });

    // B. BẤM NÚT LỌC NHANH (Chỉ hiện nhóm tương ứng)
    $(document).on('click', '.quick-btn', function(e) {
        e.preventDefault(); 
        $('#filter-popup').addClass('open'); 
        $('body').addClass('no-scroll');
        
        var targetID = $(this).data('target');
        // Lấy tên nút để thay đổi tiêu đề popup (VD: Dung lượng)
        var label = $(this).text().trim(); 

        // Ẩn tất cả các nhóm trước
        $('.fp-group').hide();

        // Chỉ hiện nhóm mục tiêu
        if(targetID && $('#' + targetID).length) {
            $('#' + targetID).show();
            // Đổi tiêu đề Popup cho khớp
            $('.fp-header h3').text(label); 
        } else {
            // Fallback nếu không tìm thấy ID thì hiện hết
            $('.fp-group').show();
            $('.fp-header h3').text('Tất cả bộ lọc');
        }
    });

    // Đóng Popup Lọc
    $(document).on('click', '#btn-close-filter, .fp-overlay', function(e) {
        e.preventDefault(); e.stopPropagation();
        $('#filter-popup').removeClass('open'); 
        $('body').removeClass('no-scroll');
        
        // Reset lại khi đóng để lần sau mở "Tất cả bộ lọc" vẫn thấy đủ
        setTimeout(function(){
            $('.fp-group').show();
            $('.fp-header h3').text('Tất cả bộ lọc');
        }, 300);
    });

    // Ajax Count
    var filterTimer;
    $(document).on('change', '.fp-content input', function() {
        var $btn = $('.fp-submit-btn');
        $btn.css('opacity', '0.7').text('Đang tính...');
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            $.post(relive_ajax.url, { 
                action: 'relive_get_filter_count', 
                form_data: $('.fp-content').serialize(), 
                nonce: relive_ajax.nonce 
            }, function(res) {
                if(res.success) {
                    $btn.text(res.data.count > 0 ? 'Xem '+res.data.count+' kết quả' : 'Không có kết quả')
                        .css('opacity', '1')
                        .prop('disabled', res.data.count <= 0);
                }
            });
        }, 300);
    });

    $('#btn-reset-filter').on('click', function(e) {
        e.preventDefault();
        $('.fp-option-item input').prop('checked', false);
        $('.fp-submit-btn').text('Xem kết quả').prop('disabled', false);
    });

    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var page = 1;
        var match = url.match(/page\/(\d+)/);
        if(match) page = match[1]; 
        else { 
            var p = new URLSearchParams(url.split('?')[1]); 
            if(p.has('paged')) page = p.get('paged'); 
        }
        
        $('.shop-products-grid').css('opacity', 0.4);
        $('html, body').animate({ scrollTop: $('.shop-filter-bar').offset().top - 100 }, 500);
        
        $.post(relive_ajax.url, { 
            action: 'relive_load_products', page: page, 
            form_data: $('.fp-content').serialize(), 
            orderby: (new URLSearchParams(window.location.search)).get('orderby') || 'date',
            nonce: relive_ajax.nonce 
        }, function(res) {
            if(res.success) {
                $('.shop-products-grid').html(res.data.products).css('opacity', 1);
                $('.shop-pagination').html(res.data.pagination);
                $('.shop-result-count').html(res.data.result_count);
                window.history.pushState({path: url}, '', url);
            }
        });
    });

    /* ==========================================================================
       5. CHI TIẾT SẢN PHẨM
       ========================================================================== */
    if ($('.product-main-slider').length && typeof Swiper !== 'undefined') {
        var productSwiper = new Swiper('.product-main-slider', {
            loop: false, spaceBetween: 10, navigation: { nextEl: '.p-next', prevEl: '.p-prev' },
            on: {
                slideChange: function () {
                    var idx = this.activeIndex;
                    $('.gallery-thumbs-nav-fpt .g-item').removeClass('active');
                    var btn = $('.gallery-thumbs-nav-fpt .g-item[data-slide-index="'+idx+'"]');
                    if(btn.length) btn.addClass('active'); else $('.gallery-thumbs-nav-fpt .g-item').first().addClass('active');
                    
                    var iframe = $('#prod-video-iframe');
                    if(iframe.length) {
                         if (!$(this.slides).eq(idx).hasClass('video-slide')) {
                            try { iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*'); } catch(e){}
                         }
                    }
                }
            }
        });

        $('.gallery-thumbs-nav-fpt .g-item').on('click', function(e) {
            if($(this).attr('href')) return;
            e.preventDefault();
            var idx = $(this).data('slide-index');
            if(idx !== undefined) productSwiper.slideTo(idx);
        });

        $(document).on('click', '.zoom-trigger', function(e) {
            e.preventDefault(); e.stopPropagation();
            if ($.fancybox) {
                var $links = $('[data-relive-gallery="product-gallery"]');
                var currentSrc = $(this).attr('href');
                var index = 0;
                $links.each(function(i) {
                    if ($(this).attr('href') === currentSrc) { index = i; return false; }
                });
                $.fancybox.open($links, {
                    loop: true, infobar: false,
                    buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"],
                    animationEffect: "fade", transitionEffect: "fade", touch: false,
                    clickContent: function(current, event) { return current.type === 'image' ? 'next' : false; }
                }, index);
            }
        });
    }

    /* ==========================================================================
       6. POPUP CẤU HÌNH
       ========================================================================== */
    $(document).off('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs, #btn-close-specs, .sp-close, .specs-popup-overlay');
    
    $(document).on('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs', function(e) {
        e.preventDefault(); $('#specs-popup').addClass('open'); $('body').addClass('no-scroll');
    });

    $(document).on('click', '#btn-close-specs, .sp-close', function(e) {
        e.preventDefault(); $('#specs-popup').removeClass('open'); $('body').removeClass('no-scroll');
    });

    $(document).on('click', '.specs-popup-overlay', function(e) {
        if (e.target === this) {
            e.preventDefault(); $('#specs-popup').removeClass('open'); $('body').removeClass('no-scroll');
        }
    });
    
    $(document).on('click', '.sp-nav-item', function(e) {
        e.preventDefault();
        $('.sp-nav-item').removeClass('active');
        $(this).addClass('active');
        var targetId = $(this).attr('href');
        var $container = $('.sp-body');
        var $target = $(targetId);
        if ($target.length) {
            $container.animate({ scrollTop: $target.offset().top - $container.offset().top + $container.scrollTop() - 50 }, 400);
        }
    });

    /* ==========================================================================
       7. BIẾN THỂ & GIÁ
       ========================================================================== */
    if ($('.variations_form').length > 0) {
        var $form = $('.variations_form');
        $form.find('.variations tr').each(function() {
            var $select = $(this).find('select');
            if ($select.length === 0) return;
            var attributeName = $select.attr('id'); 
            var $swatchWrap = $('<div class="relive-swatches-wrap" data-attribute="'+attributeName+'"></div>');
            $select.find('option').each(function() {
                var val = $(this).val(); if (!val) return; 
                var text = $(this).text();
                var $btn = $('<div class="swatch-item" data-value="' + val + '"></div>');
                var hasImage = false;
                if (typeof relive_swatches_json !== 'undefined' && relive_swatches_json[val]) {
                    var meta = relive_swatches_json[val];
                    if (meta.image) {
                        $btn.addClass('has-image');
                        $btn.append('<span class="swatch-img"><img src="'+meta.image+'" alt="'+text+'" /></span><span class="swatch-text">'+text+'</span>');
                        hasImage = true;
                    } else if (meta.color) {
                        $btn.addClass('has-color');
                        $btn.append('<span class="swatch-dot" style="background:'+meta.color+'"></span><span class="swatch-text">'+text+'</span>');
                        hasImage = true;
                    }
                }
                if (!hasImage) $btn.text(text);
                $swatchWrap.append($btn);
            });
            $select.after($swatchWrap).hide();
            $swatchWrap.on('click', '.swatch-item', function() {
                if ($(this).hasClass('disabled')) return; 
                var value = $(this).data('value');
                if ($(this).hasClass('selected')) { $(this).removeClass('selected'); $select.val('').trigger('change'); } 
                else { $(this).addClass('selected').siblings().removeClass('selected'); $select.val(value).trigger('change'); }
            });
        });

        $form.on('woocommerce_update_variation_values', function() {
            $form.find('.variations select').each(function() {
                var $select = $(this);
                var $swatchWrap = $select.next('.relive-swatches-wrap');
                $swatchWrap.find('.swatch-item').each(function() {
                    var val = $(this).data('value');
                    var $option = $select.find('option[value="' + val + '"]');
                    if ($option.length === 0 || $option.is(':disabled')) $(this).addClass('disabled').removeClass('selected');
                    else $(this).removeClass('disabled');
                });
            });
        });
        
        var $priceBlock = $('#fpt-price-dynamic');
        function formatMoney(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫'; }

        $form.on('found_variation', function(event, variation) {
            var price = variation.display_price; var regular = variation.display_regular_price; 
            $priceBlock.find('.current-price').html(formatMoney(price));
            if (regular > price) {
                var percent = Math.round(((regular - price) / regular) * 100);
                $priceBlock.find('.regular-price').html(formatMoney(regular));
                $priceBlock.find('.percent-tag').text('-' + percent + '%');
                $priceBlock.find('.old-price-wrap').removeClass('d-none');
            } else {
                $priceBlock.find('.old-price-wrap').addClass('d-none');
            }
            var points = Math.floor(price / 10000);
            $priceBlock.find('.points-val').text(points.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","));
            var installVal = Math.floor(price / 12);
            $priceBlock.find('.installment-price').text(formatMoney(installVal));
        });

        $('.reset_variations').on('click', function(){ $('.swatch-item').removeClass('selected disabled'); });
    }

    /* ==========================================================================
       8. NÚT MUA HÀNG
       ========================================================================== */
    $(document).on('click', '.action-trigger', function(e) {
        e.preventDefault();
        var type = $(this).data('type');
        var $form = $('.variations_form');
        if ($form.length > 0) {
            var variationID = $form.find('input[name="variation_id"]').val();
            if (!variationID || variationID == 0) { alert('Vui lòng chọn đầy đủ Màu sắc và Dung lượng!'); return; }
        }
        var $realBtn = $('.single_add_to_cart_button');
        if (type === 'buy-now') $realBtn.trigger('click'); else $realBtn.trigger('click');
    });

    /* ==========================================================================
       9. MÔ TẢ SẢN PHẨM & MUA KÈM
       ========================================================================== */
    $('#btn-expand-content').on('click', function(e) {
        e.preventDefault();
        var $content = $('#main-content-body');
        var $btn = $(this);
        if ($content.hasClass('expanded')) {
            $content.removeClass('expanded');
            $btn.html('Xem thêm <i class="fas fa-caret-down"></i>');
            $('html, body').animate({ scrollTop: $('#prod-description').offset().top - 80 }, 500);
        } else {
            $content.addClass('expanded');
            $btn.html('Thu gọn <i class="fas fa-caret-up"></i>');
        }
    });

    $('.bt-checkbox-btn input').on('change', function() {
        var $btn = $(this).next('.btn-select-add');
        if($(this).is(':checked')) $btn.html('Đã chọn <i class="fas fa-check"></i>');
        else $btn.html('Chọn thêm <i class="fas fa-plus"></i>');
    });

    /* ==========================================================================
       10. HỆ THỐNG ĐÁNH GIÁ (REVIEW SYSTEM)
       ========================================================================== */
    
    function loadReviews(page, star) {
        var $container = $('#relive-reviews-container');
        if($container.length === 0) return;
        var prodId = $container.data('product-id');
        
        $('.loading-reviews').show();
        $('.reviews-list-inner').css('opacity', 0.5);

        $.ajax({
            url: relive_ajax.url,
            type: 'POST',
            data: { action: 'relive_load_reviews', product_id: prodId, page: page, star: star, nonce: relive_ajax.nonce },
            success: function(res) {
                $('.loading-reviews').hide();
                $('.reviews-list-inner').css('opacity', 1).html(res.data.html);
                $('.reviews-pagination').html(res.data.pagination);
            }
        });
    }

    if($('#relive-reviews-container').length) loadReviews(1, 'all');

    var dt = new DataTransfer(); 

    $('#review_image').on('change', function(e) {
        var files = e.target.files;
        for (var i = 0; i < files.length; i++) {
            if(!files[i].type.match('image.*')) continue;
            if(dt.items.length >= 5) { alert("Tối đa 5 ảnh."); break; }
            dt.items.add(files[i]);
        }
        this.files = dt.files;
        renderImagePreview();
    });

    function renderImagePreview() {
        var $container = $('#review-img-preview');
        $container.empty();
        $.each(dt.files, function(i, file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var html = `
                    <div class="preview-item" style="position:relative; width:60px; height:60px;">
                        <img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                        <span class="remove-img-btn" data-index="${i}" style="position:absolute; top:-5px; right:-5px; background:red; color:#fff; border-radius:50%; width:16px; height:16px; font-size:10px; display:flex; align-items:center; justify-content:center; cursor:pointer;">&times;</span>
                    </div>`;
                $container.append(html);
            }
            reader.readAsDataURL(file);
        });
        if(dt.items.length > 0) $('.btn-upload-img span').text('Đã chọn ' + dt.items.length + ' ảnh');
        else $('.btn-upload-img span').text('Gửi ảnh thực tế (tối đa 5 ảnh)');
    }

    $(document).on('click', '.remove-img-btn', function() {
        dt.items.remove($(this).data('index'));
        $('#review_image')[0].files = dt.files;
        renderImagePreview();
    });

    function resetReviewForm() {
        $('#relive-review-form')[0].reset();
        dt = new DataTransfer();
        $('#review-img-preview').empty();
        $('.btn-upload-img span').text('Gửi ảnh thực tế (tối đa 5 ảnh)');
        $('#review-modal').removeClass('open');
        $('body').removeClass('no-scroll');
        $('.star-widget i').addClass('active'); 
        $('#rating-input').val(5);
    }

    $('#btn-open-review').on('click', function(e) {
        e.preventDefault();
        $('#review-modal').addClass('open');
        $('body').addClass('no-scroll');
        $('#comment_parent').val(0);
        $('#rm-title-text').text('Đánh giá & Nhận xét');
        $('.rm-rating-select').show();
    });

    $(document).on('click', '.btn-reply-trigger', function(e) {
        e.preventDefault();
        var parentId = $(this).data('id');
        var authorName = $(this).data('name');
        $('#review-modal').addClass('open');
        $('body').addClass('no-scroll');
        $('#comment_parent').val(parentId);
        $('#rm-title-text').text('Trả lời: ' + authorName);
        $('.rm-rating-select').hide();
        $('#review_comment_content').val('@' + authorName + ' ').focus();
    });

    $('#btn-close-review, .relive-modal-overlay').on('click', function(e) {
        if(e.target === this || $(this).attr('id') === 'btn-close-review') resetReviewForm();
    });

    $('.star-widget i').on('click', function() {
        var val = $(this).data('val');
        $('#rating-input').val(val);
        $('.star-widget i').removeClass('active');
        $('.star-widget i').each(function() { if($(this).data('val') <= val) $(this).addClass('active'); });
        var text = ['Rất tệ', 'Tệ', 'Bình thường', 'Tốt', 'Tuyệt vời'];
        $('.rating-text').text(text[val-1]);
    });

    $('.filter-star-item').on('click', function() {
        $('.filter-star-item').removeClass('active');
        $(this).addClass('active');
        loadReviews(1, $(this).data('star'));
    });

    $(document).on('click', '.reviews-pagination .page-numbers', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var page = 1;
        if(url) { var match = url.match(/paged=(\d+)/); if(match) page = match[1]; }
        loadReviews(page, $('.filter-star-item.active').data('star'));
    });

    // --- SUBMIT FORM (CÓ VALIDATE SĐT & TÊN) ---
    $('#relive-review-form').on('submit', function(e) {
        e.preventDefault();
        
        var author = $('input[name="author"]').val().trim();
        var phone = $('input[name="phone"]').val().trim();
        
        // 1. Validate Họ tên (Ít nhất 2 ký tự)
        if(author.length < 2) {
            alert('Vui lòng nhập Họ tên hợp lệ (tối thiểu 2 ký tự).');
            $('input[name="author"]').focus();
            return;
        }

        // 2. Validate Số điện thoại Việt Nam
        // Quy tắc: Bắt đầu bằng 0, theo sau là 3,5,7,8,9 và 8 số nữa. Tổng 10 số.
        var vnf_regex = /^(0)(3|5|7|8|9)[0-9]{8}$/;
        
        if (!vnf_regex.test(phone)) {
            alert('Số điện thoại không hợp lệ. Vui lòng nhập đúng SĐT Việt Nam (10 số, đầu 03, 05, 07, 08, 09).');
            $('input[name="phone"]').focus();
            return;
        }

        // Nếu đúng hết thì mới gửi
        var formData = new FormData(this);
        var $btn = $('.btn-submit-review');
        $btn.text('Đang gửi...').prop('disabled', true);

        $.ajax({
            url: relive_ajax.url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(res.success) {
                    alert('Gửi thành công! Đánh giá của bạn đang chờ kiểm duyệt.');
                    resetReviewForm();
                    loadReviews(1, 'all'); 
                } else {
                    alert(res.data.message || 'Lỗi xảy ra.');
                }
                $btn.text('GỬI ĐÁNH GIÁ').prop('disabled', false);
            },
            error: function() {
                alert('Lỗi kết nối.');
                $btn.text('GỬI ĐÁNH GIÁ').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.btn-like-review', function(e) {
        e.preventDefault();
        var $btn = $(this);
        if($btn.hasClass('liked')) return;
        $.post(relive_ajax.url, { action: 'relive_like_review', comment_id: $btn.data('id'), nonce: relive_ajax.nonce }, function(res) {
            if(res.success) {
                $btn.addClass('liked').css('color', '#cb1c22').find('span').text('Thích (' + res.data.count + ')');
            }
        });
    });
    /* ==========================================================================
       13. COPY COUPON CODE
       ========================================================================== */
    $(document).on('click', '.btn-copy-code', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var code = $btn.data('code');
        
        // Copy to clipboard
        navigator.clipboard.writeText(code).then(function() {
            $btn.text('Đã chép').addClass('copied');
            
            // Reset sau 2 giây
            setTimeout(function() {
                $btn.text('Sao chép').removeClass('copied');
            }, 2000);
        }, function(err) {
            alert('Không thể copy: ' + err);
        });
    });
    /* ==========================================================================
       14. CART QUANTITY (TĂNG GIẢM SỐ LƯỢNG)
       ========================================================================== */
    $(document).on('click', '.qty-btn', function() {
        var $btn = $(this);
        var $input = $btn.siblings('.qty-input');
        var val = parseInt($input.val());
        
        if ($btn.hasClass('plus')) {
            $input.val(val + 1);
        } else {
            if (val > 1) $input.val(val - 1);
        }
        
        // Tự động cập nhật giỏ hàng sau 0.5s
        $input.trigger('change');
        $('.woocommerce-cart-form button[name="update_cart"]').removeAttr('disabled').trigger('click');
    });
    /* ==========================================================================
       8. XỬ LÝ MUA HÀNG (MUA KÈM & BIẾN THỂ - FIX)
       ========================================================================== */
    $(document).on('click', '.action-trigger', function(e) {
        e.preventDefault();
        var type = $(this).data('type'); // 'buy-now' hoặc 'add-to-cart'
        var $form = $('.variations_form');
        
        // Lấy ID sản phẩm chính (Hỗ trợ cả Simple và Variable Product)
        var mainProductID = $('input[name="product_id"]').val() || $('button[name="add-to-cart"]').val() || $('.action-trigger').val();
        var variationID = 0;

        // 1. Kiểm tra biến thể (nếu là trang sản phẩm có biến thể)
        if ($form.length > 0) {
            variationID = $form.find('input[name="variation_id"]').val();
            if (!variationID || variationID == 0) {
                alert('Vui lòng chọn đầy đủ Màu sắc và Dung lượng!');
                return;
            }
        }

        // 2. Gom ID sản phẩm mua kèm
        var productIDs = [];
        
        // Luôn thêm SP chính đầu tiên
        if(mainProductID) {
            productIDs.push(mainProductID);
        } else {
            // Trường hợp fallback nếu không tìm thấy input hidden
            console.error("Không tìm thấy ID sản phẩm chính");
        }

        // Quét các checkbox mua kèm đã check
        $('input[name="add_bought_together[]"]:checked').each(function() {
            productIDs.push($(this).val());
        });

        console.log("DS Sản phẩm thêm vào giỏ:", productIDs); // Debug xem lấy đúng chưa

        // 3. Gửi Ajax
        var $btn = $(this);
        var originalText = $btn.text();
        $btn.css('opacity', '0.7').text('Đang xử lý...');

        $.ajax({
            url: relive_ajax.url,
            type: 'POST',
            data: {
                action: 'relive_add_multiple_to_cart',
                product_ids: productIDs,
                variation_id: variationID,
                nonce: relive_ajax.nonce
            },
            success: function(res) {
                if (res.success) {
                    if (type === 'buy-now') {
                        window.location.href = res.data.redirect;
                    } else {
                        alert('Đã thêm thành công!');
                        location.reload(); 
                    }
                } else {
                    alert(res.data.message || 'Lỗi thêm giỏ hàng.');
                }
                $btn.css('opacity', '1').text(originalText);
            },
            error: function() {
                alert('Lỗi kết nối server.');
                $btn.css('opacity', '1').text(originalText);
            }
        });
    });
});