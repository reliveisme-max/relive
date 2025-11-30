<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', 'relive_product_fields');
function relive_product_fields()
{
    Container::make('post_meta', 'Cấu hình Sản phẩm (FPT Style)')
        ->where('post_type', '=', 'product')
        ->add_fields(array(

            // TAB 1: THƯ VIỆN ẢNH & VIDEO
            Field::make('separator', 'sep_gallery', '1. Thư viện ảnh & Video'),
            Field::make('image', 'prod_featured_image', 'Ảnh Nổi Bật (Slide đầu tiên)')->set_value_type('url'),
            Field::make('text', 'prod_video', 'Link Video Youtube'),
            Field::make('media_gallery', 'box_images', 'Ảnh mở hộp / Phụ kiện'),
            Field::make('media_gallery', 'real_images', 'Ảnh thực tế'),

            // TAB 2: THÔNG SỐ KỸ THUẬT
            Field::make('separator', 'sep_specs', '2. Thông số kỹ thuật'),
            Field::make('image', 'spec_feature_image', 'Ảnh mô tả tính năng')->set_value_type('url'),
            Field::make('complex', 'fpt_specs_groups', 'Danh sách Nhóm thông số')
                ->set_layout('tabbed-vertical')
                ->add_fields(array(
                    Field::make('text', 'group_name', 'Tên nhóm (VD: Màn hình)')->set_width(100),
                    Field::make('complex', 'group_items', 'Chi tiết thông số')
                        ->set_layout('tabbed-horizontal')
                        ->add_fields(array(
                            Field::make('text', 'label', 'Tên (VD: Kích thước)')->set_width(40),
                            Field::make('text', 'spec_val', 'Giá trị (VD: 6.9 inch)')->set_width(40),
                            Field::make('checkbox', 'is_highlight', 'Hiện ở mục nổi bật?')->set_width(20),
                            Field::make('text', 'icon', 'Icon (VD: fas fa-chip)')->set_width(100),
                        ))
                        ->set_header_template('<%- label %>: <%- spec_val %>')
                ))
                ->set_header_template('<%- group_name %>'),

            // TAB 3: KHUYẾN MÃI
            Field::make('separator', 'sep_promo', '3. Khuyến mãi (FPT Style)'),
            Field::make('complex', 'fpt_promotions', 'Các nhóm khuyến mãi')
                ->set_layout('tabbed-vertical')
                ->add_fields(array(
                    Field::make('text', 'promo_title', 'Tiêu đề nhóm (VD: Khuyến mãi 1)'),
                    Field::make('complex', 'promo_items', 'Danh sách ưu đãi')
                        ->set_layout('tabbed-horizontal')
                        ->add_fields(array(
                            Field::make('text', 'content', 'Nội dung'),
                            Field::make('text', 'link', 'Link chi tiết (Nếu có)'),
                        ))
                ))
                ->set_header_template('<%- promo_title %>'),

            // TAB 4: SẢN PHẨM MUA KÈM (CẬP NHẬT MỚI)
            Field::make('separator', 'sep_bought_together', '4. Mua kèm giá sốc'),
            Field::make('complex', 'fpt_bought_together', 'Danh sách Mua kèm')
                ->set_layout('tabbed-horizontal')
                ->add_fields(array(
                    Field::make('association', 'product_assoc', 'Chọn sản phẩm')
                        ->set_types(array(array('type' => 'post', 'post_type' => 'product')))
                        ->set_max(1)
                        ->set_width(50),
                    Field::make('text', 'percent_sale', '% Giảm giá (VD: 20)')
                        ->set_width(50)
                        ->set_attribute('type', 'number'),
                ))
                ->set_header_template('<%- percent_sale %> %'),

            // TAB 5: MÃ GIẢM GIÁ (CẬP NHẬT: Chọn từ WooCommerce Coupon)
            Field::make('separator', 'sep_coupons', '5. Mã giảm giá thêm'),

            // Dùng Association để tìm kiếm và chọn Coupon có sẵn
            Field::make('association', 'product_coupons', 'Chọn Mã giảm giá (Đã tạo trong Marketing > Coupons)')
                ->set_types(array(
                    array(
                        'type'      => 'post',
                        'post_type' => 'shop_coupon', // Post type của Coupon WooCommerce
                    )
                ))
        ));
}

// ... (GIỮ NGUYÊN CÁC HÀM BÊN DƯỚI NHƯ CŨ: relive_term_fields, relive_cat_banner_fields...)
add_action('carbon_fields_register_fields', 'relive_term_fields');
function relive_term_fields()
{
    if (! function_exists('wc_get_attribute_taxonomies')) return;
    $attributes = wc_get_attribute_taxonomies();
    $slugs = array();
    if ($attributes) {
        foreach ($attributes as $tax) {
            $slugs[] = wc_attribute_taxonomy_name($tax->attribute_name);
        }
    }
    if (! empty($slugs)) {
        $container = Container::make('term_meta', 'Cấu hình Biến thể (Swatches)');
        foreach ($slugs as $index => $slug) {
            if ($index === 0) {
                $container->where('term_taxonomy', '=', $slug);
            } else {
                $container->or_where('term_taxonomy', '=', $slug);
            }
        }
        $container->add_fields(array(
            Field::make('color', 'attribute_color', 'Màu sắc (Nếu là biến thể màu)'),
            Field::make('image', 'attribute_image', 'Ảnh đại diện (Thay thế màu)'),
        ));
    }
}

