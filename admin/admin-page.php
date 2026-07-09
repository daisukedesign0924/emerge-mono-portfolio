<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function emono_admin_page() {
    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'contact'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
    // フロント（見た目）の編集は「Design Editor」へ集約したため、ここには
    // バックエンド寄りの機能設定のみを残す。
    $tabs = array(
        'contact'  => array( 'label' => __( 'Contact Form', 'emerge-mono-portfolio' ),        'icon' => '✉' ),
        'cpt'      => array( 'label' => __( 'Post Type (Works)', 'emerge-mono-portfolio' ),     'icon' => '📂' ),
        'privacy'  => array( 'label' => __( 'Privacy Policy', 'emerge-mono-portfolio' ),               'icon' => '🔒' ),
        'terms'    => array( 'label' => __( 'Terms of Service', 'emerge-mono-portfolio' ),                           'icon' => '📋' ),
        'estimate' => array( 'label' => __( 'Estimate Simulator', 'emerge-mono-portfolio' ),               'icon' => '💰' ),
        'editor'   => array( 'label' => __( 'Editor', 'emerge-mono-portfolio' ),                            'icon' => '✏️' ),
        'shortcodes' => array( 'label' => __( 'Shortcodes', 'emerge-mono-portfolio' ),        'icon' => '📋' ),
    );
    $opts = emono_get_options();
    ?>
    <div class="en-admin-wrap">
        <div class="en-admin-header">
            <div class="en-admin-logo">
                <span class="en-admin-logo-mark"><img src="<?php echo esc_url( EMONO_URL . 'assets/img/plugin-icon.webp' ); ?>" alt="Emerge Mono" width="36" height="36"></span>
                <div>
                    <div class="en-admin-title">Emerge Mono</div>
                    <div class="en-admin-version">v<?php echo esc_html( EMONO_VERSION ); ?></div>
                </div>
            </div>
            <?php
            $en_theme_url = defined( 'EMONO_THEME_URL' ) ? EMONO_THEME_URL : '#';
            $en_theme_disabled = ( $en_theme_url === '' || $en_theme_url === '#' );
            ?>
            <a class="en-admin-theme-btn<?php echo $en_theme_disabled ? ' is-disabled' : ''; ?>"
               href="<?php echo esc_url( $en_theme_url ); ?>"
               <?php if ( ! $en_theme_disabled ) : ?>target="_blank" rel="noopener noreferrer"<?php else : ?>onclick="return false;" aria-disabled="true"<?php endif; ?>>
                <span class="en-admin-theme-btn-label"><?php esc_html_e( 'Theme', 'emerge-mono-portfolio' ); ?></span>
                <span class="en-admin-theme-btn-sep">-</span>
                <span class="en-admin-theme-btn-name">Emerge Mono Zero</span>
            </a>
        </div>
        <div class="en-admin-body">
            <nav class="en-admin-sidebar">
                <?php foreach ( $tabs as $key => $info ) : ?>
                    <a href="?page=emerge-mono-portfolio&tab=<?php echo esc_attr( $key ); ?>"
                       class="en-admin-nav-item <?php echo $tab === $key ? 'active' : ''; ?>">
                        <span class="en-admin-nav-icon"><?php echo esc_html( $info['icon'] ); ?></span>
                        <?php echo esc_html($info['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="en-admin-content">
                <?php
                switch ( $tab ) {
                    case 'shortcodes': emono_admin_tab_shortcodes(); break;
                    case 'general':  emono_admin_tab_general($opts);  break;
                    case 'profile':  emono_admin_tab_profile($opts);  break;
                    case 'nav':      emono_admin_tab_nav($opts);      break;
                    case 'contact':  emono_admin_tab_contact($opts);  break;
                    case 'design':   emono_admin_tab_design($opts);  break;
                    case 'cpt':      emono_admin_tab_cpt($opts);      break;
                    case 'privacy':  emono_admin_tab_privacy($opts);  break;
                    case 'terms':    emono_admin_tab_terms($opts);    break;
                    case 'estimate': emono_admin_tab_estimate($opts); break;
                    case 'editor':   emono_admin_tab_editor($opts);   break;
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

function emono_admin_notice() {
    if ( isset($_GET['saved']) && $_GET['saved'] === '1' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display parameter; no state change.
        echo '<div class="en-admin-notice success">✓ ' . esc_html__( 'Saved.', 'emerge-mono-portfolio' ) . '</div>';
    }
}

