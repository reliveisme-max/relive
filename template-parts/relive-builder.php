<?php
// Lấy data từ field mới 'builder_blocks' (đã đổi tên trong builder-fields.php)
$blocks = carbon_get_the_post_meta( 'builder_blocks' );

if ( ! empty( $blocks ) ) {
    foreach ( $blocks as $block ) {
        // $type ở đây sẽ là 'slider', 'banner'... (không còn re_ nữa)
        $type = $block['_type']; 
        
        // Gọi file trong thư mục builder
        // Ví dụ: template-parts/builder/slider.php
        get_template_part( 'template-parts/builder/' . $type, null, array( 'data' => $block ) );
    }
}
?>