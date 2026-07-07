<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_tab_design($opts) {
    emono_admin_notice();
    $bg     = isset($opts['design_bg'])    ? $opts['design_bg']    : '#000000';
    $text   = isset($opts['design_text'])  ? $opts['design_text']  : '#ffffff';
    $accent = isset($opts['design_accent'])? $opts['design_accent']: '#ffffff';
    $font   = isset($opts['design_font'])  ? $opts['design_font']  : 'System Mono';
    $mode   = isset($opts['design_mode'])  ? $opts['design_mode']  : 'dark';

    // フォントリスト（システムフォントのみ。外部読み込みなし。加えてカスタムフォントのアップロードも可能）
    $fonts = array(
        'System Mono'   => 'System Monospace ' . __( '(default)', 'emerge-mono-portfolio' ),
        'System Sans'   => 'System Sans-serif',
        'System Serif'  => 'System Serif',
    );

    // カスタムフォント（アップロード済み・複数）を選択肢に追加
    $custom_fonts_list = function_exists('emono_get_custom_fonts') ? emono_get_custom_fonts() : array();
    foreach ( $custom_fonts_list as $cf ) {
        if ( ! empty($cf['name']) && ! isset($fonts[ $cf['name'] ]) ) {
            $fonts[ $cf['name'] ] = $cf['name'] . ' (' . __( 'Custom', 'emerge-mono-portfolio' ) . ')';
        }
    }

    // Map each font key to its CSS font-family stack, so the preview can update
    // live via JS when the dropdown changes.
    $font_stack_map = array();
    foreach ( $fonts as $fkey => $flabel ) {
        $font_stack_map[ $fkey ] = emono_font_stack( $fkey );
    }
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('en_save_design','en_nonce'); ?>
        <input type="hidden" name="en_action" value="design">
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Color Mode', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'Select the site color mode. With Match device setting, the background switches to dark or light automatically based on the visitor device.', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <div style="display:flex;flex-direction:column;gap:10px">
                    <label class="en-mode-label">
                        <input type="radio" name="design_mode" value="dark" <?php checked($mode,'dark'); ?>>
                        <div class="en-mode-preview en-mode-dark">
                            <div class="en-mode-icon">◐</div>
                            <div>
                                <div class="en-mode-title"><?php esc_html_e( 'Always Dark', 'emerge-mono-portfolio' ); ?></div>
                                <div class="en-mode-desc"><?php esc_html_e( 'Always dark background, white text', 'emerge-mono-portfolio' ); ?></div>
                            </div>
                        </div>
                    </label>
                    <label class="en-mode-label">
                        <input type="radio" name="design_mode" value="light" <?php checked($mode,'light'); ?>>
                        <div class="en-mode-preview en-mode-light">
                            <div class="en-mode-icon">○</div>
                            <div>
                                <div class="en-mode-title"><?php esc_html_e( 'Always Light', 'emerge-mono-portfolio' ); ?></div>
                                <div class="en-mode-desc"><?php esc_html_e( 'Always light background, black text', 'emerge-mono-portfolio' ); ?></div>
                            </div>
                        </div>
                    </label>
                    <label class="en-mode-label">
                        <input type="radio" name="design_mode" value="auto" <?php checked($mode,'auto'); ?>>
                        <div class="en-mode-preview en-mode-auto">
                            <div class="en-mode-icon">◑</div>
                            <div>
                                <div class="en-mode-title"><?php esc_html_e( 'Match device setting (auto)', 'emerge-mono-portfolio' ); ?></div>
                                <div class="en-mode-desc"><?php esc_html_e( 'Automatically follows the visitor dark/light mode setting', 'emerge-mono-portfolio' ); ?></div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Color Settings', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'Set the overall site colors. No change needed if you keep the default dark background with white text.', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Background Color', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="design_bg" value="<?php echo esc_attr($bg); ?>" style="width:48px;height:36px;border:1px solid rgba(255,255,255,.15);background:none;cursor:pointer;border-radius:4px">
                    <input type="text" name="design_bg_text" value="<?php echo esc_attr($bg); ?>" class="en-field-input" style="width:120px" placeholder="#000000" oninput="document.querySelector('[name=design_bg]').value=this.value">
                </div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Text Color', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="design_text" value="<?php echo esc_attr($text); ?>" style="width:48px;height:36px;border:1px solid rgba(255,255,255,.15);background:none;cursor:pointer;border-radius:4px">
                    <input type="text" name="design_text_text" value="<?php echo esc_attr($text); ?>" class="en-field-input" style="width:120px" placeholder="#ffffff" oninput="document.querySelector('[name=design_text]').value=this.value">
                </div>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Accent Color (buttons, borders, etc.)', 'emerge-mono-portfolio' ); ?></label>
                <div style="display:flex;gap:10px;align-items:center">
                    <input type="color" name="design_accent" value="<?php echo esc_attr($accent); ?>" style="width:48px;height:36px;border:1px solid rgba(255,255,255,.15);background:none;cursor:pointer;border-radius:4px">
                    <input type="text" name="design_accent_text" value="<?php echo esc_attr($accent); ?>" class="en-field-input" style="width:120px" placeholder="#ffffff" oninput="document.querySelector('[name=design_accent]').value=this.value">
                </div>
            </div>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Font Settings', 'emerge-mono-portfolio' ); ?></div>
            <div class="en-field-desc" style="margin-bottom:16px">
                <?php esc_html_e( 'Select the overall site font. Choose a Japanese-capable font if you have a lot of Japanese content.', 'emerge-mono-portfolio' ); ?>
            </div>
            <div class="en-field-group">
                <label class="en-field-label"><?php esc_html_e( 'Font', 'emerge-mono-portfolio' ); ?></label>
                <select name="design_font" class="en-field-input">
                    <?php foreach ( $fonts as $fkey => $flabel ) : ?>
                        <option value="<?php echo esc_attr($fkey); ?>" <?php selected($font, $fkey); ?>>
                            <?php echo esc_html($flabel); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="en-field-desc">
                <?php esc_html_e( '* All fonts are system fonts or your uploaded font files. No external font service is used.', 'emerge-mono-portfolio' ); ?>
            </div>

            <div class="en-field-group" style="margin-top:24px;border-top:1px solid rgba(255,255,255,.08);padding-top:20px">
                <label class="en-field-label"><?php esc_html_e( 'Custom Font Upload', 'emerge-mono-portfolio' ); ?></label>
                <div class="en-field-desc" style="margin-bottom:10px">
                    <?php esc_html_e( 'Upload your own font file (.woff2 / .woff / .ttf / .otf). It is added immediately and appears in the font list above.', 'emerge-mono-portfolio' ); ?>
                </div>
                <?php
                // 一覧・プレビュー用の @font-face は emono_admin_enqueue() で
                // 'emerge-mono-admin' スタイルに追加済み（名前は emono_font_stack と同じサニタイズ）。
                ?>
                <input type="text" id="en-custom-font-name" class="en-field-input" placeholder="<?php echo esc_attr__( 'Font name (e.g. Baskerville)', 'emerge-mono-portfolio' ); ?>" style="margin-bottom:8px">
                <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px">
                    <input type="text" id="en-custom-font-url" class="en-field-input" placeholder="<?php echo esc_attr__( 'Font file URL', 'emerge-mono-portfolio' ); ?>" style="flex:1" readonly>
                    <button type="button" class="en-media-btn" id="en-custom-font-select" style="white-space:nowrap"><?php esc_html_e( 'Select File', 'emerge-mono-portfolio' ); ?></button>
                    <button type="button" class="en-save-btn" id="en-custom-font-add" style="white-space:nowrap;padding:8px 20px"><?php esc_html_e( 'Add Font', 'emerge-mono-portfolio' ); ?></button>
                </div>
                <div id="en-custom-font-msg" style="font-size:12px;min-height:18px;margin-bottom:10px"></div>

                <div id="en-custom-font-list">
                    <?php if ( $custom_fonts ) : ?>
                        <?php foreach ( $custom_fonts as $cf ) : if ( empty($cf['name']) ) continue; ?>
                        <div class="en-custom-font-row" data-name="<?php echo esc_attr($cf['name']); ?>" style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(255,255,255,.04);padding:10px 14px;border-radius:6px;margin-bottom:6px">
                            <span style="font-family:'<?php echo esc_attr($cf['name']); ?>',sans-serif;font-size:18px"><?php echo esc_html($cf['name']); ?></span>
                            <button type="button" class="en-remove-btn en-custom-font-del" data-name="<?php echo esc_attr($cf['name']); ?>"><?php esc_html_e( 'Delete', 'emerge-mono-portfolio' ); ?></button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            wp_localize_script( 'emerge-mono-admin', 'emonoCustomFontSettings', array(
                'nonce'   => wp_create_nonce( 'en_custom_font_nonce' ),
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'i18n'    => array(
                    'needBoth'    => __( 'Please enter a font name and select a file.', 'emerge-mono-portfolio' ),
                    'adding'      => __( 'Adding...', 'emerge-mono-portfolio' ),
                    'added'       => __( 'Font added.', 'emerge-mono-portfolio' ),
                    'confirmDel'  => __( 'Delete this font?', 'emerge-mono-portfolio' ),
                    'custom'      => __( 'Custom', 'emerge-mono-portfolio' ),
                    'del'         => __( 'Delete', 'emerge-mono-portfolio' ),
                    'mediaTitle'  => __( 'Select Font File', 'emerge-mono-portfolio' ),
                    'mediaButton' => __( 'Use this font', 'emerge-mono-portfolio' ),
                ),
            ) );
            wp_add_inline_script( 'emerge-mono-admin', <<<'EMONO_CUSTOM_FONT_JS'
(function(){
                var settings = window.emonoCustomFontSettings || {};
                var nonce   = settings.nonce || '';
                var ajaxUrl = settings.ajaxUrl || '';
                var i18n = settings.i18n || {};
                var nameInput = document.getElementById('en-custom-font-name');
                var urlInput  = document.getElementById('en-custom-font-url');
                var msg       = document.getElementById('en-custom-font-msg');
                var listEl    = document.getElementById('en-custom-font-list');
                var fontSel   = document.querySelector('select[name="design_font"]');

                // ファイル選択
                document.getElementById('en-custom-font-select').addEventListener('click', function(e){
                    e.preventDefault();
                    var frame = wp.media({
                        title: i18n.mediaTitle,
                        button: { text: i18n.mediaButton },
                        multiple: false,
                        library: { type: ['font/woff','font/woff2','font/ttf','font/otf','application/octet-stream'] }
                    });
                    frame.on('select', function(){
                        var att = frame.state().get('selection').first().toJSON();
                        urlInput.value = att.url;
                        if ( ! nameInput.value && att.title ) nameInput.value = att.title;
                    });
                    frame.open();
                });

                function injectFontFace(name, url){
                    var s = document.createElement('style');
                    s.textContent = "@font-face{font-family:'" + name + "';font-display:swap;src:url('" + url + "');}";
                    document.head.appendChild(s);
                }
                function addToDropdown(name){
                    if ( ! fontSel ) return;
                    for ( var i=0; i<fontSel.options.length; i++ ){
                        if ( fontSel.options[i].value === name ) return;
                    }
                    var opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name + ' (' + i18n.custom + ')';
                    fontSel.appendChild(opt);
                }
                function addRow(name){
                    var row = document.createElement('div');
                    row.className = 'en-custom-font-row';
                    row.setAttribute('data-name', name);
                    row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:12px;background:rgba(255,255,255,.04);padding:10px 14px;border-radius:6px;margin-bottom:6px';
                    row.innerHTML = '<span style="font-family:\'' + name + '\',sans-serif;font-size:18px"></span>'
                                  + '<button type="button" class="en-remove-btn en-custom-font-del" data-name="' + name + '"></button>';
                    row.querySelector('span').textContent = name;
                    row.querySelector('button').textContent = i18n.del;
                    listEl.appendChild(row);
                    bindDelete(row.querySelector('.en-custom-font-del'));
                }

                // 追加
                document.getElementById('en-custom-font-add').addEventListener('click', function(e){
                    e.preventDefault();
                    var name = nameInput.value.trim();
                    var url  = urlInput.value.trim();
                    if ( ! name || ! url ) { msg.style.color='rgba(255,120,120,.9)'; msg.textContent = i18n.needBoth; return; }
                    var btn = this; btn.disabled = true;
                    msg.style.color='rgba(255,255,255,.6)'; msg.textContent = i18n.adding;
                    var data = new FormData();
                    data.append('action','en_add_custom_font');
                    data.append('nonce', nonce);
                    data.append('name', name);
                    data.append('url', url);
                    fetch(ajaxUrl, {method:'POST', body:data}).then(function(r){return r.json();}).then(function(res){
                        btn.disabled = false;
                        if ( res.success ) {
                            injectFontFace(name, url);
                            addToDropdown(name);
                            // 既存行になければ追加
                            if ( ! listEl.querySelector('.en-custom-font-row[data-name="'+CSS.escape(name)+'"]') ) {
                                addRow(name);
                            }
                            msg.style.color='rgba(120,220,120,.9)'; msg.textContent = i18n.added;
                            nameInput.value=''; urlInput.value='';
                        } else {
                            msg.style.color='rgba(255,120,120,.9)'; msg.textContent = res.data || 'Error';
                        }
                    });
                });

                // 削除
                function bindDelete(btn){
                    btn.addEventListener('click', function(e){
                        e.preventDefault();
                        if ( ! confirm(i18n.confirmDel) ) return;
                        var name = this.getAttribute('data-name');
                        var b = this; b.disabled = true;
                        var data = new FormData();
                        data.append('action','en_delete_custom_font');
                        data.append('nonce', nonce);
                        data.append('name', name);
                        fetch(ajaxUrl, {method:'POST', body:data}).then(function(r){return r.json();}).then(function(res){
                            if ( res.success ) {
                                var row = listEl.querySelector('.en-custom-font-row[data-name="'+CSS.escape(name)+'"]');
                                if ( row ) row.remove();
                                // ドロップダウンからも削除
                                if ( fontSel ) {
                                    for ( var i=0; i<fontSel.options.length; i++ ){
                                        if ( fontSel.options[i].value === name ) { fontSel.remove(i); break; }
                                    }
                                }
                            } else {
                                b.disabled = false;
                                alert(res.data || 'Error');
                            }
                        });
                    });
                }
                document.querySelectorAll('.en-custom-font-del').forEach(bindDelete);
            })();
EMONO_CUSTOM_FONT_JS
            );
            ?>
        </div>
        <div class="en-admin-section">
            <div class="en-admin-section-title"><?php esc_html_e( 'Preview', 'emerge-mono-portfolio' ); ?></div>
            <?php
            // The preview font is applied by JS (see emono_admin_get_script), which reads
            // the initial stack from data-font-stack and updates it live on dropdown change.
            $preview_font = emono_font_stack( $font );
            ?>
            <div id="en-design-preview" data-font-stack="<?php echo esc_attr( $preview_font ); ?>" data-font-map="<?php echo esc_attr( wp_json_encode( $font_stack_map ) ); ?>" style="background:<?php echo esc_attr($bg); ?>;color:<?php echo esc_attr($text); ?>;padding:24px;border-radius:8px;border:1px solid rgba(255,255,255,.08)">
                <div style="font-size:20px;font-weight:700;letter-spacing:.1em;margin-bottom:8px">DAISUKE DESIGN</div>
                <div style="font-size:11px;letter-spacing:.4em;opacity:.5;margin-bottom:16px">WEB CREATOR</div>
                <div style="display:inline-block;border:1px solid <?php echo esc_attr($accent); ?>;color:<?php echo esc_attr($accent); ?>;font-size:10px;letter-spacing:.3em;padding:8px 20px;opacity:.7">WORKS</div>
            </div>
        </div>
        <button type="submit" class="en-save-btn"><?php esc_html_e( 'Save', 'emerge-mono-portfolio' ); ?></button>
    </form>
    <?php
}
