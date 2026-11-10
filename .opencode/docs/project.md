# Project mechanics — Simple Revision Control

> Loading flow, assets, translations, options framework, dependencies, general conventions.
> Read only when the task involves these areas.

## Overview

The "Simple Revision Control" WordPress plugin (iWorks): controls and limits
post revisions per post type. Source files contain **literal placeholders**
(`PLUGIN_VERSION`, `BUILDTIME`, `PLUGIN_TILL_YEAR`, …) — they are replaced by
the bun bundler at build time (see `build.md`), never in the source.

The version lives only in `package.json` (`"version"`); the main file header
and the version constant both use the `PLUGIN_VERSION` placeholder.

## Directory structure

```
/
├── simple-revision-control.php      # Plugin bootstrap (header, constants, includes)
├── etc/
│   └── options.php                  # Options page configuration (iworks_options format)
├── includes/
│   └── iworks/
│       ├── simple-revision-control/ # Plugin classes (base, main, github updater)
│       ├── options/                 # Vendored iworks options framework (git submodule)
│       └── rate/                    # Vendored iworks rate framework (git submodule)
├── assets/
│   ├── scripts/src/admin/           # Admin JS sources (concat order matters)
│   ├── scripts/admin.js / .min.js   # Compiled bundles (built)
│   └── images/                      # Logo SVG
├── languages/                       # simple-revision-control.pot (+ .po/.mo)
├── bundler/                         # Bun build system — see build.md
├── package.json                     # Version + release metadata (single source of truth)
└── readme.txt                       # WordPress.org readme (uses PLUGIN_* placeholders)
```

## Loading flow

1. WordPress loads `simple-revision-control.php` (textdomain
   `simple-revision-control`):
   - defines `SIMPLE_REVISION_CONTROL_VERSION` (`'PLUGIN_VERSION'`) and
     `SIMPLE_REVISION_CONTROL_PREFIX` (`'simple_revision_control_'`);
   - loads `etc/options.php`, the vendored `iworks_options` class and the
     main class `iWorks_Simple_Revision_Control`
     (`includes/iworks/simple-revision-control/class-simple-revision-control.php`);
   - `get_simple_revision_control_options()` instantiates `iworks_options`
     with option function name `simple_revision_control_options` and prefix
     `simple_revision_control_`.
2. The main class (extends the abstract `iWorks_Simple_Revision_Control_Base`):
   - on `init` (PHP_INT_MAX): `change_post_type_revision_support()` — applies
     per-post-type revision settings;
   - filters `wp_revisions_to_keep` (PHP_INT_MAX) — enforces the per-post-type
     limit (-1 unlimited, 0 none, N custom);
   - bulk action + AJAX deletion of revisions
     (`wp_ajax_simple_revision_control_delete_revisions`,
     `handle_bulk_actions-edit-post`), post row actions;
   - registers with the iWorks "rate" framework;
   - optionally loads the GitHub self-updater
     (`class-simple-revision-control-github.php`) when present.
3. Utilization reporting via the `simple_revision_control_utilization` filter
   and `iworks_plugin_get_options` integration.

## Constants

- `SIMPLE_REVISION_CONTROL_VERSION` — plugin version (`'PLUGIN_VERSION'` placeholder).
- `SIMPLE_REVISION_CONTROL_PREFIX` — `simple_revision_control_` prefix for options.
- Global: `$simple_revision_control_options` (options object).

## Assets layer

- **JS**: `assets/scripts/src/admin/*.js` (fixed concat order:
  `delete.js`, `options.js` — only existing files are concatenated) →
  `assets/scripts/admin.js` → `admin.min.js`. Enqueued on the plugin's admin
  options page only (`simple-revision-control-admin` handle); debug mode uses
  the unminified file with a `filemtime()` version, production the `.min.js`
  with the plugin version.
- **CSS**: currently none — the bundler compiles `assets/sass/**` when
  sources appear.
- Compiled files are build outputs — never edit them, edit the sources and rebuild.

## Translations (i18n)

- Textdomain: `simple-revision-control`; `languages/simple-revision-control.pot`;
  locales `pl_PL`, `sv_SE`.
- `bun run make-pot` runs wp-cli `i18n make-pot` and `i18n update-po` for every
  existing `.po` file. `.mo` files are compiled only in `bun run build:github`.

## PHP dependencies

- No Composer. Requires (from `package.json`, replaced into headers at build):
  WordPress >= 6.0, PHP >= 8.0.
- Vendored frameworks live in `includes/iworks/` as git submodules:
  `options/` (settings pages) and `rate/` (rate-it notices). The plugin's own
  classes live in `includes/iworks/simple-revision-control/` (also a submodule).
  All are separate git repos — treat them as vendor code: do not refactor their
  internals; update with `bun run update-libs`.

## General conventions

- Formatting: tabs for PHP/JS/SCSS, LF, final newline.
- Every PHP file: `if ( ! defined( 'WPINC' ) ) { die; }` or
  `defined( 'ABSPATH' ) || exit` (existing style).
- File naming: kebab-case, `class-simple-revision-control-{name}.php`;
  class names: `iWorks_Simple_Revision_Control_*` (existing style),
  methods snake_case.
- Hooks: `simple_revision_control_utilization`, `iworks_plugin_get_options`,
  `iworks_rate_notice_logo_style`; AJAX action
  `simple_revision_control_delete_revisions` (nonce'd).
- Never read the vendored frameworks' internals from feature classes.

## Known pitfalls (do not fix without approval)

- Source placeholders (`PLUGIN_VERSION`, `BUILDTIME`, `PLUGIN_TILL_YEAR`, …)
  are intentional — do not replace them with real values in the repo.
- `etc/options.php` and the stub `index.php` files lack ABSPATH/WPINC guards —
  a known Plugin Check error; fix only with approval (adding guards changes
  vendor-style files).
- `release/github/*` and `release/wporg/*` are build snapshots (gitignored) —
  never edit them directly.
