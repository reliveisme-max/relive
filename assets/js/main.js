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

});