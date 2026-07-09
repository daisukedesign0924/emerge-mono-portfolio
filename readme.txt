=== Emerge Mono - Portfolio ===
Contributors: daisukedesign
Tags: portfolio, shortcode, contact form, dark mode, creator
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 3.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A monochrome portfolio toolkit for creators. Build a full portfolio site with shortcodes, no page builder required.

== Description ==

Emerge Mono - Portfolio is a shortcode-driven toolkit for building a clean, monochrome portfolio site. It is designed so that beginners can assemble a complete portfolio without touching the block editor: place a shortcode, and the section renders.

Every section is a shortcode, so you can combine them freely on any page.

**Key features**

* **Minimal Top** - A curated, minimal monochrome top page with logo, site name, tagline, and customizable navigation buttons.
* **Design Editor** - A dedicated screen to edit the front-end (top page, design, branding, menu, profile) with a live preview.
* **Extensible top layouts** - Additional top layouts (such as the composable "MONO TOP" builder) are provided by free extensions via the `emono_top_layouts` filter.
* **Works gallery** - A filterable gallery with category tabs. Supports images and video embeds (YouTube / Vimeo).
* **Profile** - Name, title, bio, profile image, skills, and SNS links with per-icon images.
* **News** - A lightweight news / blog section powered by a custom post type.
* **Contact form** - Honeypot and reCAPTCHA v3 spam protection, rate limiting, auto-reply email, and a submission log.
* **Estimate simulator** - A multi-step quote calculator you can configure from the admin screen.
* **Privacy Policy & Terms generators** - Auto-generate bilingual (Japanese / English) Privacy Policy and Terms of Service pages, with a cookie consent banner.
* **Design controls** - Dark / light / auto color mode via CSS variables, Google Fonts, system fonts, and custom font upload.
* **Fully internationalized** - English-first UI, translation-ready, with a bundled Japanese translation.

**Shortcodes**

* `[emerge_mono_top]` - Selected top layout
* `[emerge_mono_minimal_top]` - Minimal Top
* `[emerge_mono_about]` - Profile / about
* `[emerge_mono_works]` - Works gallery
* `[emerge_mono_news]` - News list
* `[emerge_mono_contact]` - Contact form
* `[emerge_mono_estimate]` - Estimate simulator
* `[emerge_mono_privacy]` - Privacy Policy
* `[emerge_mono_terms]` - Terms of Service
* `[emerge_mono_posts]` - Post list

== External services ==

This plugin does not send any data to external services by default. The following third-party services are used only when you (the site owner) explicitly enable or use the related feature. No external font service (such as Google Fonts) is used; all fonts are system fonts or files you upload yourself.

**Google reCAPTCHA v3 (optional)**

Used only if you enter your own reCAPTCHA keys in the contact form settings. When enabled, it protects your contact form from spam and bots. On pages that display the contact form, the visitor's browser loads the reCAPTCHA script from Google and Google analyzes the interaction to produce a score; when the form is submitted, the generated token is sent from your server to Google for verification. This transmits data such as the visitor's IP address and interaction signals to Google. If you do not enter reCAPTCHA keys, this service is not used and nothing is loaded or sent.
Google reCAPTCHA Terms of Service: https://policies.google.com/terms
Google Privacy Policy: https://policies.google.com/privacy

**YouTube (optional)**

Used only if you add a YouTube video URL to a Works gallery item. When a visitor opens that Works detail page, the YouTube video is embedded via an iframe, and the visitor's browser connects to YouTube (Google) to load the player. This transmits data such as the visitor's IP address to YouTube. If you do not add any YouTube URLs, no connection to YouTube is made.
YouTube Terms of Service: https://www.youtube.com/t/terms
Google Privacy Policy: https://policies.google.com/privacy

**Vimeo (optional)**

