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
        // Banner chính
        if ($('.main-slider').length) {
            new Swiper('.main-slider', { loop: true, speed: 800, autoplay: { delay: 4000 }, pagination: { el: '.swiper-pagination' }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' } });
        }
        // Banner danh mục
        if ($('.cat-banner-swiper').length) {
            new Swiper('.cat-banner-swiper', { loop: true, autoplay: { delay: 4000 }, pagination: { el: '.cb-dots' }, navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' } });
        }
        
        // Mega Menu Sub-Cat Slider (Fix lỗi hiển thị)
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

        // Slider Danh mục trang chủ
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
       4. PRODUCT FILTERS (POPUP LỌC) - [ĐÃ BỔ SUNG LẠI]
       ========================================================================== */
    
    // Mở Popup Lọc
    $(document).on('click', '#btn-open-filter, .quick-btn', function(e) {
        e.preventDefault(); 
        $('#filter-popup').addClass('open'); 
        $('body').addClass('no-scroll');
        
        // Cuộn tới nhóm lọc nếu bấm nút nhanh
        var target = $(this).data('target');
        if(target && $('#'+target).length) {
            setTimeout(function(){ 
                $('.fp-body').animate({ 
                    scrollTop: $('#'+target).offset().top - $('.fp-body').offset().top + $('.fp-body').scrollTop() - 20 
                }, 500); 
            }, 100);
        }
    });

    // Đóng Popup Lọc
    $(document).on('click', '#btn-close-filter, .fp-overlay', function(e) {
        e.preventDefault(); e.stopPropagation();
        $('#filter-popup').removeClass('open'); 
        $('body').removeClass('no-scroll');
    });

    // Ajax Count (Đếm số lượng khi chọn)
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

    // Nút Bỏ chọn
    $('#btn-reset-filter').on('click', function(e) {
        e.preventDefault();
        $('.fp-option-item input').prop('checked', false);
        $('.fp-submit-btn').text('Xem kết quả').prop('disabled', false);
    });

    // Ajax Pagination (Phân trang không load lại)
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var page = 1;
        
        // Lấy số trang từ URL
        var match = url.match(/page\/(\d+)/);
        if(match) page = match[1]; 
        else { 
            var p = new URLSearchParams(url.split('?')[1]); 
            if(p.has('paged')) page = p.get('paged'); 
        }
        
        $('.shop-products-grid').css('opacity', 0.4);
        $('html, body').animate({ scrollTop: $('.shop-filter-bar').offset().top - 100 }, 500);
        
        $.post(relive_ajax.url, { 
            action: 'relive_load_products', 
            page: page, 
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
       5. CHI TIẾT SẢN PHẨM (SLIDER & FANCYBOX)
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
                    
                    // Auto Pause Video
                    var iframe = $('#prod-video-iframe');
                    if(iframe.length) {
                         if (!$(this.slides).eq(idx).hasClass('video-slide')) {
                            iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
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

        // FANCYBOX TRIGGER
        $(document).on('click', '.zoom-trigger', function(e) {
            e.preventDefault(); e.stopPropagation();
            if ($.fancybox) {
                var $links = $('[data-fancybox="product-gallery"]');
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
       6. POPUP CẤU HÌNH (ĐÓNG/MỞ 1 CHẠM)
       ========================================================================== */
    $(document).off('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs, #btn-close-specs, .sp-close, .specs-popup-overlay');
    
    $(document).on('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs', function(e) {
        e.preventDefault(); 
        $('#specs-popup').addClass('open'); 
        $('body').addClass('no-scroll');
    });

    $(document).on('click', '#btn-close-specs, .sp-close', function(e) {
        e.preventDefault();
        $('#specs-popup').removeClass('open'); 
        $('body').removeClass('no-scroll');
    });

    $(document).on('click', '.specs-popup-overlay', function(e) {
        if (e.target === this) {
            e.preventDefault();
            $('#specs-popup').removeClass('open'); 
            $('body').removeClass('no-scroll');
        }
    });
    
    // XỬ LÝ CUỘN MENU POPUP (SCROLL TO SECTION)
    $(document).on('click', '.sp-nav-item', function(e) {
        e.preventDefault();
        $('.sp-nav-item').removeClass('active');
        $(this).addClass('active');
        
        var targetId = $(this).attr('href');
        var $container = $('.sp-body'); // Cuộn trong body chính (Layout 1 cột)
        var $target = $(targetId);
        
        if ($target.length) {
            $container.animate({
                scrollTop: $target.offset().top - $container.offset().top + $container.scrollTop() - 50
            }, 400);
        }
    });

    /* ==========================================================================
       7. AUTO SWATCHES & PRICE UPDATE (Logic Biến thể & Giá FPT)
       ========================================================================== */
    if ($('.variations_form').length > 0) {
        var $form = $('.variations_form');

        // A. Tạo giao diện nút bấm
        $form.find('.variations tr').each(function() {
            var $select = $(this).find('select');
            if ($select.length === 0) return;

            var attributeName = $select.attr('id'); 
            var $swatchWrap = $('<div class="relive-swatches-wrap" data-attribute="'+attributeName+'"></div>');

            $select.find('option').each(function() {
                var val = $(this).val();
                if (!val) return; 
                var text = $(this).text();
                
                var $btn = $('<div class="swatch-item" data-value="' + val + '"></div>');
                
                var hasImage = false;
                if (typeof relive_swatches_json !== 'undefined' && relive_swatches_json[val]) {
                    var meta = relive_swatches_json[val];
                    if (meta.image) {
                        $btn.addClass('has-image');
                        $btn.append('<span class="swatch-img"><img src="'+meta.image+'" alt="'+text+'" /></span>');
                        $btn.append('<span class="swatch-text">'+text+'</span>');
                        hasImage = true;
                    } else if (meta.color) {
                        $btn.addClass('has-color');
                        $btn.append('<span class="swatch-dot" style="background:'+meta.color+'"></span>');
                        $btn.append('<span class="swatch-text">'+text+'</span>');
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
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    $select.val('').trigger('change');
                } else {
                    $(this).addClass('selected').siblings().removeClass('selected');
                    $select.val(value).trigger('change');
                }
            });
        });

        // B. Logic Gạch chéo (Disable)
        $form.on('woocommerce_update_variation_values', function() {
            $form.find('.variations select').each(function() {
                var $select = $(this);
                var $swatchWrap = $select.next('.relive-swatches-wrap');
                
                $swatchWrap.find('.swatch-item').each(function() {
                    var val = $(this).data('value');
                    var $option = $select.find('option[value="' + val + '"]');
                    
                    if ($option.length === 0 || $option.is(':disabled')) {
                        $(this).addClass('disabled').removeClass('selected');
                    } else {
                        $(this).removeClass('disabled');
                    }
                });
            });
        });
        
        // C. Cập nhật Giá FPT
        var $priceBlock = $('#fpt-price-dynamic');
        function formatMoney(n) { return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '₫'; }

        $form.on('found_variation', function(event, variation) {
            var price = variation.display_price;        
            var regular = variation.display_regular_price; 
            
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

        $('.reset_variations').on('click', function(){
            $('.swatch-item').removeClass('selected disabled');
        });
    }

    /* ==========================================================================
       8. XỬ LÝ NÚT MUA HÀNG (THÊM VÀO GIỎ)
       ========================================================================== */
    $(document).on('click', '.action-trigger', function(e) {
        e.preventDefault();
        var type = $(this).data('type');
        var $form = $('.variations_form');
        
        // Kiểm tra xem đã chọn biến thể chưa
        if ($form.length > 0) {
            var variationID = $form.find('input[name="variation_id"]').val();
            if (!variationID || variationID == 0) {
                alert('Vui lòng chọn đầy đủ Màu sắc và Dung lượng!');
                return;
            }
        }

        var $realBtn = $('.single_add_to_cart_button');
        if (type === 'buy-now') {
            $realBtn.trigger('click'); // Hoặc logic redirect
        } else {
            $realBtn.trigger('click'); // Thêm giỏ ajax
        }
    });
    // --- FANCYBOX TRIGGER ---
        $(document).on('click', '.zoom-trigger', function(e) {
            e.preventDefault(); e.stopPropagation();
            if ($.fancybox) {
                // [FIX] Selector khớp với HTML
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

});