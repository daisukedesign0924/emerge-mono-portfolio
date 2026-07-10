<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── 投稿するページ ──
function emono_ed_page_post() {
    $type    = isset($_GET['type']) ? sanitize_key($_GET['type']) : 'works'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    $edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.

    // 編集時はデータ取得
    $edit_post = null;
    if ( $edit_id ) {
        $edit_post = get_post( $edit_id );
        if ( $edit_post ) {
            $type = $edit_post->post_type === 'en_work' ? 'works' : 'news';
        }
    }

    // カテゴリー取得
    $work_cats = get_terms( array( 'taxonomy' => 'en_work_category', 'hide_empty' => false ) );
    $news_cats = get_terms( array( 'taxonomy' => 'en_news_category', 'hide_empty' => false ) );
    if ( ! is_array($work_cats) ) $work_cats = array();
    if ( ! is_array($news_cats) ) $news_cats = array();
    ?>
    <div class="ene-wrap">
        <div class="ene-header">
            <div class="ene-header-inner">
                <div class="ene-header-title">
                    <?php echo $edit_id ? esc_html__( 'Edit Post', 'emerge-mono' ) : esc_html__( 'New Post', 'emerge-mono' ); ?>
                </div>
            </div>
        </div>
        <?php if ( ! $edit_id ) : ?>
        <div class="ene-type-bar">
            <div class="ene-type-bar-label"><?php esc_html_e( 'Where would you like to post?', 'emerge-mono' ); ?></div>
            <div class="ene-type-bar-btns">
                <a href="?page=ene-post&type=works"
                   class="ene-type-bar-btn <?php echo $type === 'works' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-portfolio"></span>
                    Works
                    <span class="ene-type-desc"><?php esc_html_e( 'Works / Portfolio', 'emerge-mono' ); ?></span>
                </a>
                <a href="?page=ene-post&type=news"
                   class="ene-type-bar-btn <?php echo $type === 'news' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php esc_html_e( 'News', 'emerge-mono' ); ?>
                    <span class="ene-type-desc"><?php esc_html_e( 'Blog / News posts', 'emerge-mono' ); ?></span>
                </a>
            </div>
        </div>
        <?php else : ?>
        <div class="ene-type-bar">
            <div class="ene-type-bar-label">
                <?php echo $type === 'works' ? esc_html__( '📂 Editing Works', 'emerge-mono' ) : esc_html__( '📰 Editing News', 'emerge-mono' ); ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="ene-body">
            <form id="ene-post-form" class="ene-form">
                <input type="hidden" id="ene-post-type" value="<?php echo esc_attr($type); ?>">
                <input type="hidden" id="ene-post-id" value="<?php echo esc_attr($edit_id); ?>">
                <?php wp_nonce_field( 'ene_post_nonce', 'ene_nonce' ); ?>

                <div class="ene-main">
                    <!-- タイトル -->
                    <div class="ene-field">
                        <label class="ene-label"><?php esc_html_e( 'Title', 'emerge-mono' ); ?> <span class="ene-req">*</span></label>
                        <input type="text" id="ene-title" class="ene-input" placeholder="<?php echo esc_attr__( 'Enter a title', 'emerge-mono' ); ?>"
                               value="<?php echo $edit_post ? esc_attr($edit_post->post_title) : ''; ?>">
                    </div>

                    <!-- 本文 -->
                    <div class="ene-field">
                        <label class="ene-label"><?php esc_html_e( 'Content', 'emerge-mono' ); ?></label>
                        <textarea id="ene-content" class="ene-textarea" placeholder="<?php echo esc_attr__( 'Enter content (optional)', 'emerge-mono' ); ?>"><?php echo $edit_post ? esc_textarea($edit_post->post_content) : ''; ?></textarea>
                    </div>

                    <!-- Works専用フィールド -->
                    <div id="ene-works-fields" style="<?php echo $type !== 'works' ? 'display:none' : ''; ?>">
                        <?php
                        $ene_opts        = get_option('en_options', array());
                        $ene_work_fields = isset($ene_opts['work_fields']) ? $ene_opts['work_fields'] : array();
                        $ene_wf_video    = isset($ene_work_fields['video_url'])    ? (int)$ene_work_fields['video_url']    : 1;
                        $ene_wf_ext      = isset($ene_work_fields['external_url']) ? (int)$ene_work_fields['external_url'] : 1;
                        $ene_wf_period   = isset($ene_work_fields['period'])       ? (int)$ene_work_fields['period']       : 1;
                        $ene_wf_role     = isset($ene_work_fields['role'])         ? (int)$ene_work_fields['role']         : 1;
                        $ene_wf_tools    = isset($ene_work_fields['tools'])        ? (int)$ene_work_fields['tools']        : 1;
                        ?>
                        <div class="ene-field-grid">
                            <?php if ( $ene_wf_video ) : ?>
                            <div class="ene-field">
                                <label class="ene-label"><?php esc_html_e( 'Video URL', 'emerge-mono' ); ?></label>
                                <input type="url" id="ene-video-url" class="ene-input" placeholder="<?php echo esc_attr__( 'YouTube / Vimeo URL', 'emerge-mono' ); ?>"
                                       value="<?php echo $edit_post ? esc_attr(get_post_meta($edit_post->ID,'en_video_url',true)) : ''; ?>">
                            </div>
                            <?php endif; ?>
                            <?php if ( $ene_wf_ext ) : ?>
                            <div class="ene-field">
                                <label class="ene-label"><?php esc_html_e( 'External Link', 'emerge-mono' ); ?></label>
                                <input type="url" id="ene-ext-url" class="ene-input" placeholder="https://"
                                       value="<?php echo $edit_post ? esc_attr(get_post_meta($edit_post->ID,'en_external_url',true)) : ''; ?>">
                            </div>
                            <?php endif; ?>
                            <?php if ( $ene_wf_period ) : ?>
                            <div class="ene-field">
                                <label class="ene-label"><?php esc_html_e( 'Duration', 'emerge-mono' ); ?></label>
                                <input type="text" id="ene-period" class="ene-input" placeholder="2024.01 – 2024.03"
                                       value="<?php echo $edit_post ? esc_attr(get_post_meta($edit_post->ID,'en_period',true)) : ''; ?>">
                            </div>
                            <?php endif; ?>
                            <?php if ( $ene_wf_role ) : ?>
                            <div class="ene-field">
                                <label class="ene-label"><?php esc_html_e( 'Role', 'emerge-mono' ); ?></label>
                                <input type="text" id="ene-role" class="ene-input" placeholder="<?php echo esc_attr__( 'Design / Development', 'emerge-mono' ); ?>"
                                       value="<?php echo $edit_post ? esc_attr(get_post_meta($edit_post->ID,'en_role',true)) : ''; ?>">
                            </div>
                            <?php endif; ?>
                            <?php if ( $ene_wf_tools ) : ?>
                            <div class="ene-field">
                                <label class="ene-label"><?php esc_html_e( 'Tools Used', 'emerge-mono' ); ?></label>
                                <input type="text" id="ene-tools" class="ene-input" placeholder="<?php echo esc_attr__( 'Figma, Swift', 'emerge-mono' ); ?>"
                                       value="<?php echo $edit_post ? esc_attr(get_post_meta($edit_post->ID,'en_tools',true)) : ''; ?>">
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <div class="ene-sidebar">
                    <!-- アイキャッチ画像 -->
                    <div class="ene-side-section">
                        <div class="ene-side-title"><?php esc_html_e( 'Featured Image', 'emerge-mono' ); ?></div>
                        <div id="ene-thumb-preview" class="ene-thumb-preview" onclick="eneOpenMedia()">
                            <?php
                            $thumb_id  = $edit_post ? get_post_thumbnail_id($edit_post->ID) : 0;
                            $thumb_url = $thumb_id  ? wp_get_attachment_image_url($thumb_id,'medium') : '';
                            ?>
                            <?php if ($thumb_url) : ?>
                                <img src="<?php echo esc_url($thumb_url); ?>" id="ene-thumb-img">
                                <div class="ene-thumb-overlay"><?php esc_html_e( 'Change', 'emerge-mono' ); ?></div>
                            <?php else : ?>
                                <div class="ene-thumb-placeholder">
                                    <span class="dashicons dashicons-format-image"></span>
                                    <div><?php esc_html_e( 'Click to select an image', 'emerge-mono' ); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="ene-thumb-id" value="<?php echo esc_attr($thumb_id); ?>">
                        <?php if ($thumb_url) : ?>
                            <button type="button" class="ene-remove-thumb" onclick="eneRemoveThumb()"><?php esc_html_e( 'Remove image', 'emerge-mono' ); ?></button>
                        <?php endif; ?>
                    </div>

                    <!-- ギャラリー画像 -->
                    <div class="ene-side-section" id="ene-gallery-section" style="<?php echo $type !== 'works' ? 'display:none' : ''; ?>">
                        <div class="ene-side-title"><?php esc_html_e( 'Gallery', 'emerge-mono' ); ?></div>
                        <div class="ene-field-desc" style="font-size:11px;opacity:.6;margin-bottom:10px"><?php esc_html_e( 'Add images and videos. Drag to reorder.', 'emerge-mono' ); ?></div>
                        <?php
                        $gallery_json = $edit_post ? get_post_meta($edit_post->ID, 'en_gallery_items', true) : '';
                        // 旧形式（en_gallery_ids）からの移行
                        if ( ! $gallery_json ) {
                            $old_ids = $edit_post ? get_post_meta($edit_post->ID, 'en_gallery_ids', true) : '';
                            if ( $old_ids ) {
                                $items = array();
                                foreach ( explode(',', $old_ids) as $id ) {
                                    $id = trim($id);
                                    if ($id) $items[] = array('type'=>'image','id'=>(int)$id);
                                }
                                $gallery_json = json_encode($items);
                            }
                        }
                        $gallery_items = $gallery_json ? json_decode($gallery_json, true) : array();
                        if (!is_array($gallery_items)) $gallery_items = array();
                        ?>
                        <input type="hidden" id="ene-gallery-items" value="<?php echo esc_attr($gallery_json ?: '[]'); ?>">
                        <div id="ene-gallery-list" style="display:flex;flex-direction:column;gap:6px;margin-bottom:10px">
                            <?php foreach ($gallery_items as $item) :
                                if ($item['type'] === 'image') :
                                    $gurl = wp_get_attachment_image_url($item['id'], 'thumbnail');
                                    if (!$gurl) continue; ?>
                                    <div class="ene-gallery-item" data-type="image" data-id="<?php echo esc_attr($item['id']); ?>" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);padding:6px;border-radius:4px;cursor:grab">
                                        <img src="<?php echo esc_url($gurl); ?>" style="width:48px;height:36px;object-fit:cover;flex-shrink:0">
                                        <span style="font-size:11px;opacity:.5;flex:1">Image #<?php echo esc_html($item['id']); ?></span>
                                        <button type="button" onclick="eneRemoveGalleryItem(this)" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:14px">×</button>
                                    </div>
                                <?php elseif ($item['type'] === 'video') : ?>
                                    <div class="ene-gallery-item" data-type="video" data-url="<?php echo esc_attr($item['url']); ?>" style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.04);padding:6px;border-radius:4px;cursor:grab">
                                        <div style="width:48px;height:36px;background:rgba(255,255,255,.08);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:16px">▶</div>
                                        <span style="font-size:11px;opacity:.5;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo esc_html($item['url']); ?></span>
                                        <button type="button" onclick="eneRemoveGalleryItem(this)" style="background:none;border:none;color:rgba(255,255,255,.4);cursor:pointer;font-size:14px">×</button>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                        </div>
                        <div style="display:flex;gap:6px">
                            <button type="button" class="ene-cat-add-btn" onclick="eneAddGalleryImage()" style="flex:1"><?php esc_html_e( '+ Image', 'emerge-mono' ); ?></button>
                            <button type="button" class="ene-cat-add-btn" onclick="eneAddGalleryVideo()" style="flex:1"><?php esc_html_e( '+ Video', 'emerge-mono' ); ?></button>
                        </div>
                        <!-- 動画URL入力エリア（非表示） -->
                        <div id="ene-video-input-wrap" style="display:none;margin-top:8px">
                            <input type="text" id="ene-video-url-input" class="ene-input" placeholder="<?php echo esc_attr__( 'YouTube / Vimeo URL', 'emerge-mono' ); ?>" style="margin-bottom:6px">
                            <div style="display:flex;gap:6px">
                                <button type="button" class="ene-cat-add-btn" onclick="eneConfirmGalleryVideo()" style="flex:1"><?php esc_html_e( 'Add', 'emerge-mono' ); ?></button>
                                <button type="button" class="ene-cat-add-btn" onclick="eneCancelGalleryVideo()" style="flex:1;opacity:.5"><?php esc_html_e( 'Cancel', 'emerge-mono' ); ?></button>
                            </div>
                        </div>
                    </div>

                    <!-- カテゴリー -->
                    <div class="ene-side-section">
                        <div class="ene-side-title"><?php esc_html_e( 'Category', 'emerge-mono' ); ?></div>

                        <!-- Works カテゴリー -->
                        <div id="ene-cat-works" style="<?php echo $type !== 'works' ? 'display:none' : ''; ?>">
                            <?php
                            $current_work_cats = $edit_post ? wp_get_post_terms($edit_post->ID,'en_work_category',array('fields'=>'ids')) : array();
                            ?>
                            <?php if ( empty($work_cats) || is_wp_error($work_cats) ) : ?>
                                <div class="ene-no-cat">No categories.<br>Add them from the Works category page.</div>
                            <?php else : ?>
                                <?php foreach ( $work_cats as $cat ) : ?>
                                <label class="ene-check-label">
                                    <input type="checkbox" class="ene-work-cat" value="<?php echo esc_attr($cat->term_id); ?>"
                                           <?php echo in_array($cat->term_id, $current_work_cats) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- お知らせ カテゴリー -->
                        <div id="ene-cat-news" style="<?php echo $type !== 'news' ? 'display:none' : ''; ?>">
                            <?php
                            $current_news_cats = $edit_post ? wp_get_post_terms($edit_post->ID,'en_news_category',array('fields'=>'ids')) : array();
                            ?>
                            <?php if ( empty($news_cats) ) : ?>
                                <div class="ene-no-cat">No categories.</div>
                            <?php else : ?>
                                <?php foreach ( $news_cats as $cat ) : ?>
                                <label class="ene-check-label">
                                    <input type="checkbox" class="ene-news-cat" value="<?php echo esc_attr($cat->term_id); ?>"
                                           <?php echo in_array($cat->term_id, $current_news_cats) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 公開設定 -->
                    <div class="ene-side-section">
                        <div class="ene-side-title"><?php esc_html_e( 'Visibility', 'emerge-mono' ); ?></div>
                        <select id="ene-status" class="ene-select">
                            <option value="publish" <?php echo ($edit_post && $edit_post->post_status==='publish') ? 'selected' : ''; ?>>Published</option>
                            <option value="draft"   <?php echo ($edit_post && $edit_post->post_status==='draft')   ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>

                    <!-- 送信ボタン -->
                    <div class="ene-actions">
                        <button type="button" id="ene-submit" class="ene-submit-btn" onclick="eneSubmit()">
                            <?php echo $edit_id ? esc_html__( 'Update', 'emerge-mono' ) : esc_html__( 'Publish', 'emerge-mono' ); ?>
                        </button>
                        <?php if ($edit_id) : ?>
                            <a href="?page=ene-post" class="ene-cancel-btn"><?php esc_html_e( 'Cancel', 'emerge-mono' ); ?></a>
                        <?php endif; ?>
                    </div>
                    <div id="ene-msg" class="ene-msg"></div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