Used only if you add a Vimeo video URL to a Works gallery item. When a visitor opens that Works detail page, the Vimeo video is embedded via an iframe, and the visitor's browser connects to Vimeo to load the player. This transmits data such as the visitor's IP address to Vimeo. If you do not add any Vimeo URLs, no connection to Vimeo is made.
Vimeo Terms of Service: https://vimeo.com/terms
Vimeo Privacy Policy: https://vimeo.com/privacy

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/emerge-mono-portfolio` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Open the "Emerge Mono - Portfolio" menu in the admin sidebar to configure your profile, design, and other settings.
4. Create a page and place the shortcodes you need (for example, `[emerge_mono_top]`), then publish.

== Frequently Asked Questions ==

= Do I need a page builder or the block editor? =

No. Every section is a shortcode. You can place them on any page or post without a page builder.

= Does it work with any theme? =

Emerge Mono - Portfolio is designed to work standalone. For the intended full-screen monochrome look, it pairs best with the companion "Emerge Mono Zero" theme, but the shortcodes render on any theme.

= Is it translation-ready? =

Yes. The UI is English-first and fully internationalized. A Japanese translation is bundled, and you can add your own translations via the `/languages` folder.

= Does the contact form include spam protection? =

Yes. It includes a honeypot field and rate limiting by default, with optional reCAPTCHA v3 support.

== Screenshots ==

1. Hero section with logo, site name, and navigation buttons.
2. Filterable works gallery.
3. Profile section with skills and SNS links.
4. Works detail page with project description and image gallery.
5. Admin settings screen.

== Changelog ==

= 3.0.0 =
* The base plugin now ships the curated **Minimal Top** as its single built-in top layout, for a focused, finished monochrome product.
* **MONO TOP** (the composable editorial section builder) has moved to a separate free extension, "Emerge Mono - MONO TOP". Install it to keep using MONO TOP; your existing MONO TOP content is preserved.
* Top layouts are now fully extension-driven via the `emono_top_layouts` filter. If a selected layout's extension is not active, the top page falls back to Minimal Top.

= 2.29.1 =
* Code quality: resolved the remaining Plugin Check warnings (nonce-verification annotations on the MONO TOP save filter; documented the standard DONOTCACHEPAGE constant). No functional change.

= 2.29.0 =
* Feature: added a Contact form section to the MONO TOP builder. Embed the contact form directly on the top page (with an optional kicker, heading, and intro text) — useful for one-page sites.

= 2.28.3 =
* UI: right-aligned the copyright text in the MONO TOP footer.

= 2.28.2 =
* Fix: removing all MONO TOP sections and saving now stays empty (the default sections no longer come back). An empty section list is kept as-is; migration only runs when no section list has ever been saved.

= 2.28.1 =
* Fix: selecting an image in the MONO TOP builder now updates the live preview immediately, without saving.
* Added a Clear button to each image field so you can remove a set image directly.

= 2.28.0 =
* Feature: MONO TOP is now a section builder. Add, remove, and drag to reorder sections, and place the same section type more than once.
* Sections are provided by a registry (Hero, About, Slider, News, CTA) that extension plugins can extend via the `emono_mono_top_sections` filter.
* Existing MONO TOP settings are migrated automatically to the new section list. The live preview and non-destructive save behavior are unchanged.

= 2.27.0 =
* Feature: consolidated all front-end (visual) editing into a single "Design Editor" screen (renamed and merged from the former TOP Editor and Live Editor).
* Design Editor has a scope sub-nav: Top Page, Design, Branding, Menu, and Profile — each with a live preview.
* The main plugin settings screen now focuses on back-end features (Contact Form, Post Type, Privacy, Terms, Estimate, Editor, Shortcodes).
* Saves made inside the Design Editor return to it. Preview remains non-destructive (changes are only stored when you press Save).

= 2.26.0 =
* Feature: added a new Live Editor screen that shows a live front-end preview while you edit. The first supported scope is Design settings (color mode, colors, and font).
* The preview reuses the same non-destructive mechanism as the TOP Editor: changes are only stored when you press Save.

= 2.25.13 =
* Fix: the WordPress admin toolbar no longer reappears when navigating to other pages inside the TOP Editor preview (preview mode is now carried across internal links).

= 2.25.12 =
* Feature: added an image ratio setting for the MONO TOP News section (Landscape 16:9, Portrait 3:4, Square 1:1, Standard 4:3).
* i18n: clarified the Japanese labels in the TOP Editor (Kicker and section Title were renamed to clearer terms).

= 2.25.11 =
* UI: widened the TOP Editor preview panel to use the available space for a larger preview.

= 2.25.10 =
* Fix: the TOP Editor live preview no longer accidentally saved settings or failed with a "Preview failed" error (the preview request no longer submits the save trigger).
* The preview now renders at desktop width (scaled to fit the panel) instead of a narrow mobile view.
* The WordPress admin toolbar is hidden inside the preview frame for a clean front-end view.

= 2.25.9 =
* UI: the TOP Editor preview now updates live as you edit, without saving.
* Unsaved top layout changes are rendered through the real front-end template via a per-user temporary preview (visible only to the logged-in administrator).
* No change to the save behavior; edits are still only stored when you press Save.

= 2.25.8 =
* UI: added a dedicated TOP Editor screen using the Emerge Mono editor-style interface.
* UI: moved detailed top layout settings out of the main Site Settings screen.
* UI: added a saved front-page preview panel to the TOP Editor.

= 2.25.7 =
* UI: show the site header on the front page when MONO TOP is selected.
* UI: align the footer styling with the MONO TOP full-page layout.

= 2.25.6 =
* Feature: added an image ratio setting for the MONO TOP selectable slider section.
* UI: slider cards can now be shown as Landscape 16:9, Portrait 3:4, Square 1:1, or Standard 4:3.

= 2.25.5 =
* Fix: changed MONO TOP VIEW ALL links to use fixed pages containing list shortcodes instead of post type archive URLs.
* UI: fixed the MONO TOP News section to the standard News post type and News list page.
* Feature: added a VIEW ALL page setting for the selectable MONO TOP slider section.
* Developer: added `emono_mono_top_list_shortcodes` and `emono_page_defs` filters so extension plugins can register their list shortcode pages.

= 2.25.4 =
* Fix: kept MONO TOP settings when switching between top page layouts.
* Fix: MONO TOP settings now appear immediately when the layout is selected.
* Fix: restored MONO TOP default sections to enabled and recovered empty all-disabled saved states.
* UI: changed MONO TOP default abstract backgrounds to monochrome dark/light friendly styling.

= 2.25.3 =
* Feature: added `MONO TOP` as a built-in selectable top page layout.
* Feature: added the `[emerge_mono_mono_top]` shortcode for directly rendering MONO TOP.
* Feature: added MONO TOP settings for hero, about, slider, news, and CTA sections.

= 2.25.2 =
* Feature: added the `[emerge_mono_minimal_top]` shortcode for directly rendering the default Minimal Top layout.
* Developer: top layouts can now declare their own direct shortcode through the `emono_top_layouts` filter.
* Developer: added hooks for layout-specific settings UI and saving: `emono_top_layout_settings` and `emono_save_top_layout_settings`.

= 2.25.1 =
* UI: renamed the default top layout to `Minimal Top` to make it clear that it is the standard minimal portfolio top.

= 2.25.0 =
* Architecture: introduced selectable top page layouts and registered the current minimal top page.
* Developer: added the `emono_top_layouts` filter so extension plugins can provide additional top page layouts.
* UI: added a Top Page Layout selector to the general settings screen.

= 2.24.18 =
* Bug fix: redirect newly published Works and News posts from the custom editor to their matching list screens to prevent duplicate submissions.

= 2.24.17 =
* Documentation: added a Works detail page screenshot to the WordPress.org listing.

= 2.24.16 =
* Refactor: moved remaining admin inline script/style blocks for settings, setup wizard, and editor screens onto WordPress enqueue handles.
* Refactor: added dedicated admin assets for the setup wizard and editor while preserving the existing ENE localization order.

= 2.24.15 =
* Security: added an explicit nonce verification guard inside Portfolio-specific admin save handling to satisfy Plugin Check after the save handler split.

= 2.24.14 =
* Refactor: split admin save handling into a thin loader, core save actions, and Portfolio-specific save actions.

= 2.24.13 =
* Refactor: split the remaining admin page tabs into dedicated files for Site Settings, Menu Settings, Contact Form, Design, Shortcodes, and Editor.

= 2.24.12 =
* UI: prevent long profile names from overflowing on mobile by allowing safe wrapping and reducing the mobile size cap.

= 2.24.11 =
* UI: slightly reduced the profile name size for a calmer visual balance.

= 2.24.10 =
* UI: changed the profile image ring to a thin #ffffff border and removed the heavy outer shadow.

= 2.24.9 =
* UI: refreshed the profile layout with a large portrait style, rounded skill tags, text-style SNS links, and container-query sizing using clamp() + cqw.
* Improvement: SNS links now show their label even when an optional icon image is set.

= 2.24.8 =
* UI: removed the fixed "About" label from the profile shortcode output.

= 2.24.7 =
* CSS: visually centered the text inside the top page navigation buttons, including the mobile letter-spacing variant.

= 2.24.6 =
* Bug fix: render required/optional form markers as safe HTML in the regular contact form and the estimate contact step instead of displaying the span markup as text.

= 2.24.5 =
* Refactor: moved Portfolio-specific admin tabs for Profile and Works post type settings into a dedicated admin page module.

= 2.24.4 =
* Refactor: split custom post type registration into shared News post types and Portfolio-specific Works post types.

= 2.24.3 =
* Refactor: split shortcode registration into Portfolio-specific shortcodes and shared core shortcodes while keeping the public shortcode names unchanged.

= 2.24.2 =
* Maintenance: cleaned up duplicated admin save handlers for menu, contact form, and Works settings.
* Maintenance: removed an inactive legacy custom fields save branch that no longer has an admin UI.

= 2.24.1 =
* Refactor: split Privacy Policy, Terms of Service, and Estimate Simulator admin screens, save handlers, and shortcodes into feature-specific files.

= 2.23.0 =
* Plugin Check hardening (part 2): added wp_unslash()/sanitization to all form input, switched to wp_safe_redirect(), and documented intentional direct DB / read-only query usage. Remaining notices are template-scope variable warnings only.

= 2.22.0 =
* Renamed all global function names and constants to use the unique "emono_" / "EMONO_" prefix, resolving Plugin Check naming warnings and reducing the risk of conflicts with other plugins. Database option names, custom post types, and CSS classes are unchanged, so existing data and styling are preserved.

= 2.21.2 =
* Plugin Check: resolved the remaining 8 errors (translators comments placed inside PHP tags, CSS output escaping).

= 2.21.1 =
* Plugin Check pass (part 1): fixed all output-escaping errors, added translators comments for placeholders, switched date() to timezone-safe functions, and enqueued Google Fonts / reCAPTCHA via the proper WordPress APIs.

= 2.21.0 =
* Security: added nonce verification (CSRF protection) to the contact log read/delete actions.
* Security: hardened database queries and output escaping in the admin contact log.
* i18n: unified the text domain with the plugin slug (emerge-mono-portfolio) and renamed the translation files accordingly.

= 2.20.2 =
* Corrected the Plugin URI to point to the actual GitHub repository.

= 2.20.1 =
* Renamed the plugin folder and main file to "emerge-mono-portfolio" and unified the admin menu slug to match the plugin name.

= 2.20.0 =
* Prepared for public release: added readme, license (GPLv2 or later), and complete plugin header.
* Top navigation buttons now use a responsive grid (4 columns on desktop, 2 on tablet and mobile) and no longer overflow on small screens.
* Works category filter now shows a "/" separator between categories for better readability on mobile.
* Completed internationalization pass: remaining admin strings are now translatable, with Japanese translations added.

== Upgrade Notice ==

= 2.20.0 =
First public release build with responsive hero navigation and a completed internationalization pass.
