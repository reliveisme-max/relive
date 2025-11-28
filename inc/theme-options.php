<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', 'relive_register_theme_options');

function relive_register_theme_options()
{
    // Khởi tạo Menu Cấu hình
    $theme_options = Container::make('theme_options', __('Cấu hình Theme', 'relive'))
        ->set_page_menu_position(2)
        ->set_icon('dashicons-admin-generic');

    // =========================================================
    // TAB 1: THÔNG TIN CHUNG (Logo, Liên hệ)
    // =========================================================
    $theme_options->add_tab(__('Thông tin chung', 'relive'), array(
        Field::make('image', 'site_logo', 'Logo Website')
            ->set_value_type('url')
            ->set_help_text('Kích thước khuyên dùng: 200x50px'),

        Field::make('text', 'contact_phone', 'Hotline')
            ->set_attribute('placeholder', 'Ví dụ: 0987...'),

        Field::make('text', 'contact_zalo', 'Link Zalo'),

        Field::make('text', 'contact_email', 'Email liên hệ'),
    ));

    // =========================================================
    // TAB 2: HEADER (ĐẦU TRANG) - MỚI THÊM
    // =========================================================
    $theme_options->add_tab(__('Header (Đầu trang)', 'relive'), array(
        Field::make('color', 'header_bg_color', 'Màu nền Header')
            ->set_default_value('#ffffff'),

        Field::make('text', 'header_height', 'Chiều cao Header (px)')
            ->set_default_value(70)
            ->set_help_text('Nhập số. Ví dụ: 70'), // Không dùng type=number để tránh lỗi

        Field::make('color', 'header_text_color', 'Màu chữ Menu & Icon')
            ->set_default_value('#333333'),

        Field::make('checkbox', 'header_sticky', 'Dính Header khi cuộn?')
            ->set_default_value(true),
    ));

    // =========================================================
    // TAB 3: CỬA HÀNG (WOOCOMMERCE)
    // =========================================================
    $theme_options->add_tab(__('Cửa hàng', 'relive'), array(
        Field::make('text', 'shop_per_page', 'Số sản phẩm trên 1 trang')
            ->set_default_value(12),

        Field::make('checkbox', 'shop_show_sidebar', 'Hiển thị Sidebar ở trang danh mục?')
            ->set_default_value(true),
    ));

    // =========================================================
    // TAB 4: CHÂN TRANG (FOOTER)
    // =========================================================
    $theme_options->add_tab(__('Chân trang', 'relive'), array(
        Field::make('rich_text', 'footer_copyright', 'Nội dung bản quyền')
            ->set_default_value('&copy; 2025 Bản quyền thuộc về Relive.'),

        Field::make('textarea', 'footer_scripts', 'Script cuối trang')
            ->set_help_text('Chèn mã Google Analytics, Chat, Pixel... vào đây.'),
    ));
    // =========================================================
    // TAB 5: MEGA MENU (CẤU HÌNH THỦ CÔNG)
    // =========================================================
    $theme_options->add_tab(__('Mega Menu (Dọc)', 'relive'), array(
        Field::make('complex', 'mega_menu_items', 'Danh sách Menu')
            ->set_layout('tabbed-vertical')
            ->add_fields(array(
                // 1. CẤU HÌNH MENU CHA (Cột Trái)
                Field::make('text', 'title', 'Tên Menu (VD: Điện thoại)')
                    ->set_width(50),
                Field::make('text', 'link', 'Link trỏ đến (VD: /danh-muc/dien-thoai/)')
                    ->set_width(50),

                Field::make('select', 'icon_type', 'Loại Icon')
                    ->set_options(array('font' => 'Font Icon', 'image' => 'Ảnh'))
                    ->set_width(50),
                Field::make('text', 'icon_font', 'Class FontAwesome (VD: fas fa-mobile)')
                    ->set_conditional_logic(array(array('field' => 'icon_type', 'value' => 'font')))
                    ->set_width(50),
                Field::make('image', 'icon_img', 'Upload Ảnh Icon')
                    ->set_conditional_logic(array(array('field' => 'icon_type', 'value' => 'image')))
                    ->set_width(50),

                // 2. CẤU HÌNH NỘI DUNG (Cột Phải)
                Field::make('separator', 'sep_brands', '1. Thương hiệu (Logo)'),
                Field::make('complex', 'brands', 'Danh sách Thương hiệu')
                    ->set_layout('tabbed-horizontal')
                    ->add_fields(array(
                        Field::make('image', 'brand_logo', 'Logo')->set_width(40),
                        Field::make('text', 'brand_link', 'Link')->set_width(60),
                    )),

                Field::make('separator', 'sep_cats', '2. Danh mục con (Text)'),
                Field::make('association', 'sub_cats', 'Chọn Danh mục hiển thị')
                    ->set_types(array(
                        array('type' => 'term', 'taxonomy' => 'product_cat'),
                    )),

                Field::make('separator', 'sep_prods', '3. Sản phẩm nổi bật'),
                Field::make('association', 'manual_products', 'Chọn Sản phẩm')
                    ->set_types(array(
                        array('type' => 'post', 'post_type' => 'product'),
                    )),

                Field::make('text', 'view_all_text', 'Chữ nút xem thêm (VD: Xem tất cả điện thoại)')
                    ->set_default_value('Xem tất cả'),
            ))
            ->set_header_template('<%- title %>') // Hiển thị tên menu ra ngoài cho dễ nhìn
    ));
}
