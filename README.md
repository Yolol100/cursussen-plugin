# Cursussen — WordPress Course Management Plugin

> **Supporting portfolio project · WordPress/PHP · custom post types · REST API · shortcodes · responsive UI**

**Developer profile:** [Andrew Baeten](https://github.com/Yolol100) · [GitHub project index](https://github.com/Yolol100?tab=repositories)

Cursussen is a focused WordPress plugin for managing structured course information and publishing it through a responsive shortcode or a paginated public REST API. It keeps the content model, frontend output and lifecycle behaviour inside a small dependency-light plugin.

## What it demonstrates

| Area | Implementation |
| --- | --- |
| Content model | Course custom post type, taxonomy and structured course metadata |
| Front end | Responsive course overview with accessible accordion behaviour |
| Shortcode API | Filterable `[toon_cursussen]` output with bounded result counts |
| REST API | Paginated public endpoints restricted to published course data |
| Performance | Maximum result limits and dependency-light frontend JavaScript |
| Data lifecycle | Conservative uninstall behaviour with explicit opt-in deletion |

## Requirements

- WordPress 6.3+
- PHP 7.4+
- Current release: **1.4.2**

## Installation

1. Upload the plugin folder to `wp-content/plugins/` or install the ZIP through WordPress.
2. Activate **Cursussen**.
3. Add course entries from the **Cursussen** admin menu.
4. Place `[toon_cursussen]` on the page where the course overview should appear.

## Shortcode

Basic usage:

```text
[toon_cursussen]
```

Supported attributes include `categorie`, `aantal`, `inschrijven_url` and `layout`.

Example:

```text
[toon_cursussen categorie="bhv" aantal="20" inschrijven_url="/inschrijven/"]
```

The configured result count is bounded to protect frontend queries.

## REST API

The plugin exposes published course data through:

```text
/wp-json/cursussen/v1/all?page=1&per_page=20
/wp-json/cursussen/v1/filter?categorie=bhv&page=1&per_page=20
```

These endpoints should be used only when the published course information is intended to be public.

## Data lifecycle

Course content is preserved on uninstall by default. Full plugin-data removal is only enabled when the dedicated uninstall option is explicitly turned on.

## Repository structure

```text
cursussen-plugin.php    Plugin bootstrap and metadata
includes/               Content model, settings, shortcode and REST logic
assets/                 Frontend/admin assets
languages/              Translation files
readme.txt              WordPress release and usage documentation
uninstall.php           Controlled cleanup
```

## Portfolio context

This is a smaller WordPress project focused on a clear content model, bounded public output and maintainable native WordPress functionality. For larger examples, see [ACF Page Text Manager](https://github.com/Yolol100/ACF-Text-Manager), [Content Sync Manager](https://github.com/Yolol100/Content-Sync-Manager) and [SooCool for WooCommerce](https://github.com/Yolol100/soocool-for-woocommerce).

## License

GPL-2.0-or-later.
