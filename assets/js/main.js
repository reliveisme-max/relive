jQuery(document).ready(function($) {

    /* ==========================================================================
       1. GLOBAL UI (STICKY HEADER, SCROLL)
       ========================================================================== */
    
    // Sticky Header: Thêm class khi cuộn trang
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.header.sticky').addClass('is-scrolling');
        } else {
            $('.header.sticky').removeClass('is-scrolling');
        }
    });

    // Smooth Scroll cho các link neo (#)
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

    // MỞ Menu Mobile (Khi bấm nút "Danh mục")
    $('.header-cat-btn').on('click', function(e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            $('.mega-menu-wrapper').addClass('open');
            $('body').addClass('no-scroll'); // Khóa cuộn
        }
    });

    // ĐÓNG Menu Mobile (Bấm nút X hoặc Nền đen)
    $(document).on('click', '.m-close, .mega-overlay', function() {
        $('.mega-menu-wrapper').removeClass('open');
        $('body').removeClass('no-scroll'); // Mở khóa cuộn
    });

    // CHUYỂN TAB CỘT TRÁI (LOGIC THÔNG MINH)
    // PC: Hover chuyển tab. Mobile: Bấm chuyển tab, bấm lần nữa vào link.
    $(document).on('mouseenter click', '.cat-item-left', function(e) {
        var isMobile = $(window).width() < 992;

        // PC: Chỉ xử lý Hover, bỏ qua Click
        if (!isMobile && e.type === 'click') return; 
        
        // Mobile: Bỏ qua Hover
        if (isMobile && e.type === 'mouseenter') return;
        
        // Mobile Logic: Double Tap to Go
        if (isMobile && e.type === 'click') {
            // Nếu đã active rồi thì cho phép chạy link
            if ($(this).hasClass('active')) return;
            
            // Nếu chưa active thì chặn link để mở tab trước
            e.preventDefault(); 
        }

        // Xử lý giao diện Active
        $('.cat-item-left').removeClass('active');
        $(this).addClass('active');

        // Hiện nội dung cột phải tương ứng
        var uniqueID = $(this).data('id');
        $('.cat-pane').removeClass('active');
        var $targetPane = $('#' + uniqueID);
        if ($targetPane.length) {
            $targetPane.addClass('active');
        }
    });

    // FIX LỖI KHÔNG BẤM ĐƯỢC LINK TRÊN MOBILE (QUAN TRỌNG)
    // Ép trình duyệt chuyển trang khi bấm vào các item con
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

        // A. MAIN HOME SLIDER (Slider chính trang chủ)
        if ($('.main-slider').length) {
            new Swiper('.main-slider', {
                loop: true,
                speed: 800,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.swiper-pagination', clickable: true },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            });
        }

        // B. CATEGORY BANNER SLIDER (Slider FPT Style ở trang danh mục)
        if ($('.cat-banner-swiper').length) {
            new Swiper('.cat-banner-swiper', {
                slidesPerView: 1,
                loop: true,
                speed: 600,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.cb-dots', clickable: true },
                navigation: { nextEl: '.cb-next', prevEl: '.cb-prev' },
            });
        }

        // C. SUB-CATEGORY SLIDER (Slider tròn trong Mega Menu)
        // Dùng .each để khởi tạo riêng biệt cho từng Tab, tránh xung đột nút bấm
        $('.sub-cat-swiper-wrap').each(function() {
            var $container = $(this).find('.sub-cat-slider');
            var $nextBtn = $(this).find('.sc-next');
            var $prevBtn = $(this).find('.sc-prev');

            if ($container.length) {
                new Swiper($container[0], {
                    slidesPerView: 3, // Mobile hiện 3
                    spaceBetween: 10,
                    observer: true,       // Tự cập nhật khi hiện từ display:none
                    observeParents: true,
                    touchStartPreventDefault: false, // Fix lỗi chạm link mobile
                    navigation: {
                        nextEl: $nextBtn[0],
                        prevEl: $prevBtn[0],
                    },
                    breakpoints: {
                        1200: { slidesPerView: 5, spaceBetween: 15 },
                        992:  { slidesPerView: 4, spaceBetween: 10 },
                        768:  { slidesPerView: 3, spaceBetween: 10 }
                    }
                });
            }
        });

        // D. CATEGORY GRID SLIDER (Slider danh mục trang chủ)
        if ($('.cat-slider').length) {
            // Lấy ID động hoặc class chung
            $('.cat-slider').each(function() {
                var $el = $(this);
                // Config cơ bản, có thể tùy biến thêm nếu cần
                new Swiper($el[0], {
                    slidesPerView: 4,
                    spaceBetween: 10,
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

    // MỞ/ĐÓNG POPUP LỌC (Nút "Lọc" hoặc nút nhanh)
    $(document).on('click', '#btn-open-filter, .quick-btn', function(e) {
        e.preventDefault();
        $('#filter-popup').addClass('open');
        $('body').addClass('no-scroll');
        
        // Scroll tới mục tương ứng nếu bấm nút nhanh
        var targetID = $(this).data('target');
        if(targetID && $('#' + targetID).length) {
            var $target = $('#' + targetID);
            var $container = $('.fp-body');
            // Tính toán vị trí scroll
            setTimeout(function(){
                $container.animate({
                    scrollTop: $target.offset().top - $container.offset().top + $container.scrollTop() - 20
                }, 500);
            }, 100);
        }
    });

    // Đóng Popup Filter
    $(document).on('click', '#btn-close-filter, .fp-overlay', function() {
        $('#filter-popup').removeClass('open');
        $('body').removeClass('no-scroll');
    });

    // DROPDOWN FILTER (Bộ lọc xổ xuống trên PC)
    $(document).on('click', '.dropdown-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $parent = $(this).parent('.filter-dropdown-wrap');
        
        // Đóng các dropdown khác
        $('.filter-dropdown-wrap').not($parent).removeClass('open');
        
        // Toggle cái hiện tại
        $parent.toggleClass('open');
        
        // Trên mobile thì khóa cuộn body khi mở dropdown
        if ($(window).width() < 992) {
            $('body').toggleClass('no-scroll', $parent.hasClass('open'));
        }
    });

    // Đóng Dropdown khi click ra ngoài
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter-dropdown-wrap').length) {
            $('.filter-dropdown-wrap').removeClass('open');
            // Chỉ mở khóa cuộn nếu không có popup nào khác đang mở
            if (!$('#filter-popup').hasClass('open')) {
                $('body').removeClass('no-scroll');
            }
        }
    });
    
    // Nút đóng trong Dropdown Mobile
    $(document).on('click', '.fd-close, .fd-overlay', function() {
        $(this).closest('.filter-dropdown-wrap').removeClass('open');
        if (!$('#filter-popup').hasClass('open')) {
            $('body').removeClass('no-scroll');
        }
    });

    // --- AJAX LIVE COUNT (CẬP NHẬT SỐ LƯỢNG KHI LỌC) ---
    var filterTimer;
    
    // Bắt sự kiện khi thay đổi bất kỳ input nào trong popup
    $(document).on('change', '.fp-content input, .fp-content select', function() {
        var $form = $(this).closest('form');
        var $btnSubmit = $form.find('.fp-submit-btn');
        var originalText = 'Xem kết quả';
        
        // Hiện hiệu ứng đang tải...
        $btnSubmit.css('opacity', '0.7').text('Đang tính...');
        
        // Dùng Timeout để tránh gửi quá nhiều request nếu bấm liên tục (Debounce)
        clearTimeout(filterTimer);
        filterTimer = setTimeout(function() {
            
            var formData = $form.serialize(); // Lấy toàn bộ dữ liệu đã chọn
            
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
                        // Cập nhật text nút bấm
                        if (count > 0) {
                            $btnSubmit.text('Xem ' + count + ' kết quả');
                            $btnSubmit.prop('disabled', false).css('opacity', '1');
                        } else {
                            $btnSubmit.text('Không có kết quả');
                            $btnSubmit.prop('disabled', true).css('opacity', '0.5'); // Khóa nút nếu = 0
                        }
                    }
                }
            });
            
        }, 300); // Đợi 300ms sau khi bấm mới gửi request
    });
    // --- NÚT BỎ CHỌN (RESET FILTER) ---
    $('#btn-reset-filter').on('click', function() {
        // 1. Bỏ tick tất cả checkbox
        $('.fp-option-item input[type="checkbox"]').prop('checked', false);
        
        // 2. Reset text nút Submit về mặc định
        $('.fp-submit-btn').text('Xem kết quả').prop('disabled', false).css('opacity', '1');
        
        // 3. (Tùy chọn) Reload lại trang gốc để xóa bộ lọc
        // window.location.href = window.location.pathname;
    });

});