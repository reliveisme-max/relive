jQuery(document).ready(function($) {

    /* ==========================================================================
       1. GLOBAL UI & MEGA MENU
       ========================================================================== */
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) $('.header.sticky').addClass('is-scrolling');
        else $('.header.sticky').removeClass('is-scrolling');
    });

    // Mega Menu Mobile
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

    // Tab Menu
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

    /* ==========================================================================
       2. SLIDERS (SWIPER)
       ========================================================================== */
    if (typeof Swiper !== 'undefined') {
        if ($('.cat-banner-swiper').length) {
            new Swiper('.cat-banner-swiper', { loop: true, autoplay: { delay: 4000 }, pagination: { el: '.cb-dots' }, navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' } });
        }
    }

    /* ==========================================================================
       3. PRODUCT DETAIL (SLIDER ẢNH CHI TIẾT)
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
                    
                    // Auto Pause Video khi chuyển slide
                    var iframe = $('#prod-video-iframe');
                    if(iframe.length) {
                         if (!$(this.slides).eq(idx).hasClass('video-slide')) {
                            iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                         }
                    }
                }
            }
        });

        // Click vào thumb bên dưới -> Chuyển slide chính
        $('.gallery-thumbs-nav-fpt .g-item').on('click', function(e) {
            if($(this).attr('href')) return;
            e.preventDefault();
            var idx = $(this).data('slide-index');
            if(idx !== undefined) productSwiper.slideTo(idx);
        });

        // --- FANCYBOX TRIGGER (FIX LỖI MỞ 2 LẦN) ---
        // Sử dụng thuộc tính mới data-relive-gallery để tránh xung đột với bản tự động của Fancybox
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
       4. POPUP CẤU HÌNH (FIXED - ĐÓNG 1 CHẠM ĂN NGAY)
       ========================================================================== */
    
    // Xóa sự kiện cũ (nếu có) để tránh lặp
    $(document).off('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs');
    $(document).off('click', '#btn-close-specs, .sp-close');
    $(document).off('click', '.specs-popup-overlay');

    // 1. Mở Popup
    $(document).on('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs', function(e) {
        e.preventDefault(); 
        $('#specs-popup').addClass('open'); 
        $('body').addClass('no-scroll');
    });

    // 2. Đóng khi bấm nút X
    $(document).on('click', '#btn-close-specs, .sp-close', function(e) {
        e.preventDefault();
        $('#specs-popup').removeClass('open'); 
        $('body').removeClass('no-scroll');
    });

    // 3. Đóng khi bấm ra vùng đen (Overlay)
    $(document).on('click', '.specs-popup-overlay', function(e) {
        // Chỉ đóng khi click đúng vào overlay, không đóng khi click vào nội dung trắng
        if (e.target === this) {
            e.preventDefault();
            $('#specs-popup').removeClass('open'); 
            $('body').removeClass('no-scroll');
        }
    });

    /* ==========================================================================
       5. AUTO SWATCHES (CÓ HÌNH ẢNH)
       ========================================================================== */
    if ($('.variations_form').length > 0) {
        $('.variations tr').each(function() {
            var $row = $(this);
            var $select = $row.find('select');
            if ($select.length === 0) return;

            var attributeName = $select.attr('id'); 
            var $swatchWrap = $('<div class="relive-swatches-wrap"></div>');

            $select.find('option').each(function() {
                var val = $(this).val();
                if (!val) return; 
                var text = $(this).text();
                
                // Tạo nút cơ bản
                var $btn = $('<div class="swatch-item" data-value="' + val + '"></div>');
                
                // --- LOGIC MỚI: KIỂM TRA ẢNH TỪ BIẾN JSON PHP TRUYỀN SANG ---
                var hasImage = false;
                if (typeof relive_swatches_json !== 'undefined' && relive_swatches_json[val]) {
                    var meta = relive_swatches_json[val];
                    
                    // Nếu có ảnh -> Chèn ảnh + Text
                    if (meta.image) {
                        $btn.addClass('has-image');
                        $btn.append('<span class="swatch-img"><img src="'+meta.image+'" alt="'+text+'" /></span>');
                        $btn.append('<span class="swatch-text">'+text+'</span>');
                        hasImage = true;
                    } 
                    // Nếu có màu -> Tô màu nền (dự phòng)
                    else if (meta.color) {
                        $btn.addClass('has-color');
                        $btn.append('<span class="swatch-dot" style="background:'+meta.color+'"></span>');
                        $btn.append('<span class="swatch-text">'+text+'</span>');
                        hasImage = true;
                    }
                }

                // Nếu không có ảnh/màu gì cả thì chỉ hiện Text
                if (!hasImage) {
                    $btn.text(text);
                }

                $swatchWrap.append($btn);
            });

            $select.after($swatchWrap).hide();

            $swatchWrap.on('click', '.swatch-item', function() {
                var value = $(this).data('value');
                $(this).addClass('selected').siblings().removeClass('selected');
                $select.val(value).trigger('change');
            });
            
            $select.on('change', function() {
                if(!$(this).val()) $swatchWrap.find('.swatch-item').removeClass('selected');
            });
        });
    }

});