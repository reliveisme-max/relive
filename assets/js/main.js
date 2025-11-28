jQuery(document).ready(function($) {

    /* ==========================================================================
       1. GLOBAL UI (STICKY HEADER, SCROLL)
       ========================================================================== */
    
    // Sticky Header
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.header.sticky').addClass('is-scrolling');
        } else {
            $('.header.sticky').removeClass('is-scrolling');
        }
    });

    // Smooth Scroll
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
       2. MEGA MENU & MOBILE NAVIGATION
       ========================================================================== */

    // MỞ Menu Mobile
    $('.header-cat-btn').on('click', function(e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            $('.mega-menu-wrapper').addClass('open');
            $('body').addClass('no-scroll');
        }
    });

    // ĐÓNG Menu Mobile
    $(document).on('click', '.m-close, .mega-overlay', function() {
        $('.mega-menu-wrapper').removeClass('open');
        $('body').removeClass('no-scroll');
    });

    // CHUYỂN TAB CỘT TRÁI
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
        var $targetPane = $('#' + uniqueID);
        if ($targetPane.length) {
            $targetPane.addClass('active');
        }
    });

    // FIX LỖI LINK MOBILE
    $(document).on('click', '.brand-item, .sub-cat-item, .mini-prod-item, .btn-view-all', function(e) {
        if ($(window).width() < 992) {
            var url = $(this).attr('href');
            if (url && url !== '#' && url !== 'javascript:;') {
                window.location.href = url;
            }
        }
    });


    /* ==========================================================================
       3. SLIDERS (SWIPER CONFIG)
       ========================================================================== */
    
    if (typeof Swiper !== 'undefined') {
        // Main Slider
        if ($('.main-slider').length) {
            new Swiper('.main-slider', {
                loop: true, speed: 800,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.swiper-pagination', clickable: true },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            });
        }

        // Category Banner Slider
        if ($('.cat-banner-swiper').length) {
            new Swiper('.cat-banner-swiper', {
                slidesPerView: 1, loop: true, speed: 600,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.cb-dots', clickable: true },
                navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' },
            });
        }

        // Sub-Category Slider
        $('.sub-cat-swiper-wrap').each(function() {
            var $container = $(this).find('.sub-cat-slider');
            var $nextBtn = $(this).find('.sc-next');
            var $prevBtn = $(this).find('.sc-prev');

            if ($container.length) {
                new Swiper($container[0], {
                    slidesPerView: 3, spaceBetween: 10,
                    observer: true, observeParents: true,
                    touchStartPreventDefault: false,
                    navigation: { nextEl: $nextBtn[0], prevEl: $prevBtn[0] },
                    breakpoints: {
                        1200: { slidesPerView: 5, spaceBetween: 15 },
                        992:  { slidesPerView: 4, spaceBetween: 10 },
                        768:  { slidesPerView: 3, spaceBetween: 10 }
                    }
                });
            }
        });

        // Category Grid Slider
        if ($('.cat-slider').length) {
            $('.cat-slider').each(function() {
                var $el = $(this);
                new Swiper($el[0], {
                    slidesPerView: 4, spaceBetween: 10,
                    navigation: { nextEl: '.cat-next', prevEl: '.cat-prev' },
                    breakpoints: {
                        1024: { slidesPerView: 8, spaceBetween: 15 },
                        768:  { slidesPerView: 6, spaceBetween: 15 }
                    }
                });
            });
        }
    }


    /* ==========================================================================
       4. PRODUCT FILTERS (BỘ LỌC TGDĐ)
       ========================================================================== */

    // Mở Popup
    $(document).on('click', '#btn-open-filter, .quick-btn', function(e) {
        e.preventDefault();
        $('#filter-popup').addClass('open');
        $('body').addClass('no-scroll');
        
        var targetID = $(this).data('target');
        if(targetID && $('#' + targetID).length) {
            var $target = $('#' + targetID);
            var $container = $('.fp-body');
            setTimeout(function(){
                $container.animate({
                    scrollTop: $target.offset().top - $container.offset().top + $container.scrollTop() - 20
                }, 500);
            }, 100);
        }
    });

    // Đóng Popup
    $(document).on('click', '#btn-close-filter, .fp-overlay', function() {
        $('#filter-popup').removeClass('open');
        $('body').removeClass('no-scroll');
    });

    // Dropdown Filter (PC)
    $(document).on('click', '.dropdown-toggle', function(e) {
        e.preventDefault(); e.stopPropagation();
        var $parent = $(this).parent('.filter-dropdown-wrap');
        $('.filter-dropdown-wrap').not($parent).removeClass('open');
        $parent.toggleClass('open');
        if ($(window).width() < 992) {
            $('body').toggleClass('no-scroll', $parent.hasClass('open'));
        }
    });

    // Đóng khi click ngoài
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter-dropdown-wrap').length) {
            $('.filter-dropdown-wrap').removeClass('open');
            if (!$('#filter-popup').hasClass('open')) $('body').removeClass('no-scroll');
        }
    });
    
    $(document).on('click', '.fd-close, .fd-overlay', function() {
        $(this).closest('.filter-dropdown-wrap').removeClass('open');
        if (!$('#filter-popup').hasClass('open')) $('body').removeClass('no-scroll');
    });

    // --- AJAX LIVE COUNT ---
    var filterTimer;
    $(document).on('change', '.fp-content input, .fp-content select', function() {
        var $form = $(this).closest('form');
        var $btnSubmit = $form.find('.fp-submit-btn');
        
        $btnSubmit.css('opacity', '0.7').text('Đang tính...');
        
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            var formData = $form.serialize();
            $.ajax({
                url: relive_ajax.url,
                type: 'POST',
                data: {
                    action: 'relive_get_filter_count',
                    form_data: formData,
                    nonce: relive_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var count = response.data.count;
                        if (count > 0) {
                            $btnSubmit.text('Xem ' + count + ' kết quả');
                            $btnSubmit.prop('disabled', false).css('opacity', '1');
                        } else {
                            $btnSubmit.text('Không có kết quả');
                            $btnSubmit.prop('disabled', true).css('opacity', '0.5');
                        }
                    }
                }
            });
        }, 300);
    });

    // --- NÚT BỎ CHỌN ---
    $('#btn-reset-filter').on('click', function() {
        $('.fp-option-item input[type="checkbox"]').prop('checked', false);
        $('.fp-submit-btn').text('Xem kết quả').prop('disabled', false).css('opacity', '1');
    });

    // --- AJAX PAGINATION CLICK (FIXED) ---
    $(document).on('click', '.pagination .page-link', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var url = $btn.attr('href');
        
        var pageMatch = url.match(/page\/(\d+)/);
        var page = 1;
        if (pageMatch) {
            page = pageMatch[1];
        } else {
            var urlParams = new URLSearchParams(url.split('?')[1]);
            if (urlParams.has('paged')) page = urlParams.get('paged');
        }

        var $form = $('#filter-popup form');
        var formData = $form.serialize(); 
        var currentParams = new URLSearchParams(window.location.search);
        var orderby = currentParams.get('orderby') || 'date';

        $('.shop-products-grid').css('opacity', '0.4');
        $('html, body').animate({ scrollTop: $('.shop-filter-bar').offset().top - 100 }, 500);

        $.ajax({
            url: relive_ajax.url,
            type: 'POST',
            data: {
                action: 'relive_load_products',
                page: page,
                form_data: formData,
                orderby: orderby,
                nonce: relive_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.shop-products-grid').html(response.data.products).css('opacity', '1');
                    $('.shop-pagination').html(response.data.pagination);
                    $('.shop-result-count').html(response.data.result_count);
                    
                    var newUrl = url;
                    if (window.location.search && !newUrl.includes('?')) {
                         newUrl += window.location.search;
                    }
                    window.history.pushState({path: newUrl}, '', newUrl);
                } else {
                    $('.shop-products-grid').css('opacity', '1');
                    alert('Lỗi tải trang. Vui lòng thử lại.');
                }
            }
        });
    });

    /* ==========================================================================
       5. PRODUCT GALLERY SLIDER & FANCYBOX (FIX LỖI POPUP)
       ========================================================================== */

    if ($('.product-main-slider').length) {
        var productSwiper = new Swiper('.product-main-slider', {
            loop: false,
            spaceBetween: 10,
            navigation: { nextEl: '.p-next', prevEl: '.p-prev' },
            on: {
                slideChange: function () {
                    var index = this.activeIndex;
                    var $slides = $(this.slides);
                    var $currentSlide = $slides.eq(index);
                    var $iframe = $('#prod-video-iframe');

                    // Cập nhật nút Active
                    $('.gallery-thumbs-nav-fpt .g-item').removeClass('active');
                    var $targetBtn = $('.gallery-thumbs-nav-fpt .g-item[data-slide-index="' + index + '"]');
                    if ($targetBtn.length) $targetBtn.addClass('active');
                    else $('.gallery-thumbs-nav-fpt .g-item').first().addClass('active');

                    // Auto Pause Video khi lướt qua
                    if ($iframe.length) {
                        if (!$currentSlide.hasClass('video-slide')) {
                            $iframe[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                        }
                    }
                }
            }
        });

        // Click Thumb
        $('.gallery-thumbs-nav-fpt .g-item').on('click', function(e) {
            if ($(this).attr('href')) return;
            e.preventDefault();
            var slideIndex = $(this).data('slide-index');
            if (slideIndex !== undefined) productSwiper.slideTo(slideIndex);
        });

        // --- KÍCH HOẠT LIGHTBOX (FANCYBOX LAZY INIT) ---
        // Dùng .on('click') để đảm bảo chạy kể cả khi thư viện load chậm
        $(document).on('click', '[data-fancybox="product-gallery"]', function(e) {
            // Kiểm tra xem thư viện đã load chưa
            if ($.fancybox) {
                // Nếu chưa init, init ngay
                if (!$(this).hasClass('fancybox-initialized')) {
                    e.preventDefault();
                    var index = $('[data-fancybox="product-gallery"]').index(this);
                    
                    $.fancybox.open($('[data-fancybox="product-gallery"]'), {
                        loop: true,
                        animationEffect: "zoom-in-out",
                        transitionEffect: "slide",
                        buttons: ["zoom", "slideShow", "fullScreen", "thumbs", "close"],
                        protect: true
                    }, index);
                }
            } else {
                console.error('Fancybox library not loaded!');
            }
        });
    }

    // --- POPUP CẤU HÌNH (SLIDE TỪ PHẢI) ---
    
    // Mở Popup
    $(document).on('click', '#btn-open-specs, #btn-open-specs-2, .view-all-specs', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $popup = $('#specs-popup');
        if ($popup.length) {
            $popup.addClass('open'); 
            $('body').addClass('no-scroll');
        } else {
            console.log('Lỗi: Không tìm thấy ID #specs-popup trong HTML');
        }
    });

    // Đóng Popup
    $(document).on('click', '#btn-close-specs, .specs-popup-overlay', function(e) {
        if (e.target === this || $(e.target).closest('.sp-close').length) {
            $('#specs-popup').removeClass('open');
            $('body').removeClass('no-scroll');
        }
    });

});