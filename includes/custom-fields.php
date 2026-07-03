<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', 'en_add_work_meta_boxes' );
function en_add_work_meta_boxes() {
    add_meta_box( 'en_work_meta', __( 'Work Details', 'emerge-mono-portfolio' ), 'en_work_meta_box_html', 'en_work', 'normal', 'high' );

}

function en_work_meta_box_html( $post ) {
    wp_nonce_field( 'en_work_meta_save', 'en_work_meta_nonce' );
    $video   = get_post_meta( $post->ID, 'en_video_url', true );
    $ext_url = get_post_meta( $post->ID, 'en_external_url', true );
    $period  = get_post_meta( $post->ID, 'en_period', true );
    $role    = get_post_meta( $post->ID, 'en_role', true );
    $tools   = get_post_meta( $post->ID, 'en_tools', true );
    $status  = get_post_meta( $post->ID, 'en_status', true );
    ?>
    <table style="width:100%;border-collapse:collapse">
        <tr><th style="width:140px;padding:10px 10px 10px 0;font-size:13px;text-align:left"><?php esc_html_e( 'Video URL', 'emerge-mono-portfolio' ); ?></th>
            <td><input type="url" name="en_video_url" value="<?php echo esc_attr($video); ?>" style="width:100%;padding:6px 8px;border:1px solid #ddd" placeholder="<?php echo esc_attr__( 'YouTube / Vimeo URL', 'emerge-mono-portfolio' ); ?>"></td></tr>
        <tr><th style="width:140px;padding:10px 10px 10px 0;font-size:13px;text-align:left"><?php esc_html_e( 'External Link', 'emerge-mono-portfolio' ); ?></th>
            <td><input type="url" name="en_external_url" value="<?php echo esc_attr($ext_url); ?>" style="width:100%;padding:6px 8px;border:1px solid #ddd" placeholder="https://"></td></tr>
        <tr><th style="width:140px;padding:10px 10px 10px 0;font-size:13px;text-align:left"><?php esc_html_e( 'Duration', 'emerge-mono-portfolio' ); ?></th>
            <td><input type="text" name="en_period" value="<?php echo esc_attr($period); ?>" style="width:100%;padding:6px 8px;border:1px solid #ddd" placeholder="2024.01 - 2024.03"></td></tr>
        <tr><th style="width:140px;padding:10px 10px 10px 0;font-size:13px;text-align:left"><?php esc_html_e( 'Role', 'emerge-mono-portfolio' ); ?></th>
            <td><input type="text" name="en_role" value="<?php echo esc_attr($role); ?>" style="width:100%;padding:6px 8px;border:1px solid #ddd"></td></tr>
        <tr><th style="width:140px;padding:10px 10px 10px 0;font-size:13px;text-align:left"><?php esc_html_e( 'Tools Used', 'emerge-mono-portfolio' ); ?></th>
            <td><input type="text" name="en_tools" value="<?php echo esc_attr($tools); ?>" style="width:100%;padding:6px 8px;border:1px solid #ddd"></td></tr>
        <tr><th style="width:140px;padding:10px 10px 10px 0;font-size:13px;text-align:left"><?php esc_html_e( 'Status', 'emerge-mono-portfolio' ); ?></th>
            <td><select name="en_status" style="padding:6px 8px;border:1px solid #ddd">
                <option value="publish" <?php selected($status,'publish'); ?>><?php esc_html_e( 'Published', 'emerge-mono-portfolio' ); ?></option>
                <option value="private" <?php selected($status,'private'); ?>><?php esc_html_e( 'Private', 'emerge-mono-portfolio' ); ?></option>
            </select></td></tr>
    </table>
    <?php
}

add_action( 'save_post_en_work', 'en_save_work_meta' );
function en_save_work_meta( $post_id ) {
    if ( ! isset( $_POST['en_work_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['en_work_meta_nonce'], 'en_work_meta_save' ) ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $fields = array( 'en_video_url', 'en_external_url', 'en_period', 'en_role', 'en_tools', 'en_status' );
    foreach ( $fields as $f ) {
        if ( isset( $_POST[ $f ] ) ) {
            update_post_meta( $post_id, $f, sanitize_text_field( $_POST[ $f ] ) );
        }
    }

}
