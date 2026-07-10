<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'emerge_mono_contact', 'emono_shortcode_contact' );
function emono_shortcode_contact( $atts ) {
    // フィールドは getter 経由（デザイン編集のライブプレビュー時は仮の値で差し替わる）。
    $fields      = function_exists( 'emono_get_contact_fields' ) ? emono_get_contact_fields() : get_option( 'en_contact_fields', emono_default_contact_fields() );
    $btn_text    = emono_opt('contact_btn_text', 'Send');
    $recaptcha_key = emono_opt('recaptcha_site_key', '');
    ob_start(); ?>
    <div class="en-contact" id="en-contact">
        <div class="en-contact-inner">
            <div class="en-contact-label">Contact</div>
            <?php if ( $msg = emono_opt('contact_desc', '') ) : ?>
                <div class="en-contact-desc"><?php echo esc_html($msg); ?></div>
            <?php endif; ?>
            <form class="en-contact-form" id="en-contact-form" <?php echo $recaptcha_key ? '' : 'data-no-recaptcha="1"'; ?>>
                <input type="hidden" name="action" value="en_send_contact">
                <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce('en_nonce') ); ?>">
                <!-- ハニーポット（ボット対策・ユーザーには見えない） -->
                <div style="position:absolute;left:-9999px;opacity:0;pointer-events:none" aria-hidden="true">
                    <input type="text" name="en_hp_field" tabindex="-1" autocomplete="off">
                </div>
                <?php foreach ( $fields as $field ) :
                    $key         = sanitize_key( $field['key'] );
                    $label       = $field['label'];
                    $type        = $field['type'];
                    $required    = ! empty( $field['required'] );
                    $placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
                    $options     = isset($field['options']) ? $field['options'] : array();
                    $req_attr    = $required ? 'required' : '';
                    $req_mark    = $required ? '<span class="en-required">*</span>' : '<span class="en-optional">' . esc_html__( 'Optional', 'emerge-mono' ) . '</span>';
                ?>
                <div class="en-form-group">
                    <label class="en-form-label"><?php echo esc_html($label); ?> <?php echo wp_kses( $req_mark, array( 'span' => array( 'class' => array() ) ) ); ?></label>
                    <?php if ( $type === 'textarea' ) : ?>
                        <textarea name="en_field_<?php echo esc_attr($key); ?>" class="en-form-textarea" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo esc_attr( $req_attr ); ?>></textarea>
                    <?php elseif ( $type === 'select' ) : ?>
                        <select name="en_field_<?php echo esc_attr($key); ?>" class="en-form-input" <?php echo esc_attr( $req_attr ); ?>>
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono' ); ?></option>
                            <?php foreach ( $options as $opt ) : ?>
                                <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ( $type === 'checkbox' ) : ?>
                        <div class="en-form-checkboxes">
                            <?php foreach ( $options as $opt ) : ?>
                                <label class="en-form-check-label">
                                    <input type="checkbox" name="en_field_<?php echo esc_attr($key); ?>[]" value="<?php echo esc_attr($opt); ?>">
                                    <?php echo esc_html($opt); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <input type="<?php echo esc_attr($type); ?>" name="en_field_<?php echo esc_attr($key); ?>" class="en-form-input" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo esc_attr( $req_attr ); ?>>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php
                // 同意チェックボックス
                $consent_enabled  = emono_opt('contact_consent_enabled', '0');
                $consent_text     = emono_opt('contact_consent_text', __( 'I agree to the Privacy Policy.', 'emerge-mono' ));
                $consent_page_id  = (int)emono_opt('contact_consent_page_id', 0);
                $consent_page_url = $consent_page_id ? get_permalink($consent_page_id) : '';
                if ( $consent_enabled === '1' ) : ?>
                <div class="en-form-group">
                    <label class="en-form-consent-label">
                        <input type="checkbox" name="en_consent" required class="en-form-consent-check">
                        <span>
                            <?php if ( $consent_page_url ) : ?>
                                <a href="<?php echo esc_url($consent_page_url); ?>" target="_blank" rel="noopener" class="en-consent-link"><?php echo esc_html($consent_text); ?></a>
                            <?php else : ?>
                                <?php echo esc_html($consent_text); ?>
                            <?php endif; ?>
                            <span class="en-required">*</span>
                        </span>
                    </label>
                </div>
                <?php endif; ?>
                <button type="submit" class="en-form-submit"><?php echo esc_html($btn_text); ?></button>
                <div class="en-form-status" id="en-form-status"></div>
            </form>
        </div>
    </div>
    <?php return ob_get_clean();
}