// ── Works一覧ページ ──
function emono_ed_page_works() {
    $paged = isset($_GET['paged']) ? (int)$_GET['paged'] : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    $posts = get_posts( array(
        'post_type'      => 'en_work',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'post_status'    => array('publish','draft'),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    $work_cats = get_terms( array( 'taxonomy' => 'en_work_category', 'hide_empty' => false ) );
    if ( ! is_array($work_cats) ) $work_cats = array();
    ?>
    <div class="ene-wrap">
        <div class="ene-header">
            <div class="ene-header-inner">
                <div class="ene-header-title"><?php esc_html_e( 'All Works', 'emerge-mono' ); ?></div>
                <a href="?page=ene-post&type=works" class="ene-new-btn"><?php esc_html_e( '+ Add New Work', 'emerge-mono' ); ?></a>
            </div>
        </div>
        <div class="ene-cat-bar">
            <div class="ene-cat-bar-inner">
                <div class="ene-cat-bar-title"><?php esc_html_e( 'Works Categories', 'emerge-mono' ); ?></div>
                <div class="ene-cat-list" id="ene-work-cat-list">
                    <?php foreach ( $work_cats as $cat ) : ?>
                    <span class="ene-cat-tag">
                        <?php echo esc_html($cat->name); ?>
                        <button type="button" class="ene-cat-delete" onclick="eneDeleteCat(<?php echo (int) $cat->term_id; ?>, 'en_work_category', this)" title="Delete">×</button>
                    </span>
                    <?php endforeach; ?>
                    <?php if ( empty($work_cats) ) : ?>
                        <span class="ene-cat-empty">No categories yet</span>
                    <?php endif; ?>
                </div>
                <div class="ene-cat-add">
                    <input type="text" id="ene-work-cat-input" class="ene-cat-input" placeholder="<?php echo esc_attr__( 'Enter a new category name', 'emerge-mono' ); ?>">
                    <button type="button" class="ene-cat-add-btn" onclick="eneAddCat('en_work_category', 'ene-work-cat-input', 'ene-work-cat-list')">Add</button>
                </div>
            </div>
        </div>
        <div class="ene-body" style="display:block">
            <?php if ( empty($posts) ) : ?>
                <div class="ene-empty">No works yet. Add one from "Post".</div>
            <?php else : ?>
            <table class="ene-table">
                <thead>
                    <tr>
                        <th style="width:80px"><?php esc_html_e( 'Image', 'emerge-mono' ); ?></th>
                        <th><?php esc_html_e( 'Title', 'emerge-mono' ); ?></th>
                        <th style="width:100px"><?php esc_html_e( 'Category', 'emerge-mono' ); ?></th>
                        <th style="width:80px"><?php esc_html_e( 'Status', 'emerge-mono' ); ?></th>
                        <th style="width:120px"><?php esc_html_e( 'Date', 'emerge-mono' ); ?></th>
                        <th style="width:120px"><?php esc_html_e( 'Actions', 'emerge-mono' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $posts as $post ) :
                        $thumb = get_the_post_thumbnail_url($post->ID,'thumbnail');
                        $cats  = get_the_terms($post->ID,'en_work_category');
                        $cat   = ($cats && !is_wp_error($cats)) ? implode(', ', wp_list_pluck($cats,'name')) : '—';
                        $status_label = $post->post_status === 'publish' ? __( 'Published', 'emerge-mono' ) : __( 'Draft', 'emerge-mono' );
                        $status_class = $post->post_status === 'publish' ? 'ene-badge-pub' : 'ene-badge-draft';
                    ?>
                    <tr>
                        <td>
                            <?php if ($thumb) : ?>
                                <img src="<?php echo esc_url($thumb); ?>" style="width:60px;height:40px;object-fit:cover;border-radius:4px">
                            <?php else : ?>
                                <div style="width:60px;height:40px;background:rgba(255,255,255,.05);border-radius:4px;border:1px solid rgba(255,255,255,.08)"></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?page=ene-post&type=works&edit=<?php echo (int) $post->ID; ?>" class="ene-table-title">
                                <?php echo esc_html($post->post_title ?: __( '(No title)', 'emerge-mono' )); ?>
                            </a>
                        </td>
                        <td style="font-size:12px;color:rgba(255,255,255,.4)"><?php echo esc_html($cat); ?></td>
                        <td><span class="ene-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
                        <td style="font-size:12px;color:rgba(255,255,255,.3)"><?php echo get_the_date('Y.m.d', $post->ID); ?></td>
                        <td>
                            <a href="?page=ene-post&type=works&edit=<?php echo (int) $post->ID; ?>" class="ene-table-btn"><?php esc_html_e( 'Edit', 'emerge-mono' ); ?></a>
                            <button class="ene-table-btn ene-delete-btn" onclick="eneDelete(<?php echo (int) $post->ID; ?>, this)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// ── お知らせ一覧ページ ──
function emono_ed_page_news() {
    $paged = isset($_GET['paged']) ? (int)$_GET['paged'] : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    $posts = get_posts( array(
        'post_type'      => 'en_news',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'post_status'    => array('publish','draft'),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
    $news_cats = get_terms( array( 'taxonomy' => 'en_news_category', 'hide_empty' => false ) );
    if ( ! is_array($news_cats) ) $news_cats = array();
    ?>
    <div class="ene-wrap">
        <div class="ene-header">
            <div class="ene-header-inner">
                <div class="ene-header-title"><?php esc_html_e( 'All News', 'emerge-mono' ); ?></div>
                <a href="?page=ene-post&type=news" class="ene-new-btn"><?php esc_html_e( '+ Add New Post', 'emerge-mono' ); ?></a>
            </div>
        </div>
        <div class="ene-cat-bar">
            <div class="ene-cat-bar-inner">
                <div class="ene-cat-bar-title"><?php esc_html_e( 'News Categories', 'emerge-mono' ); ?></div>
                <div class="ene-cat-list" id="ene-news-cat-list">
                    <?php foreach ( $news_cats as $cat ) : ?>
                    <span class="ene-cat-tag">
                        <?php echo esc_html($cat->name); ?>
                        <button type="button" class="ene-cat-delete" onclick="eneDeleteCat(<?php echo (int) $cat->term_id; ?>, 'en_news_category', this)" title="Delete">×</button>
                    </span>
                    <?php endforeach; ?>
                    <?php if ( empty($news_cats) ) : ?>
                        <span class="ene-cat-empty">No categories yet</span>
                    <?php endif; ?>
                </div>
                <div class="ene-cat-add">
                    <input type="text" id="ene-news-cat-input" class="ene-cat-input" placeholder="<?php echo esc_attr__( 'Enter a new category name', 'emerge-mono' ); ?>">
                    <button type="button" class="ene-cat-add-btn" onclick="eneAddCat('en_news_category', 'ene-news-cat-input', 'ene-news-cat-list')">Add</button>
                </div>
            </div>
        </div>
        <div class="ene-body" style="display:block">
            <?php if ( empty($posts) ) : ?>
                <div class="ene-empty">No news yet. Add one from "Post".</div>
            <?php else : ?>
            <table class="ene-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Title', 'emerge-mono' ); ?></th>
                        <th style="width:100px"><?php esc_html_e( 'Category', 'emerge-mono' ); ?></th>
                        <th style="width:80px"><?php esc_html_e( 'Status', 'emerge-mono' ); ?></th>
                        <th style="width:120px"><?php esc_html_e( 'Date', 'emerge-mono' ); ?></th>
                        <th style="width:120px"><?php esc_html_e( 'Actions', 'emerge-mono' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $posts as $post ) :
                        $cats  = get_the_terms($post->ID, 'en_news_category');
                        $cat   = ( $cats && ! is_wp_error($cats) ) ? $cats[0]->name : '—';
                        $status_label = $post->post_status === 'publish' ? __( 'Published', 'emerge-mono' ) : __( 'Draft', 'emerge-mono' );
                        $status_class = $post->post_status === 'publish' ? 'ene-badge-pub' : 'ene-badge-draft';
                    ?>
                    <tr>
                        <td>
                            <a href="?page=ene-post&type=news&edit=<?php echo (int) $post->ID; ?>" class="ene-table-title">
                                <?php echo esc_html($post->post_title ?: __( '(No title)', 'emerge-mono' )); ?>
                            </a>
                        </td>
                        <td style="font-size:12px;color:rgba(255,255,255,.4)"><?php echo esc_html($cat); ?></td>
                        <td><span class="ene-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
                        <td style="font-size:12px;color:rgba(255,255,255,.3)"><?php echo get_the_date('Y.m.d', $post->ID); ?></td>
                        <td>
                            <a href="?page=ene-post&type=news&edit=<?php echo (int) $post->ID; ?>" class="ene-table-btn"><?php esc_html_e( 'Edit', 'emerge-mono' ); ?></a>
                            <button class="ene-table-btn ene-delete-btn" onclick="eneDelete(<?php echo (int) $post->ID; ?>, this)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

// ── TOP編集 ──
function emono_ed_page_top() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $opts        = emono_get_options();
    $top_layout  = isset( $opts['top_layout'] ) ? sanitize_key( $opts['top_layout'] ) : 'mono';
    $top_layouts = function_exists( 'emono_get_top_layouts' ) ? emono_get_top_layouts() : array(
        'mono' => array(
            'label'       => __( 'Minimal Top', 'emerge-mono' ),
            'description' => __( 'Minimal portfolio top page with logo, site name, tagline, and buttons.', 'emerge-mono' ),
        ),
    );

    if ( ! isset( $top_layouts[ $top_layout ] ) ) {
        $top_layout = 'mono';
    }

    $front_url = home_url( '/' );
    $saved     = isset( $_GET['saved'] ) ? sanitize_key( wp_unslash( $_GET['saved'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success flag.
    ?>
    <div class="ene-wrap ene-top-editor">
        <div class="ene-header">
            <div class="ene-header-inner">
                <div class="ene-header-title"><?php esc_html_e( 'TOP Editor', 'emerge-mono' ); ?></div>
                <div class="ene-header-actions">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=emerge-mono' ) ); ?>" class="ene-back-btn"><?php esc_html_e( 'Back to Settings', 'emerge-mono' ); ?></a>
                    <a href="<?php echo esc_url( $front_url ); ?>" target="_blank" class="ene-new-btn"><?php esc_html_e( 'Preview', 'emerge-mono' ); ?></a>
                </div>
            </div>
        </div>

        <?php if ( $saved === '1' ) : ?>
            <div class="ene-top-editor-notice"><?php esc_html_e( 'Saved.', 'emerge-mono' ); ?></div>
        <?php endif; ?>

        <form method="post" action="" class="ene-top-editor-form">
            <?php wp_nonce_field( 'en_save_top_editor', 'en_nonce' ); ?>
            <input type="hidden" name="en_action" value="top_editor">

            <div class="ene-top-editor-grid">
                <main class="ene-top-editor-main">
                    <section class="ene-type-bar ene-top-editor-layouts">
                        <div class="ene-type-bar-label"><?php esc_html_e( 'Top Page Layout', 'emerge-mono' ); ?></div>
                        <div class="ene-type-bar-btns">
                            <?php foreach ( $top_layouts as $layout_key => $layout ) : ?>
                                <?php
                                $layout_key  = sanitize_key( $layout_key );
                                $label       = isset( $layout['label'] ) ? $layout['label'] : $layout_key;
                                $description = isset( $layout['description'] ) ? $layout['description'] : '';
                                ?>
                                <label class="ene-type-bar-btn ene-top-layout-card <?php echo esc_attr( $top_layout === $layout_key ? 'active' : '' ); ?>">
                                    <input type="radio" name="top_layout" value="<?php echo esc_attr( $layout_key ); ?>" <?php checked( $top_layout, $layout_key ); ?>>
                                    <span class="dashicons dashicons-layout"></span>
                                    <span>
                                        <span class="ene-top-layout-title"><?php echo esc_html( $label ); ?></span>
                                        <?php if ( $description ) : ?>
                                            <span class="ene-type-desc"><?php echo esc_html( $description ); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="ene-top-editor-empty" data-top-layout-settings="mono" <?php if ( $top_layout !== 'mono' ) : ?>hidden<?php endif; ?>>
                        <div class="ene-side-section">
                            <div class="ene-side-title"><?php esc_html_e( 'Minimal Top', 'emerge-mono' ); ?></div>
                            <p><?php esc_html_e( 'Minimal Top uses the shared site name, logo, tagline, and top buttons from Site Settings.', 'emerge-mono' ); ?></p>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=emerge-mono' ) ); ?>" class="ene-table-btn"><?php esc_html_e( 'Edit shared settings', 'emerge-mono' ); ?></a>
                        </div>
                    </section>

                    <?php do_action( 'emono_top_layout_settings', $top_layout, $opts ); ?>
                </main>

                <aside class="ene-top-editor-sidebar">
                    <div class="ene-side-section">
                        <div class="ene-side-title">
                            <?php esc_html_e( 'Preview', 'emerge-mono' ); ?>
                            <span class="ene-top-preview-status" data-preview-status hidden></span>
                        </div>
                        <div class="ene-top-preview-frame">
                            <iframe id="ene-top-preview-iframe" src="<?php echo esc_url( $front_url ); ?>" title="<?php echo esc_attr__( 'Top page preview', 'emerge-mono' ); ?>"></iframe>
                        </div>
                        <div class="ene-field-desc"><?php esc_html_e( 'The preview updates automatically as you edit. Your changes are not saved until you press Save.', 'emerge-mono' ); ?></div>
                        <a href="<?php echo esc_url( $front_url ); ?>" target="_blank" class="ene-table-btn"><?php esc_html_e( 'Open in new tab', 'emerge-mono' ); ?></a>
                    </div>

                    <div class="ene-actions">
                        <button type="submit" class="ene-submit-btn"><?php esc_html_e( 'Save', 'emerge-mono' ); ?></button>
                    </div>
                </aside>
            </div>
        </form>
    </div>
    <?php
    $preview_nonce = wp_create_nonce( EMONO_TOP_PREVIEW_NONCE );
    wp_localize_script( 'emerge-mono-admin', 'emonoTopPreview', array(
        'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
        'action'      => 'emono_top_preview',
        'nonce'       => $preview_nonce,
        'previewBase' => $front_url,
        'i18n'        => array(
            'updating' => __( 'Updating…', 'emerge-mono' ),
            'live'     => __( 'Live', 'emerge-mono' ),
            'error'    => __( 'Preview failed', 'emerge-mono' ),
        ),
    ) );
    wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_TOP_EDITOR_JS'
(function() {
    var root = document.querySelector('.ene-top-editor');
    if (!root) { return; }
    var form = root.querySelector('.ene-top-editor-form');

    function updateTopEditor() {
        var selected = root.querySelector('input[name="top_layout"]:checked');
        var layout = selected ? selected.value : '';

        root.querySelectorAll('.ene-top-layout-card').forEach(function(card) {
            var input = card.querySelector('input[name="top_layout"]');
            card.classList.toggle('active', input && input.value === layout);
        });

        root.querySelectorAll('[data-top-layout-settings]').forEach(function(section) {
            section.hidden = section.getAttribute('data-top-layout-settings') !== layout;
        });
    }

    root.querySelectorAll('input[name="top_layout"]').forEach(function(input) {
        input.addEventListener('change', updateTopEditor);
    });
    updateTopEditor();

    // ── ライブプレビュー ──
    var cfg = window.emonoTopPreview;
    var iframe = document.getElementById('ene-top-preview-iframe');
    if (!cfg || !form || !iframe || typeof window.fetch === 'undefined' || typeof FormData === 'undefined') {
        return;
    }
    var i18n = cfg.i18n || {};
    var statusEl = root.querySelector('[data-preview-status]');
    var frame = root.querySelector('.ene-top-preview-frame');
    var timer = null;
    var reqToken = 0;

    // デスクトップ幅（1280px）で描画し、枠幅に合わせて縮小表示する。
    var BASE_W = 1280, BASE_H = 800;
    function fitPreview() {
        if (!frame) { return; }
        var w = frame.clientWidth;
        if (!w) { return; }
        var scale = w / BASE_W;
        iframe.style.transform = 'scale(' + scale + ')';
        frame.style.height = (BASE_H * scale) + 'px';
    }
    window.addEventListener('resize', fitPreview);
    iframe.addEventListener('load', fitPreview);
    fitPreview();

    function setStatus(text, state) {
        if (!statusEl) { return; }
        if (!text) { statusEl.hidden = true; statusEl.textContent = ''; return; }
        statusEl.hidden = false;
        statusEl.textContent = text;
        statusEl.setAttribute('data-state', state || '');
    }

    function previewUrl() {
        var base = cfg.previewBase || '/';
        var sep = base.indexOf('?') === -1 ? '?' : '&';
        return base + sep + 'emono_top_preview=1&emono_preview_nonce=' +
            encodeURIComponent(cfg.nonce) + '&t=' + Date.now();
    }

    function reloadPreview() {
        try { iframe.src = previewUrl(); } catch (e) {}
    }

    function pushPreview() {
        var token = ++reqToken;
        setStatus(i18n.updating || 'Updating…', 'busy');
        var data = new FormData(form);
        // フォームの保存トリガーは絶対に送らない（誤保存＆リダイレクト防止）。
        data.delete('en_action');
        data.delete('en_nonce');
        data.append('action', cfg.action);
        data.append('nonce', cfg.nonce);
        window.fetch(cfg.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function(res) {
            return res.json();
        }).then(function(json) {
            if (token !== reqToken) { return; }
            if (json && json.success) {
                reloadPreview();
                setStatus(i18n.live || 'Live', 'ok');
            } else {
                setStatus(i18n.error || 'Preview failed', 'error');
            }
        }).catch(function() {
            if (token !== reqToken) { return; }
            setStatus(i18n.error || 'Preview failed', 'error');
        });
    }

    function schedulePreview() {
        if (timer) { clearTimeout(timer); }
        timer = setTimeout(pushPreview, 700);
    }

    form.addEventListener('input', schedulePreview);
    form.addEventListener('change', schedulePreview);

    // 初期表示も保存前状態を反映しておく。
    pushPreview();
}());
EMONO_TOP_EDITOR_JS
    );
}

// ── ページ管理 ──
function emono_ed_page_create() {
    $em_pages = function_exists('emono_get_em_page_defs') ? emono_get_em_page_defs() : array(
        array( 'sc' => '[emerge_mono_top]',      'title' => 'Home',             'slug' => '',              'desc' => __( 'Top page — Minimal Top or another selected layout', 'emerge-mono' ),      'icon' => '🏠' ),
        array( 'sc' => '[emerge_mono_about]',     'title' => 'Profile',          'slug' => 'about',         'desc' => __( 'Profile page — bio, social links', 'emerge-mono' ),       'icon' => '👤' ),
        array( 'sc' => '[emerge_mono_works]',     'title' => 'Works',            'slug' => 'works',         'desc' => __( 'Works page — portfolio grid', 'emerge-mono' ),          'icon' => '📂' ),
        array( 'sc' => '[emerge_mono_news]',      'title' => 'News',             'slug' => 'news',          'desc' => __( 'News page — post list', 'emerge-mono' ),               'icon' => '📰' ),
        array( 'sc' => '[emerge_mono_contact]',   'title' => 'Contact',          'slug' => 'contact',       'desc' => __( 'Contact page — form', 'emerge-mono' ),           'icon' => '✉' ),
        array( 'sc' => '[emerge_mono_privacy]',   'title' => 'Privacy Policy',   'slug' => 'privacy-policy','desc' => __( 'Privacy policy page', 'emerge-mono' ),               'icon' => '🔒' ),
        array( 'sc' => '[emerge_mono_terms]',     'title' => 'Terms of Service', 'slug' => 'terms',         'desc' => __( 'Terms of service page', 'emerge-mono' ),                          'icon' => '📋' ),
        array( 'sc' => '[emerge_mono_estimate]',  'title' => 'Estimate',         'slug' => 'estimate',      'desc' => __( 'Estimate simulator page', 'emerge-mono' ),            'icon' => '💰' ),
    );
    wp_localize_script( 'ene-page-manager', 'enePageSettings', array(
        'createNonce' => wp_create_nonce( 'ene_create_em_page' ),
        'i18n'        => array(
            /* translators: %s: the page title */
            'confirmDelete'  => __( 'Delete "%s"?\nThis cannot be undone.', 'emerge-mono' ),
            'deleting'       => __( 'Deleting...', 'emerge-mono' ),
            /* translators: %s: the page title */
            'deletedMsg'     => '🗑 ' . __( 'Deleted "%s".', 'emerge-mono' ),
            'del'            => __( 'Delete', 'emerge-mono' ),
            'errorPrefix'    => __( 'Error: ', 'emerge-mono' ),
            'couldNotDelete' => __( 'Could not delete', 'emerge-mono' ),
            'creating'       => __( 'Creating...', 'emerge-mono' ),
            /* translators: %s: the page title */
            'createdMsg'     => '✅ %s ' . __( 'created.', 'emerge-mono' ),
            'create'         => __( 'Create', 'emerge-mono' ),
            'couldNotCreate' => __( 'Could not create', 'emerge-mono' ),
        ),
    ) );

    $existing_pages = get_pages( array( 'post_status' => array('publish','draft'), 'sort_column' => 'menu_order' ) );
    $sc_page_map = array();
    foreach ( $existing_pages as $page ) {
        foreach ( $em_pages as $def ) {
            if ( strpos($page->post_content, $def['sc']) !== false ) {
                $sc_page_map[$def['sc']] = $page;
            }
        }
    }
    ?>
    <div class="ene-wrap">
        <div class="ene-header">
            <div class="ene-header-inner">
                <div class="ene-header-title"><?php esc_html_e( 'Page Manager', 'emerge-mono' ); ?></div>
                <?php if ( function_exists('emono_wizard_url') ) : ?>
                <a href="<?php echo esc_url( emono_wizard_url() ); ?>" class="ene-back-btn"><?php esc_html_e( 'Open Setup Wizard', 'emerge-mono' ); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="ene-body" style="display:block;padding:24px;">
            <div style="font-size:12px;color:rgba(255,255,255,.4);margin-bottom:20px;"><?php esc_html_e( 'Create and manage Emerge Mono pages. Clicking "Create" generates a page with the shortcode already set.', 'emerge-mono' ); ?></div>
            <table class="ene-table">
                <thead>
                    <tr>
                        <th style="width:40px"></th>
                        <th><?php esc_html_e( 'Page Name', 'emerge-mono' ); ?></th>
                        <th><?php esc_html_e( 'Shortcode', 'emerge-mono' ); ?></th>
                        <th style="width:100px">Status</th>
                        <th style="width:150px"><?php esc_html_e( 'Actions', 'emerge-mono' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $em_pages as $def ) :
                        $page         = isset($sc_page_map[$def['sc']]) ? $sc_page_map[$def['sc']] : null;
                        $created      = ! is_null($page);
                        $page_url     = $created ? get_permalink($page->ID) : '';
                        $status_label = $created ? ($page->post_status === 'publish' ? __( 'Published', 'emerge-mono' ) : __( 'Draft', 'emerge-mono' )) : __( 'Not created', 'emerge-mono' );
                        $status_class = $created ? ($page->post_status === 'publish' ? 'ene-badge-pub' : 'ene-badge-draft') : 'ene-badge-none';
                    ?>
                    <tr>
                        <td style="text-align:center;font-size:16px"><?php echo esc_html( $def['icon'] ); ?></td>
                        <td>
                            <div style="font-size:13px;font-weight:500"><?php echo esc_html($def['title']); ?></div>
                            <div style="font-size:11px;color:rgba(255,255,255,.3);margin-top:2px"><?php echo esc_html($def['desc']); ?></div>
                        </td>
                        <td><code style="font-size:11px;background:rgba(255,255,255,.06);padding:2px 6px;border-radius:3px"><?php echo esc_html($def['sc']); ?></code></td>
                        <td><span class="ene-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
                        <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                            <?php if ( $created ) : ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=ene-page-edit&edit='.$page->ID)); ?>" class="ene-table-btn"><?php esc_html_e( 'Edit', 'emerge-mono' ); ?></a>
                                <a href="<?php echo esc_url($page_url); ?>" target="_blank" class="ene-table-btn"><?php esc_html_e( 'View', 'emerge-mono' ); ?></a>
                                <button class="ene-table-btn ene-delete-btn" onclick="eneDeleteEmPage(<?php echo (int) $page->ID; ?>, '<?php echo esc_js($def['title']); ?>', this)">Delete</button>
                            <?php else : ?>
                                <button class="ene-table-btn" onclick="eneCreatePage(<?php echo esc_attr(json_encode($def)); ?>, this)">Create</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div id="ene-page-msg" style="margin-top:16px;font-size:12px;"></div>
        </div>
    </div>    <?php
}

// ── ページ作成 AJAX ──
add_action( 'wp_ajax_ene_create_em_page', 'emono_ed_ajax_create_em_page' );
function emono_ed_ajax_create_em_page() {
    global $wpdb;
    if ( ! check_ajax_referer('ene_create_em_page', 'nonce', false) || ! current_user_can('edit_pages') ) {
        wp_send_json_error('Permission denied');
    }
    $title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
    $slug  = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
    $sc    = sanitize_text_field( wp_unslash( $_POST['sc'] ?? '' ) );
    if ( ! $title || ! $sc ) {
        wp_send_json_error('Invalid parameters');
    }

    $desired_slug = $slug ?: sanitize_title( $title );

    // 希望スラッグがゴミ箱/下書き等の残骸に占有されていれば解放（terms-2 化の防止）
    if ( function_exists('emono_wizard_reclaim_slug') ) {
        emono_wizard_reclaim_slug( $desired_slug, $sc );
    }

    $page_id = wp_insert_post( array(
        'post_title'   => $title,
        'post_name'    => $desired_slug,
        'post_content' => $sc,
        'post_status'  => 'publish',
        'post_type'    => 'page',
    ) );
    if ( is_wp_error($page_id) ) {
        wp_send_json_error( $page_id->get_error_message() );
    }

    // 挿入後にsuffixが付いていたら、実在する固定ページに使われていない限り直接DBで強制修正
    if ( function_exists('emono_wizard_slug_taken_by_live_page') ) {
        $actual_slug = get_post_field( 'post_name', $page_id );
        if ( $actual_slug !== $desired_slug && ! emono_wizard_slug_taken_by_live_page( $desired_slug, $page_id ) ) {
            $wpdb->update( $wpdb->posts, array( 'post_name' => $desired_slug ), array( 'ID' => (int) $page_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom log/table operation; caching not applicable.
            clean_post_cache( $page_id );
        }
    }

    if ( $sc === '[emerge_mono_top]' ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_id );
    }
    wp_send_json_success( array( 'page_id' => $page_id ) );
}

// ── ページ一覧（統合済みのためリダイレクト） ──
function emono_ed_page_list() {
    wp_safe_redirect( admin_url('admin.php?page=ene-page-create') );
    exit;
}

// ── ページ編集画面 ──
function emono_ed_page_edit() {
    $edit_id   = isset($_GET['edit']) ? (int)$_GET['edit'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    $edit_post = $edit_id ? get_post($edit_id) : null;

    if ( ! $edit_post ) {
        wp_safe_redirect( admin_url('admin.php?page=ene-page-create') );
        exit;
    }

    $thumb_id  = get_post_thumbnail_id($edit_post->ID);
    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id,'medium') : '';
    $slug      = $edit_post->post_name;
    $status    = $edit_post->post_status;
    ?>
    <div class="ene-wrap">
        <div class="ene-header">
            <div class="ene-header-inner">
                <div class="ene-header-title"><?php esc_html_e( 'Edit Page', 'emerge-mono' ); ?></div>
                <a href="<?php echo esc_url( admin_url('admin.php?page=ene-page-create') ); ?>" class="ene-back-btn">← Back to Page Manager</a>
            </div>
        </div>
        <div class="ene-body">
            <div class="ene-main">

                <div class="ene-field">
                    <label class="ene-label"><?php esc_html_e( 'Page Title', 'emerge-mono' ); ?> <span class="ene-req">*</span></label>
                    <input type="text" id="enp-title" class="ene-input"
                           value="<?php echo esc_attr($edit_post->post_title); ?>">
                </div>

                <div class="ene-field">
                    <label class="ene-label"><?php esc_html_e( 'URL (slug)', 'emerge-mono' ); ?></label>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:12px;opacity:.3"><?php echo esc_url(home_url('/')); ?></span>
                        <input type="text" id="enp-slug" class="ene-input"
                               value="<?php echo esc_attr($slug); ?>"
                               style="flex:1">
                    </div>
                </div>

            </div>

            <div class="ene-sidebar">

                <div class="ene-side-section">
                    <div class="ene-side-title"><?php esc_html_e( 'Featured Image', 'emerge-mono' ); ?></div>
                    <div id="enp-thumb-preview" class="ene-thumb-preview" onclick="enpOpenMedia()">
                        <?php if ($thumb_url) : ?>
                            <img src="<?php echo esc_url($thumb_url); ?>">
                            <div class="ene-thumb-overlay"><?php esc_html_e( 'Change', 'emerge-mono' ); ?></div>
                        <?php else : ?>
                            <div class="ene-thumb-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                                <div><?php esc_html_e( 'Click to select an image', 'emerge-mono' ); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" id="enp-thumb-id" value="<?php echo esc_attr($thumb_id); ?>">
                    <?php if ($thumb_url) : ?>
                        <button type="button" class="ene-remove-thumb" onclick="enpRemoveThumb()"><?php esc_html_e( 'Remove image', 'emerge-mono' ); ?></button>
                    <?php endif; ?>
                </div>

                <div class="ene-side-section">
                    <div class="ene-side-title"><?php esc_html_e( 'Visibility', 'emerge-mono' ); ?></div>
                    <select id="enp-status" class="ene-select">
                        <option value="publish" <?php selected($status,'publish'); ?>>Published</option>
                        <option value="draft"   <?php selected($status,'draft'); ?>>Draft</option>
                    </select>
                </div>

                <div class="ene-actions">
                    <button type="button" id="enp-submit" class="ene-submit-btn" onclick="enpSubmit(<?php echo (int) $edit_id; ?>)">
                        <?php esc_html_e( 'Update', 'emerge-mono' ); ?>
                    </button>
                    <a href="<?php echo esc_url( admin_url('admin.php?page=ene-page-create') ); ?>" class="ene-cancel-btn"><?php esc_html_e( 'Cancel', 'emerge-mono' ); ?></a>
                </div>
                <div id="enp-msg" class="ene-msg"></div>

            </div>
        </div>
    </div>
    <?php
}
