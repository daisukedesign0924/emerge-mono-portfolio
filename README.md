# Emerge Mono

A monochrome, section-based site builder for WordPress, built for creators. Compose your top page and content pages by stacking sections in a live‑preview Design Editor — no external page builder or block editor required.

Pick a section, arrange it, preview it live, and publish.

## Features

- **Design Editor with live preview** — One screen to edit your Top page, About page, Header, Footer, Menu, Branding, Contact form, and Colors & Fonts, each with an instant side‑by‑side preview.
- **Section builder (MONO TOP, built in)** — Build editorial pages by stacking sections: an animated particle hero, about, post slider, news grid, CTA, and contact. Add, remove, reorder, and use multiple instances.
- **Minimal Top** — A curated, minimal top page with logo, site name, tagline, and navigation buttons.
- **About page, your way** — The About page is composed from sections in the Design Editor, so each site can look different. Shown only when you create it.
- **Profile** — A fixed, refined profile section: name, title, bio, image, skills, and social links with per‑icon images.
- **Works gallery** — A filterable gallery with category tabs. Supports images and video embeds (YouTube / Vimeo).
- **News** — A lightweight news / blog section powered by a custom post type.
- **Customizable contact form** — Edit the form fields (label, type, placeholder, required) right in the Design Editor with live preview. Honeypot and reCAPTCHA v3 protection, rate limiting, auto‑reply, and a submission log.
- **Post type visibility** — Show or hide each post type (including those added by extensions) from the dashboard menu and Page Manager, to keep client sites tidy.
- **Extension‑ready** — Extensions can register their own post types and sections. Post types opt into the visibility list with a single filter — no core update required.
- **Estimate simulator, Privacy Policy & Terms generators** — A multi‑step quote calculator and auto‑generated Privacy / Terms pages with a cookie consent banner.
- **Design controls** — Dark / light / auto color mode, system fonts, and custom font upload.
- **Fully internationalized** — English‑first UI, translation‑ready, with a bundled Japanese translation.

## Shortcodes

| Shortcode | Section |
|---|---|
| `[emerge_mono_top]` | Selected top layout (Minimal Top or MONO TOP) |
| `[emerge_mono_profile]` | Profile (fixed design) |
| `[emerge_mono_about]` | About page (built from sections) |
| `[emerge_mono_works]` | Works gallery |
| `[emerge_mono_news]` | News list |
| `[emerge_mono_contact]` | Contact form |
| `[emerge_mono_estimate]` | Estimate simulator |
| `[emerge_mono_privacy]` | Privacy Policy |
| `[emerge_mono_terms]` | Terms of Service |
| `[emerge_mono_posts]` | Post list |

## Installation

1. Download the latest release ZIP.
2. In your WordPress admin, go to **Plugins → Add New → Upload Plugin**, and upload the ZIP.
3. Activate the plugin.
4. Open the **Emerge Mono** menu in the admin sidebar, then use **Design Editor** to build your pages.
5. Create a page, place the shortcode you need (for example `[emerge_mono_top]`), and publish.

## Extensions

Emerge Mono can be extended with add‑ons that register their own post types and sections (for example, a talent/VTuber directory). Extensions appear automatically in the post‑type visibility list by registering with the `emono_managed_post_types` filter — no changes to the core plugin are needed.

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later

## Companion theme

For the intended full‑screen monochrome look, this plugin pairs with the **Emerge Mono Zero** theme. The shortcodes render on any theme, but the companion theme provides the matching layout and styling.

## License

This plugin is free software, released under the **GNU General Public License v2.0 or later (GPLv2+)**.

It is provided "as is", without warranty of any kind. Use at your own risk. See the [LICENSE](LICENSE) file for the full license text.

Copyright (C) 2026 DAISUKE DESIGN
