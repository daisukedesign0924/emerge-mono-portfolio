<?php
/**
 * Single Post Template — News/Blog article view
 * Loaded via template_include filter for is_singular('en_news')
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

while ( have_posts() ) : the_post();
    $post_id   = get_the_ID();
    $title     = get_the_title();
    $date      = get_the_date();
    $cats      = get_the_terms( $post_id, 'en_news_category' );
    if ( ! $cats || is_wp_error( $cats ) ) $cats = array();
    $thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url( $post_id, 'large' ) : '';

    $news_page = get_page_by_path('news');
    $news_url  = $news_page ? get_permalink( $news_page->ID ) : home_url('/');
    ?>
    <article class="en-post-single" id="en-post-single">
        <div class="en-post-single-inner">

            <a href="<?php echo esc_url( $news_url ); ?>" class="en-post-single-back">← News</a>

            <header class="en-post-single-header">
                <div class="en-post-single-meta">
                    <span class="en-post-single-date"><?php echo esc_html( $date ); ?></span>
                    <?php if ( ! empty( $cats ) ) : ?>
                        <span class="en-post-single-cats">
                        <?php foreach ( $cats as $cat ) : ?>
                            <span class="en-post-single-cat"><?php echo esc_html( $cat->name ); ?></span>
                        <?php endforeach; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h1 class="en-post-single-title"><?php echo esc_html( $title ); ?></h1>
            </header>

            <?php if ( $thumb_url ) : ?>
            <div class="en-post-single-thumb">
                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
            </div>
            <?php endif; ?>

            <div class="en-post-single-body">
                <?php the_content(); ?>
            </div>

            <footer class="en-post-single-footer">
                <a href="<?php echo esc_url( $news_url ); ?>" class="en-post-single-back-bottom">← <?php esc_html_e( 'Back to News', 'emerge-mono-portfolio' ); ?></a>
            </footer>

        </div>
    </article>
<?php endwhile;

get_footer();
