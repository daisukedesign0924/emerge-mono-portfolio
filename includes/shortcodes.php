<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'emerge_mono_top', 'emono_shortcode_top' );
function emono_shortcode_top( $atts ) {
    $site_name      = emono_opt('site_name', get_bloginfo('name'));
    $tagline        = emono_opt('site_tagline', '');
    $logo_url       = emono_opt('logo_url', '');
    $logo_url_light = emono_opt('logo_url_light', '');

    // TOPボタン（複数対応）
    $top_buttons = emono_opt('top_buttons', array());
    if ( empty($top_buttons) ) {
        // 自動検出
        $top_buttons = array();
        $p1 = get_page_by_path('about') ?: get_page_by_path('profile');
        $p2 = get_page_by_path('works');
        if ( $p1 ) $top_buttons[] = array( 'label' => 'Profile', 'url' => get_permalink($p1->ID) );
        if ( $p2 ) $top_buttons[] = array( 'label' => 'Works',   'url' => get_permalink($p2->ID) );
    }
    ob_start(); ?>
    <div class="en-top" id="en-top">
        <?php if ( $logo_url || $logo_url_light ) :
            $dark_src  = $logo_url       ? $logo_url       : $logo_url_light;
            $light_src = $logo_url_light ? $logo_url_light : $logo_url;
        ?>
            <img src="<?php echo esc_url($dark_src); ?>"  alt="<?php echo esc_attr($site_name); ?>" class="en-top-logo-img en-logo-dark">
            <?php if ( $logo_url && $logo_url_light ) : ?>
            <img src="<?php echo esc_url($light_src); ?>" alt="<?php echo esc_attr($site_name); ?>" class="en-top-logo-img en-logo-light">
            <?php endif; ?>
        <?php else : ?>
            <div class="en-top-logo-placeholder"></div>
        <?php endif; ?>
        <div class="en-top-name"><?php echo esc_html($site_name); ?></div>
        <?php if ( $tagline ) : ?>
            <div class="en-top-sub"><?php echo esc_html($tagline); ?></div>
        <?php endif; ?>
        <?php if ( ! empty($top_buttons) ) : ?>
        <div class="en-top-btns">
            <?php foreach ( $top_buttons as $tbtn ) :
                if ( empty($tbtn['label']) ) continue; ?>
                <a href="<?php echo esc_url(isset($tbtn['url']) ? $tbtn['url'] : '#'); ?>" class="en-btn"><?php echo esc_html($tbtn['label']); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php return ob_get_clean();
}

add_shortcode( 'emerge_mono_about', 'emono_shortcode_about' );
function emono_shortcode_about( $atts ) {
    $name    = emono_opt('profile_name', '');
    $role    = emono_opt('profile_role', '');
    $bio     = emono_opt('profile_bio', '');
    $img_url = emono_opt('profile_img', '');
    $skills  = emono_opt('profile_skills', '');
    $sns     = emono_opt('sns_links', array());

    // スキルをカンマ分割・トリム
    $skill_list = array();
    if ( $skills ) {
        $skill_list = array_filter( array_map( 'trim', explode( ',', $skills ) ) );
    }

    ob_start(); ?>
    <div class="en-about" id="en-about">
        <div class="en-about-inner">

            <div class="en-about-img-wrap">
                <?php if ( $img_url ) : ?>
                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($name); ?>" class="en-about-img">
                <?php else : ?>
                    <div class="en-about-img-placeholder"></div>
                <?php endif; ?>
            </div>

            <div class="en-about-info">
                <div class="en-about-label">About</div>

                <?php if ( $name ) : ?>
                    <h1 class="en-about-name"><?php echo esc_html($name); ?></h1>
                <?php endif; ?>

                <?php if ( $role ) : ?>
                    <div class="en-about-role"><?php echo esc_html($role); ?></div>
                <?php endif; ?>

                <?php if ( $bio ) : ?>
                    <div class="en-about-bio"><?php echo nl2br( esc_html($bio) ); ?></div>
                <?php endif; ?>

                <?php if ( ! empty($skill_list) ) : ?>
                    <div class="en-about-skills">
                        <?php foreach ( $skill_list as $skill ) : ?>
                            <span class="en-about-skill"><?php echo esc_html($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty($sns) ) : ?>
                    <div class="en-about-sns">
                        <?php foreach ( $sns as $s ) :
                            if ( empty($s['url']) ) continue;
                            $sns_icon  = isset($s['icon'])  ? $s['icon']  : '';
                            $sns_label = isset($s['label']) ? $s['label'] : $s['url'];
                        ?>
                            <a href="<?php echo esc_url($s['url']); ?>" target="_blank" rel="noopener" class="en-sns-link<?php echo $sns_icon ? ' en-sns-link-icon' : ''; ?>">
                                <?php if ( $sns_icon ) : ?>
                                    <img src="<?php echo esc_url($sns_icon); ?>" alt="<?php echo esc_attr($sns_label); ?>" class="en-sns-icon-img">
                                <?php else : ?>
                                    <?php echo esc_html( $sns_label ); ?>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}

add_shortcode( 'emerge_mono_works', 'emono_shortcode_works' );
function emono_shortcode_works( $atts ) {
    ob_start(); ?>
    <div class="en-works" id="en-works">
        <div class="en-works-header">
            <div class="en-works-title">Works</div>
            <div class="en-works-filter" id="en-works-filter">
                <button class="en-filter-btn active" data-cat="all">All</button>
            </div>
        </div>
        <div class="en-works-grid" id="en-works-grid"></div>
        <div class="en-works-loading" id="en-works-loading">Loading...</div>
    </div>
    <?php return ob_get_clean();
}

add_shortcode( 'emerge_mono_contact', 'emono_shortcode_contact' );
function emono_shortcode_contact( $atts ) {
    $fields      = get_option( 'en_contact_fields', emono_default_contact_fields() );
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
                    $req_mark    = $required ? '<span class="en-required">*</span>' : '<span class="en-optional">' . esc_html__( 'Optional', 'emerge-mono-portfolio' ) . '</span>';
                ?>
                <div class="en-form-group">
                    <label class="en-form-label"><?php echo esc_html($label); ?> <?php echo esc_html( $req_mark ); ?></label>
                    <?php if ( $type === 'textarea' ) : ?>
                        <textarea name="en_field_<?php echo esc_attr($key); ?>" class="en-form-textarea" placeholder="<?php echo esc_attr($placeholder); ?>" <?php echo esc_attr( $req_attr ); ?>></textarea>
                    <?php elseif ( $type === 'select' ) : ?>
                        <select name="en_field_<?php echo esc_attr($key); ?>" class="en-form-input" <?php echo esc_attr( $req_attr ); ?>>
                            <option value=""><?php esc_html_e( 'Please select', 'emerge-mono-portfolio' ); ?></option>
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
                $consent_text     = emono_opt('contact_consent_text', __( 'I agree to the Privacy Policy.', 'emerge-mono-portfolio' ));
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

    if ( empty($posts) ) return '<div class="en-posts-empty">' . esc_html__( 'No posts found', 'emerge-mono-portfolio' ) . '</div>';

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
            <div class="en-news-title"><?php esc_html_e( 'News', 'emerge-mono-portfolio' ); ?></div>
        </div>
        <?php if ( empty($posts) ) : ?>
            <div class="en-news-empty"><?php esc_html_e( 'No posts found', 'emerge-mono-portfolio' ); ?></div>
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
