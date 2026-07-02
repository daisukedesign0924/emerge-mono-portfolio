=== Emerge Mono - Portfolio ===
Contributors: daisukedesign
Tags: portfolio, shortcode, contact form, dark mode, creator
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.20.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A monochrome portfolio toolkit for creators. Build a full portfolio site with shortcodes, no page builder required.

== Description ==

Emerge Mono - Portfolio is a shortcode-driven toolkit for building a clean, monochrome portfolio site. It is designed so that beginners can assemble a complete portfolio without touching the block editor: place a shortcode, and the section renders.

Every section is a shortcode, so you can combine them freely on any page.

**Key features**

* **Hero section** - Logo, site name, tagline, and customizable navigation buttons that reflow responsively (4 columns on desktop, 2 on tablet and mobile).
* **Works gallery** - A filterable gallery with category tabs. Supports images and video embeds (YouTube / Vimeo).
* **Profile** - Name, title, bio, profile image, skills, and SNS links with per-icon images.
* **News** - A lightweight news / blog section powered by a custom post type.
* **Contact form** - Honeypot and reCAPTCHA v3 spam protection, rate limiting, auto-reply email, and a submission log.
* **Estimate simulator** - A multi-step quote calculator you can configure from the admin screen.
* **Privacy Policy & Terms generators** - Auto-generate bilingual (Japanese / English) Privacy Policy and Terms of Service pages, with a cookie consent banner.
* **Design controls** - Dark / light / auto color mode via CSS variables, Google Fonts, system fonts, and custom font upload.
* **Fully internationalized** - English-first UI, translation-ready, with a bundled Japanese translation.

**Shortcodes**

* `[emerge_mono_top]` - Hero section
* `[emerge_mono_about]` - Profile / about
* `[emerge_mono_works]` - Works gallery
* `[emerge_mono_news]` - News list
* `[emerge_mono_contact]` - Contact form
* `[emerge_mono_estimate]` - Estimate simulator
* `[emerge_mono_privacy]` - Privacy Policy
* `[emerge_mono_terms]` - Terms of Service
* `[emerge_mono_posts]` - Post list

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
4. Admin settings screen.

== Changelog ==

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
