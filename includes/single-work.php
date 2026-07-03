<?php
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) : the_post();
    $post_id  = get_the_ID();
    $title    = get_the_title();
    $content  = get_the_content();
    $cats     = get_the_terms( $post_id, 'en_work_category' );

    $ext_url  = get_post_meta( $post_id, 'en_external_url', true );
    $period   = get_post_meta( $post_id, 'en_period',       true );
    $role     = get_post_meta( $post_id, 'en_role',         true );
    $tools    = get_post_meta( $post_id, 'en_tools',        true );

    $gallery_json  = get_post_meta( $post_id, 'en_gallery_items', true );
    $gallery_items = $gallery_json ? json_decode( $gallery_json, true ) : array();
    if ( empty($gallery_items) ) {
        $old_ids = get_post_meta( $post_id, 'en_gallery_ids', true );
        if ( $old_ids ) {
            $gallery_items = array();
            foreach ( array_filter( array_map( 'intval', explode(',', $old_ids) ) ) as $id ) {
                $gallery_items[] = array( 'type' => 'image', 'id' => $id );
            }
        }
    }
    if ( ! is_array($gallery_items) ) $gallery_items = array();



    $works_page = get_page_by_path('works');
    $works_url  = $works_page ? get_permalink( $works_page->ID ) : home_url('/');

    $meta_rows = array();
    if ( $period ) $meta_rows[ __( '— Duration —', 'emerge-mono-portfolio' ) ] = $period;
    if ( $role   ) $meta_rows[ __( '— Role —', 'emerge-mono-portfolio' ) ] = $role;
    if ( $tools  ) $meta_rows[ __( '— Tools —', 'emerge-mono-portfolio' ) ] = $tools;


    $slide_count = count($gallery_items);
    ?>
    <div class="en-single" id="en-single">
        <div class="en-single-inner">

            <a href="<?php echo esc_url( $works_url ); ?>" class="en-single-back">← Works</a>

            <?php if ( $slide_count > 0 ) : ?>
            <div class="en-slider<?php echo $slide_count > 1 ? ' has-multiple' : ''; ?>" id="en-slider">
                <div class="en-slider-stage" id="en-slider-stage">
                    <?php foreach ( $gallery_items as $i => $item ) : ?>
                    <div class="en-slide<?php echo $i === 0 ? ' active' : ''; ?>">
                        <?php if ( $item['type'] === 'image' ) :
                            $img_url = wp_get_attachment_image_url( $item['id'], 'large' );
                            if ( $img_url ) : ?>
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>">
                        <?php endif;
                        elseif ( $item['type'] === 'video' && ! empty($item['url']) ) :
                            $embed_url = '';
                            if ( preg_match( '/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $item['url'], $m ) ) {
                                $embed_url = 'https://www.youtube.com/embed/' . $m[1] . '?enablejsapi=1';
                            } elseif ( preg_match( '/vimeo\.com\/(\d+)/', $item['url'], $m ) ) {
                                $embed_url = 'https://player.vimeo.com/video/' . $m[1];
                            }
                            if ( $embed_url ) : ?>
                            <div class="en-slide-video">
                                <iframe src="<?php echo esc_url($embed_url); ?>" allowfullscreen></iframe>
                            </div>
                        <?php endif;
                        endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if ( $slide_count > 1 ) : ?>
                    <button class="en-slider-edge en-slider-prev" id="en-slider-prev" aria-label="Previous"><span>&#8592;</span></button>
                    <button class="en-slider-edge en-slider-next" id="en-slider-next" aria-label="Next"><span>&#8594;</span></button>
                    <?php endif; ?>
                </div>

                <?php if ( $slide_count > 1 ) : ?>
                <div class="en-slider-ui">
                    <div class="en-slider-counter"><b id="en-slider-cur">01</b> / <span id="en-slider-tot"><?php echo str_pad($slide_count, 2, '0', STR_PAD_LEFT); ?></span></div>
                    <div class="en-slider-bar"><i id="en-slider-fill"></i></div>
                    <div class="en-slider-arrows">
                        <button id="en-slider-prev2" aria-label="Previous">&#8592;</button>
                        <button id="en-slider-next2" aria-label="Next">&#8594;</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="en-single-footer">
                <div class="en-single-main">
                    <h1 class="en-single-title"><?php echo esc_html( $title ); ?></h1>

                    <?php if ( $cats && ! is_wp_error( $cats ) ) : ?>
                    <div class="en-single-cats">
                        <?php foreach ( $cats as $cat ) : ?>
                        <span class="en-single-cat"><?php echo esc_html( $cat->name ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ( $content ) : ?>
                    <div class="en-single-body">
                        <?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ( ! empty( $meta_rows ) || $ext_url ) : ?>
                <div class="en-single-sidebar">
                    <?php foreach ( $meta_rows as $label => $value ) : ?>
                    <div class="en-single-meta-row">
                        <dt><?php echo esc_html( $label ); ?></dt>
                        <dd><?php echo esc_html( $value ); ?></dd>
                    </div>
                    <?php endforeach; ?>
                    <?php if ( $ext_url ) : ?>
                    <a href="<?php echo esc_url( $ext_url ); ?>" class="en-single-ext-link" target="_blank" rel="noopener">View Project →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <?php if ( $slide_count > 1 ) : ?>
    <script>
    (function(){
        var stage  = document.getElementById('en-slider-stage');
        var slides = stage.querySelectorAll('.en-slide');
        var fill   = document.getElementById('en-slider-fill');
        var curEl  = document.getElementById('en-slider-cur');
        var cur    = 0;
        var total  = <?php echo (int)$slide_count; ?>;

        function pad(n){ return n < 10 ? '0' + n : '' + n; }

        function goTo(n) {
            cur = (n + total) % total;
            slides.forEach(function(s, i){ s.classList.toggle('active', i === cur); });
            if (fill)  fill.style.width = ((cur + 1) / total * 100) + '%';
            if (curEl) curEl.textContent = pad(cur + 1);
        }

        // 矢印（画像上の左右エッジ＋下部UI、両方）
        ['en-slider-prev','en-slider-prev2'].forEach(function(id){
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', function(){ goTo(cur - 1); });
        });
        ['en-slider-next','en-slider-next2'].forEach(function(id){
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', function(){ goTo(cur + 1); });
        });

        // スワイプ（スマホ）
        var startX = 0, startY = 0, swiping = false;
        stage.addEventListener('touchstart', function(e){
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            swiping = true;
        }, {passive:true});
        stage.addEventListener('touchend', function(e){
            if (!swiping) return;
            swiping = false;
            var dx = startX - e.changedTouches[0].clientX;
            var dy = startY - e.changedTouches[0].clientY;
            // 横方向の動きが縦より大きいときだけ反応（縦スクロール誤爆防止）
            if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
                goTo(dx > 0 ? cur + 1 : cur - 1);
            }
        }, {passive:true});

        // キーボード（PCでスライダーにフォーカス／ホバー時）
        var hovering = false;
        var sliderEl = document.getElementById('en-slider');
        sliderEl.addEventListener('mouseenter', function(){ hovering = true; });
        sliderEl.addEventListener('mouseleave', function(){ hovering = false; });
        document.addEventListener('keydown', function(e){
            if (!hovering) return;
            if (e.key === 'ArrowLeft')  goTo(cur - 1);
            if (e.key === 'ArrowRight') goTo(cur + 1);
        });

        goTo(0);
    })();
    </script>
    <?php endif; ?>

    <?php
endwhile;

get_footer();