add_action('carbon_fields_register_fields', 'relive_cat_banner_fields');
function relive_cat_banner_fields()
{
    Container::make('term_meta', 'Banner Quảng Cáo')
        ->where('term_taxonomy', '=', 'product_cat')
        ->add_fields(array(
            Field::make('complex', 'cat_banner_slider', 'Danh sách Banner')
                ->set_layout('tabbed-horizontal')
                ->add_fields(array(Field::make('image', 'img_pc', 'Ảnh Banner')->set_value_type('url'), Field::make('text', 'link', 'Link liên kết')))
        ));
}

add_action('carbon_fields_register_fields', 'relive_register_builder');
function relive_register_builder()
{
    Container::make('post_meta', __('PAGE BUILDER', 'relive'))
        ->where('post_type', '=', 'page')
        ->add_fields(array(
            Field::make('complex', 'builder_blocks', __('Nội dung trang', 'relive'))
                ->set_layout('tabbed-vertical')
                ->set_collapsed(true)
                ->add_fields('slider', __('Slider Ảnh'), array(
                    Field::make('complex', 'slides', 'Slides')->add_fields(array(Field::make('image', 'image', 'Ảnh'), Field::make('text', 'link', 'Link'), Field::make('text', 'thumb_title', 'Tab Tiêu đề'), Field::make('text', 'thumb_desc', 'Tab Mô tả'))),
                    Field::make('select', 'width_mode', 'Độ rộng')->set_options(array('container' => 'Container', 'full' => 'Full Width')),
                    Field::make('text', 'height', 'Chiều cao')->set_default_value(400),
                    Field::make('select', 'pagi_style', 'Kiểu phân trang')->set_options(array('dots' => 'Chấm', 'thumbs_text' => 'Tab nội dung')),
                    Field::make('checkbox', 'arrows_hide_mobile', 'Ẩn mũi tên Mobile?')->set_default_value(true),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                ))
                ->add_fields('category_icons', __('Danh mục'), array(
                    Field::make('text', 'title', 'Tiêu đề'),
                    Field::make('association', 'selected_cats', 'Chọn danh mục')->set_types(array(array('type' => 'term', 'taxonomy' => 'product_cat'))),
                    Field::make('select', 'cat_rows', 'Số hàng')->set_options(array('1' => '1 Hàng', '2' => '2 Hàng')),
                    Field::make('text', 'col_desk', 'Cột Desktop')->set_default_value(8),
                    Field::make('text', 'col_mob', 'Cột Mobile')->set_default_value(4),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                ))
                ->add_fields('product_grid', __('Sản phẩm'), array(
                    Field::make('text', 'title', 'Tiêu đề'),
                    Field::make('text', 'limit', 'Số lượng')->set_default_value(8),
                    Field::make('select', 'col', 'Số cột')->set_options(array('4' => '4', '5' => '5')),
                    Field::make('select', 'filter_type', 'Nguồn')->set_options(array('auto' => 'Tự động', 'cat' => 'Danh mục', 'manual' => 'Thủ công')),
                    Field::make('select', 'auto_type', 'Loại')->set_options(array('recent' => 'Mới nhất', 'sale' => 'Khuyến mãi', 'best' => 'Bán chạy')),
                    Field::make('association', 'selected_cats', 'Chọn Danh mục')->set_conditional_logic(array(array('field' => 'filter_type', 'value' => 'cat')))->set_types(array(array('type' => 'term', 'taxonomy' => 'product_cat'))),
                    Field::make('association', 'selected_ids', 'Chọn Sản phẩm')->set_conditional_logic(array(array('field' => 'filter_type', 'value' => 'manual')))->set_types(array(array('type' => 'post', 'post_type' => 'product'))),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                ))
                ->add_fields('banner', __('Banner Đơn'), array(
                    Field::make('image', 'bg_image', 'Ảnh'),
                    Field::make('text', 'link', 'Link'),
                    Field::make('text', 'height', 'Chiều cao'),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                ))
                ->add_fields('text_block', __('Văn bản / HTML'), array(
                    Field::make('rich_text', 'content', 'Nội dung'),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                ))
                ->add_fields('video', __('Video Youtube'), array(
                    Field::make('text', 'video_url', 'Link Youtube'),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                ))
                ->add_fields('row', __('Hàng / Cột'), array(
                    Field::make('color', 'bg_color', 'Màu nền'),
                    Field::make('image', 'bg_image', 'Ảnh nền'),
                    Field::make('checkbox', 'container', 'Container?')->set_default_value(true),
                    Field::make('text', 'mt', 'Margin Top'),
                    Field::make('text', 'mb', 'Margin Bottom'),
                    Field::make('complex', 'columns', 'Các Cột')->set_layout('tabbed-horizontal')
                        ->add_fields(array(
                            Field::make('select', 'width', 'Độ rộng')->set_options(array('12' => '100%', '6' => '50%', '4' => '33%', '3' => '25%'))->set_default_value('6'),
                            Field::make('complex', 'col_content', 'Nội dung')->set_collapsed(true)
                                ->add_fields('text_block', __('Văn bản'), array(Field::make('rich_text', 'content')))
                                ->add_fields('banner', __('Banner'), array(Field::make('image', 'bg_image', 'Ảnh'), Field::make('text', 'link', 'Link')))
                        ))
                ))
        ));
}