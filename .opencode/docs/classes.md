# Classes — Simple Revision Control

> Reference for work on PHP classes: base class, main class, revision control,
> options, AJAX, and the vendored iworks frameworks.
> Read only when the task involves these areas.

## Class hierarchy

```
iWorks_Simple_Revision_Control_Base           (abstract, class-…-base.php)
├── iWorks_Simple_Revision_Control            (main class, class-…-control.php)
└── iworks_simple_revision_control_github     (self-updater, class-…-github.php)
```

## Base class (class-simple-revision-control-base.php)

`iWorks_Simple_Revision_Control_Base` — abstract, singleton-ish
(protected constructor): sets `$debug` (true under `WP_DEBUG`), stores shared
state for the main and github classes.

## Main class (class-simple-revision-control.php)

`iWorks_Simple_Revision_Control extends iWorks_Simple_Revision_Control_Base` —
required once from `simple-revision-control.php` (guarded by
`class_exists( 'iWorks_Simple_Revision_Control' )`).

- `__construct()` — resolves `$file` (plugin main file path via nested
  `dirname()`), sets `$this->plugin_file`, registers hooks:
  - `admin_notices` → `action_maybe_show_notice_after_delete_revisions()`
    (feedback after bulk/AJAX revision deletion);
  - `init` (PHP_INT_MAX) → `change_post_type_revision_support()` — applies the
    per-post-type revision configuration from options;
  - `wp_ajax_simple_revision_control_delete_revisions` →
    `action_ajax_delete_revisions()` (nonce'd per-post revision deletion);
  - `handle_bulk_actions-edit-post` → `filter_delete_revisions()` — bulk
    "delete revisions" action on the post list table;
  - `post_row_actions` (PHP_INT_MAX) → `filter_maybe_add_post_row_actions()` —
    per-post "delete revisions" link when revisions exist;
  - `wp_revisions_to_keep` (PHP_INT_MAX) → `wp_revisions_to_keep()` — enforces
    the per-post-type limit (-1 unlimited, 0 none, N custom);
  - `iworks_plugin_get_options` → `filter_add_post_types_options()` — injects
    per-post-type revision settings into the iworks options page
    (selects: unlimited / none / custom number);
  - `simple_revision_control_utilization` → `filter_get_utilization()` —
    DB utilization report (revision counts per post type) shown on the
    options page;
  - `iworks_rate_notice_logo_style` → `filter_plugin_logo()` — plugin logo in
    the rate-me notice.
- Assets: enqueues `simple-revision-control-admin` on its admin page —
  `assets/scripts/admin.js` (`.min.js` in production; unminified with
  `filemtime()` version under `WP_DEBUG`).
- Settings link on the Plugins screen (`plugin_action_links_…`).

## GitHub self-updater (class-simple-revision-control-github.php)

`iworks_simple_revision_control_github` — updates the plugin from the GitHub
repository (`plugin_popup` / `modify_transient` style hooks, textdomain load
on `init`). Loaded conditionally by the main class when the file exists.
**Excluded from the WordPress.org release ZIP** (wporg releases must not
self-update from GitHub).

## Options layer (etc/options.php + iworks_options)

- One options page (`index`) under Settings → "Revisions"
  (`page_title` "Revisions configuration", `menu: 'options'`),
  enqueues `simple-revision-control-admin` scripts.
- Per-post-type revision settings are injected at runtime by
  `filter_add_post_types_options()` (not stored statically in options.php):
  unlimited (-1) / none (0) / custom N per post type with `revisions` support.
- Misc options (rate notice copy, message titles) for the iworks rate framework.

## Vendored frameworks (includes/iworks/)

- `options/` — `iworks_options`: settings pages, option storage and upgrade
  routines. Configured from `etc/options.php`; option names get the
  `simple_revision_control_` prefix. Separate git repo (submodule) —
  treat as vendor, do not refactor.
- `rate/` — rate-it / "enjoying this plugin?" admin notices and templates,
  wired via the `IWORKS_RATE_TEXTDOMAIN` placeholder. Separate git repo
  (submodule) — vendor.
- `simple-revision-control/` — the plugin's own classes; also a submodule —
  update with `bun run update-libs`.

## Known pitfalls (do not fix without approval)

- `assets/scripts/src/admin/options.js` is in the concat order but missing
  from the repo (grunt-era leftover) — the bundler concatenates only
  existing files.
- `etc/options.php` has no ABSPATH guard (Plugin Check error) — vendor-style
  file, fix only with approval.
- The GitHub updater is loaded whenever its file exists, so the wporg
  copy rules must keep excluding it (see `build.md`).