add_shortcode( 'emerge_mono_posts', 'emono_shortcode_posts' );
function emono_shortcode_posts( $atts ) {
    $atts = shortcode_atts( array( 'type' => '' ), $atts );
    $type = sanitize_key( $atts['type'] );
    if ( ! $type ) return '';

    $posts = get_posts( array(
        'post_type'      => 'en_' . $type,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if ( empty($posts) ) return '<div class="en-posts-empty">' . esc_html__( 'No posts found', 'emerge-mono' ) . '</div>';

    ob_start(); ?>
    <div class="en-posts" id="en-posts-<?php echo esc_attr($type); ?>">
        <div class="en-posts-header">
            <div class="en-posts-title"><?php echo esc_html( ucfirst($type) ); ?></div>
        </div>
        <div class="en-posts-list">
            <?php foreach ( $posts as $post ) :
                $thumb = get_the_post_thumbnail_url( $post->ID, 'large' );
                $url   = get_permalink( $post->ID );
                $date  = get_the_date( 'Y.m.d', $post->ID );
            ?>
            <article class="en-post-item">
                <a href="<?php echo esc_url($url); ?>" class="en-post-link">
                    <?php if ( $thumb ) : ?>
                        <div class="en-post-thumb" style="background-image:url(<?php echo esc_url($thumb); ?>)"></div>
                    <?php else : ?>
                        <div class="en-post-thumb en-post-thumb-empty"></div>
                    <?php endif; ?>
                    <div class="en-post-meta">
                        <div class="en-post-date"><?php echo esc_html($date); ?></div>
                        <h3 class="en-post-title"><?php echo esc_html( get_the_title($post->ID) ); ?></h3>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
    <?php return ob_get_clean();
}

add_shortcode( 'emerge_mono_news', 'emono_shortcode_news' );
function emono_shortcode_news( $atts ) {
    $atts = shortcode_atts( array(
        'per_page' => 20,
        'category' => '',
    ), $atts );

    $args = array(
        'post_type'      => 'en_news',
        'posts_per_page' => (int) $atts['per_page'],
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    if ( $atts['category'] ) {
        $args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Simple category filter for a small news list.
            array(
                'taxonomy' => 'en_news_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['category'] ),
            ),
        );
    }

    $posts = get_posts( $args );

    ob_start(); ?>
    <div class="en-news" id="en-news">
        <div class="en-news-header">
            <div class="en-news-label">News</div>
            <div class="en-news-title"><?php esc_html_e( 'News', 'emerge-mono' ); ?></div>
        </div>
        <?php if ( empty($posts) ) : ?>
            <div class="en-news-empty"><?php esc_html_e( 'No posts found', 'emerge-mono' ); ?></div>
        <?php else : ?>
        <div class="en-news-list">
            <?php foreach ( $posts as $post ) :
                $url  = get_permalink( $post->ID );
                $date = get_the_date( 'Y.m.d', $post->ID );
                $cats = get_the_terms( $post->ID, 'en_news_category' );
                $cat  = ( $cats && ! is_wp_error($cats) ) ? $cats[0]->name : '';
            ?>
            <a href="<?php echo esc_url($url); ?>" class="en-news-item">
                <div class="en-news-meta">
                    <?php if ( $cat ) : ?>
                        <span class="en-news-cat"><?php echo esc_html($cat); ?></span>
                    <?php endif; ?>
                    <span class="en-news-date"><?php echo esc_html($date); ?></span>
                </div>
                <div class="en-news-item-title"><?php echo esc_html( get_the_title($post->ID) ); ?></div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}
