<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', 'relive_register_builder');

function relive_register_builder()
{
    Container::make('post_meta', __('PAGE BUILDER', 'relive'))
        ->where('post_type', '=', 'page')
        ->add_fields(array(
            Field::make('complex', 'builder_blocks', __('Nội dung trang', 'relive'))
                ->set_layout('tabbed-vertical')
                ->set_collapsed(true)

                // 1. SLIDER ẢNH (Đã update full options)
                ->add_fields('slider', __('Slider Ảnh', 'relive'), array(

                    Field::make('complex', 'slides', 'Danh sách Slide')
                        ->set_layout('tabbed-horizontal')
                        ->add_fields(array(
                            Field::make('image', 'image', 'Ảnh'),
                            Field::make('text', 'link', 'Link'),
                            // MỚI: Ô nhập cho kiểu Relive Style
                            Field::make('text', 'thumb_title', 'Tiêu đề tab (VD: Tủ lạnh)')->set_width(50),
                            Field::make('text', 'thumb_desc', 'Mô tả tab (VD: Giảm 20%)')->set_width(50),
                        )),

                    // Tùy chọn độ rộng
                    Field::make('select', 'width_mode', 'Kiểu hiển thị')
                        ->set_options(array(
                            'container' => 'Có lề (Container)',
                            'full' => 'Tràn viền (Full Width)'
                        ))
                        ->set_default_value('container')
                        ->set_width(50),

                    Field::make('text', 'height', 'Chiều cao (px)')->set_default_value(400)->set_width(50),

                    Field::make('select', 'effect', 'Hiệu ứng')
                        ->set_options(array('slide' => 'Trượt ngang', 'fade' => 'Mờ dần', 'creative' => 'Creative'))
                        ->set_width(50),

                    Field::make('checkbox', 'arrows', 'Hiện mũi tên?')->set_default_value(true)->set_width(33),
                    // --- MỚI THÊM: Tùy chọn ẩn mũi tên trên Mobile ---
                    Field::make('checkbox', 'arrows_hide_mobile', 'Ẩn mũi tên trên Mobile?')->set_default_value(true)->set_width(33),
                    Field::make('checkbox', 'dots', 'Hiện phân trang?')->set_default_value(true)->set_width(33),
                    Field::make('checkbox', 'autoplay', 'Tự động chạy?')->set_default_value(true)->set_width(33),

                    // MỚI: Tùy chọn kiểu phân trang thumbs_text
                    Field::make('select', 'pagi_style', 'Kiểu phân trang')
                        ->set_options(array(
                            'dots'        => 'Dấu chấm tròn (Default)',
                            'dashes'      => 'Gạch ngang (FPT Style)',
                            'thumbs_text' => 'Tab nội dung (Relive Style)',
                        ))
                        ->set_default_value('dots')
                        ->set_width(100),

                    Field::make('text', 'mt', 'Margin Top')->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_width(50),
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-images-alt2"></span></div><div class="cf-info"><strong>SLIDER</strong></div></div>')

                // 2. DANH MỤC (Update: Bỏ Effect, giữ lại cột & hàng)
                ->add_fields('category_icons', __('Danh mục (Swiper)', 'relive'), array(
                    Field::make('text', 'title', 'Tiêu đề')->set_default_value('Danh mục nổi bật'),

                    Field::make('association', 'selected_cats', 'Chọn danh mục')
                        ->set_types(array(array('type' => 'term', 'taxonomy' => 'product_cat'))),

                    // ĐÃ XÓA: Field cat_effect

                    Field::make('select', 'cat_rows', 'Số hàng hiển thị')
                        ->set_options(array('1' => '1 Hàng', '2' => '2 Hàng (Grid)'))
                        ->set_default_value('1')
                        ->set_width(50),

                    // Cột Responsive
                    Field::make('text', 'col_desk', 'Số cột Desktop')->set_default_value(8)->set_width(33)->set_attribute('type', 'number'),
                    Field::make('text', 'col_tab', 'Số cột Tablet')->set_default_value(6)->set_width(33)->set_attribute('type', 'number'),
                    Field::make('text', 'col_mob', 'Số cột Mobile')->set_default_value(4)->set_width(33)->set_attribute('type', 'number'),

                    Field::make('text', 'mt', 'Margin Top')->set_default_value(0)->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_default_value(30)->set_width(50),
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-category"></span></div><div class="cf-info"><strong>DANH MỤC</strong></div></div>')

                // 3. BANNER
                ->add_fields('banner', __('Banner', 'relive'), array(
                    Field::make('image', 'bg_image', 'Ảnh'),
                    Field::make('text', 'link', 'Link'),
                    Field::make('text', 'height', 'Cao')->set_default_value('auto'),
                    Field::make('text', 'mt', 'Margin Top')->set_default_value(0)->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_default_value(30)->set_width(50),
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-cover-image"></span></div><div class="cf-info"><strong>BANNER</strong></div></div>')

                // 4. SẢN PHẨM
                ->add_fields('product_grid', __('Sản phẩm', 'relive'), array(
                    Field::make('text', 'title', 'Tiêu đề'),
                    Field::make('select', 'filter_type', 'Nguồn')->set_options(array('auto' => 'Tự động', 'cat' => 'Danh mục', 'manual' => 'Thủ công'))->set_default_value('auto'),
                    Field::make('select', 'auto_type', 'Loại')->set_options(array('recent' => 'Mới', 'sale' => 'Sale', 'best' => 'Hot'))->set_default_value('recent')->set_conditional_logic(array(array('field' => 'filter_type', 'value' => 'auto'))),
                    Field::make('association', 'selected_cats', 'Chọn DM')->set_types(array(array('type' => 'term', 'taxonomy' => 'product_cat')))->set_conditional_logic(array(array('field' => 'filter_type', 'value' => 'cat'))),
                    Field::make('association', 'selected_ids', 'Chọn SP')->set_types(array(array('type' => 'post', 'post_type' => 'product')))->set_conditional_logic(array(array('field' => 'filter_type', 'value' => 'manual'))),
                    Field::make('text', 'limit', 'Số lượng')->set_default_value(8)->set_width(50),
                    Field::make('select', 'col', 'Cột')->set_options(array('3' => '3', '4' => '4', '5' => '5'))->set_default_value('4')->set_width(50),
                    Field::make('text', 'mt', 'Margin Top')->set_default_value(0)->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_default_value(30)->set_width(50),
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-grid-view"></span></div><div class="cf-info"><strong>SẢN PHẨM</strong></div></div>')

                // 5. TEXT
                ->add_fields('text_block', __('Văn bản', 'relive'), array(
                    Field::make('rich_text', 'content', 'Nội dung'),
                    Field::make('text', 'mt', 'Margin Top')->set_default_value(0)->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_default_value(30)->set_width(50),
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-editor-code"></span></div><div class="cf-info"><strong>TEXT</strong></div></div>')

                // 6. VIDEO
                ->add_fields('video', __('Video', 'relive'), array(
                    Field::make('text', 'video_url', 'Link YouTube'),
                    Field::make('text', 'mt', 'Margin Top')->set_default_value(0)->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_default_value(30)->set_width(50),
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-video-alt3"></span></div><div class="cf-info"><strong>VIDEO</strong></div></div>')

                // 7. ROW / COLUMN
                ->add_fields('row', __('Hàng / Cột', 'relive'), array(
                    Field::make('color', 'bg_color', 'Màu nền')->set_width(33),
                    Field::make('image', 'bg_image', 'Ảnh nền')->set_width(33),
                    Field::make('checkbox', 'container', 'Container?')->set_default_value(true)->set_width(33),
                    Field::make('text', 'mt', 'Margin Top')->set_default_value(0)->set_width(50),
                    Field::make('text', 'mb', 'Margin Bottom')->set_default_value(30)->set_width(50),

                    Field::make('complex', 'columns', 'Các Cột')
                        ->set_layout('tabbed-horizontal')
                        ->add_fields(array(
                            Field::make('select', 'width', 'Độ rộng')
                                ->set_options(array('12' => '100%', '6' => '50%', '4' => '33%', '3' => '25%', '8' => '66%', '9' => '75%'))->set_default_value('6'),

                            Field::make('complex', 'col_content', 'Nội dung')
                                ->set_collapsed(true)
                                ->add_fields('banner', __('Banner'), array(Field::make('image', 'bg_image', 'Ảnh'), Field::make('text', 'link', 'Link'), Field::make('text', 'height', 'Cao')->set_default_value(300), Field::make('text', 'mt', 'Top')->set_width(50), Field::make('text', 'mb', 'Bottom')->set_width(50)))
                                ->add_fields('product_grid', __('Sản phẩm'), array(Field::make('text', 'title', 'Tiêu đề'), Field::make('text', 'limit', 'SL')->set_default_value(4), Field::make('select', 'col', 'Cột')->set_default_value('2'), Field::make('select', 'filter_type', 'Nguồn')->set_options(array('auto' => 'Auto', 'cat' => 'DM'))->set_default_value('auto'), Field::make('select', 'auto_type', 'Loại')->set_options(array('recent' => 'Mới', 'sale' => 'Sale', 'best' => 'Hot'))->set_default_value('recent'), Field::make('association', 'selected_cats', 'Chọn DM')->set_types(array(array('type' => 'term', 'taxonomy' => 'product_cat')))->set_conditional_logic(array(array('field' => 'filter_type', 'value' => 'cat'))), Field::make('text', 'mt', 'Top')->set_width(50), Field::make('text', 'mb', 'Bottom')->set_width(50)))
                                ->add_fields('text_block', __('Văn bản'), array(Field::make('rich_text', 'content', 'Nội dung'), Field::make('text', 'mt', 'Top')->set_width(50), Field::make('text', 'mb', 'Bottom')->set_width(50)))
                                ->add_fields('video', __('Video'), array(Field::make('text', 'video_url', 'Link'), Field::make('text', 'mt', 'Top')->set_width(50), Field::make('text', 'mb', 'Bottom')->set_width(50)))
                        ))
                ))->set_header_template('<div class="cf-preview-block"><div class="cf-icon-wrap"><span class="dashicons dashicons-columns"></span></div><div class="cf-info"><strong>ROW</strong></div></div>')
        ));
}
