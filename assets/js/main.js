jQuery(document).ready(function($) {
    
    // 1. MOBILE MENU TOGGLE
    // Khi bấm nút 3 gạch
    $('.mobile-nav-toggle').on('click', function(e) {
        e.preventDefault();
        $('body').toggleClass('mobile-menu-open');
    });

    // Bấm ra ngoài (nền tối) để đóng menu
    $(document).on('click', function(e) {
        if ( $('body').hasClass('mobile-menu-open') ) {
            // Nếu click không trúng menu và không trúng nút toggle
            if ( !$(e.target).closest('.main-navigation').length && !$(e.target).closest('.mobile-nav-toggle').length ) {
                $('body').removeClass('mobile-menu-open');
            }
        }
    });

    // 2. STICKY HEADER (Thêm class khi cuộn)
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.header.sticky').addClass('is-scrolling');
        } else {
            $('.header.sticky').removeClass('is-scrolling');
        }
    });

    // 3. SMOOTH SCROLL (Cuộn mượt)
    $('a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 80
                }, 800);
                return false;
            }
        }
    });

    // 4. VERTICAL MENU TOGGLE (Mobile & PC Click)
    $('.btn-cat-menu').on('click', function(e) {
        e.preventDefault();
        $(this).parent('.vertical-menu-wrap').toggleClass('active');
    });

    // --- XỬ LÝ MENU MOBILE (SPLIT VIEW) ---
    
    // 1. Mở Menu
    $('.btn-cat-menu').on('click', function(e) {
        e.preventDefault();
        $('.vertical-menu-wrap').addClass('active');
        $('body').addClass('mobile-menu-open');
        
        // Mặc định kích hoạt tab đầu tiên nếu chưa có cái nào active
        if ( $(window).width() < 769 ) {
            if ( !$('.v-menu > li.active').length ) {
                $('.v-menu > li:first-child').addClass('active');
            }
        }
    });

    // 2. Đóng Menu (Nút X hoặc Bấm ra ngoài)
    $('.mobile-menu-close, .vertical-overlay').on('click', function() {
        $('.vertical-menu-wrap').removeClass('active');
        $('body').removeClass('mobile-menu-open');
    });

    // 3. Chuyển Tab (Khi bấm vào cột trái)
    $('.v-menu > li > a').on('click', function(e) {
        // Chỉ chạy logic này trên Mobile
        if ($(window).width() < 769) {
            // Nếu item này có menu con thì mới chặn chuyển trang để mở tab
            if ( $(this).parent().hasClass('menu-item-has-children') ) {
                e.preventDefault();
                
                // Xóa active cũ, thêm active mới
                $('.v-menu > li').removeClass('active');
                $(this).parent('li').addClass('active');
            }
        }
    });

});