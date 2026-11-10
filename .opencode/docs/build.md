# Build — Bun bundler

> Toolchain, modes, pipeline, placeholders, release ZIPs, translations, tests.
> Read only when working on the build.

## Toolchain

- Bun (>= 1.3) — package manager, script runner and the bundler runtime
  (`bundler/index.js` modes). `bun.lock` is committed; `package-lock.json` is
  deleted (grunt era is over). Node is not required.
- PHP >= 8.0; `wp-cli.phar` (downloaded on demand by `make-pot`, kept untracked)
  for translations and Plugin Check; PHPUnit for tests (`tests/php/`, may not exist).
- Prettier, sass, terser and clean-css run **in-process** (`bundler/tools.mjs`) —
  no CLI spawns per file. Only wp-cli and PHPUnit are child processes.
- All dist writes are atomic (temp + rename); tool failures print as friendly
  one-liners (`tool — file` + message, no stack trace) and fail the build.
- Grunt was fully replaced: `Gruntfile.js` and all `grunt-*` devDependencies are
  gone; jshint and `checktextdomain` were dropped (gettext usage is covered by
  `make-pot` output review).

## Files (`bundler/`)

| File              | Role                                                                                     |
| ----------------- | ---------------------------------------------------------------------------------------- |
| `index.js`        | Modes: `dev` (default) / `compile` / `build` / `build:wporg` / `build:github` / `make-pot` / `test` / `update-libs` |
| `config.mjs`      | Paths, names (from `package.json`), placeholder patterns, release copy rules per flavor   |
| `pipelines.mjs`   | CSS (sass → minify) and JS (concat → terser) pipelines, prettier pass, clean              |
| `tools.mjs`       | In-process APIs (prettier, sass, terser, clean-css), atomic writes, glob helpers          |
| `makeZip.mjs`     | Staging copy → placeholder replacement → ZIP (archiver, level 9)                          |
| `makePot.mjs`     | wp-cli i18n: `make-pot` → `update-po` (→ `make-mo` for github)                            |
| `pluginCheck.mjs` | wporg only: Plugin Check (PCP) on the staged release (see below)                          |
| `spawnAsync.mjs`  | `child_process.spawn` wrapper with captured output                                        |
| `consoleHelpers.mjs`, `progressBar.mjs` | Logging, timing, build progress bar                                 |

## Output names (grunt parity — the PHP code enqueues them, never change)

- JS: `assets/scripts/admin.js` + `.min.js` — ordered concat from
  `assets/scripts/src/admin/`: `delete.js`, `options.js` (only files that
  actually exist are concatenated); terser mangle with `jQuery` reserved.
- CSS: currently no SCSS sources exist (`assets/sass/` is absent); the pipeline
  compiles every non-`_` entry under `assets/sass/` → `assets/styles/{path}.css`
  + `.min.css` when any appear.
- Banners (grunt parity): concat = `/*! {title} - {version}\n * {homepage}\n *
  Copyright (c) {year}\n * Licensed {license} */`; minified JS/CSS use the same
  with `Copyright (c) {year};` (note the `;`).

## Modes (`bun run ...`)

| Command                | Description                                                                                  |
| ---------------------- | -------------------------------------------------------------------------------------------- |
| `bun run dev`          | Watcher: `assets/sass/**` → CSS pipeline, `assets/scripts/src/admin/**` → prettier + JS pipeline (200 ms debounce); no ZIP, no i18n |
| `bun run compile`      | One-shot asset compile with source maps (old `grunt` default task)                            |
| `bun run build`        | Both flavors back-to-back: github + wporg                                                     |
| `bun run build:wporg`  | Production → `release/wporg/{name}.zip` + Plugin Check (PCP)                                  |
| `bun run build:github` | Production + `.mo` → `release/github/{name}.zip`                                              |
| `bun run make-pot`     | Translations only (`.pot` + `.po` update)                                                     |
| `bun run update-libs`  | `git pull` on the vendored iWorks libraries (`includes/iworks/options`, `includes/iworks/rate`, `includes/iworks/simple-revision-control`) |
| `bun run test`         | PHPUnit (`tests/php/phpunit.xml`)                                                             |
| (env) `DEV_EXIT_AFTER_MS` | Dev mode exits itself after N ms — headless testing                                        |

## Build pipeline (`build:*`, 7–8 steps)

1. **clean** — remove built JS (`assets/scripts/*.js`, `*.map`), built CSS
   (`assets/styles/**/*.css`, `*.map`), `languages/*.mo`;
2. **prettier** — format sources in place (`assets/scripts/src/**/*.js`,
   `bundler/**/*.{js,mjs}`, root `*.js`) — the build rewrites sources by design;
3. **css** — sass compiles every non-`_` entry under `assets/sass/` (parallel,
   no maps in build) → `assets/styles/{path}.css` + clean-css `.min.css`
   (no SCSS entries exist right now — the step warns and no-ops);
4. **js** — ordered concat with the concat banner → `admin.js`; terser +
   min banner → `admin.min.js`;
5. **i18n** — wp-cli `i18n make-pot` → `languages/simple-revision-control.pot`,
   then `i18n update-po` per existing `.po`; `make-mo` only in `build:github`;
6. **zip** — staging copy → placeholder replacement → `release/{flavor}/{version}/{name}/`
   → `release/{flavor}/{name}.zip`;
7. **plugin-check** (wporg only) — see below;
8. done — progress bar + total time; any failure prints `BUILD FAILED` and exits 1.

## Plugin Check (PCP, wporg build step 7)

`bundler/pluginCheck.mjs` — copies the staged release
(`release/wporg/{version}/{name}`) into the local WordPress plugins dir
(parent of the plugin dir; override with `PLUGIN_CHECK_PLUGINS_DIR`) under
the real slug, runs `wp plugin check <slug>` via `wp-cli.phar` (excluding the
vendored `includes/iworks/*` submodules), then removes the copy.
**Fails the build on any ERROR** (warnings are informational).
Skips gracefully (with a warning) when `wp-cli.phar` or the Plugin Check
plugin is missing. `PLUGIN_CHECK_SKIP=1` skips the step for one build.
After the check (also on a red build), the staged release is **symlinked**
into the WordPress plugins dir (`wp-content/plugins/simple-revision-control` →
`dev-simple-revision-control/release/wporg/{version}/simple-revision-control`,
relative link) so the local site runs the actual built version.
`PLUGIN_SYMLINK_SKIP=1` skips it.

Current state: 1 pre-existing PCP error in the plugin's own code
(`missing_direct_file_access_protection` — `etc/options.php` and the empty
stub `index.php` files lack ABSPATH guards) — code debt, not a build problem;
fix in the plugin rather than skipping the check.

## Placeholders (release copies only)

Applied to every staged text file (binary extensions and `.mo/.po/.pot` skipped):
`AUTHOR_NAME/URI`, `BUILDTIME`, `IWORKS_RATE_TEXTDOMAIN`,
`IWORKS_OPTIONS_TEXTDOMAIN`, `PLUGIN_DESCRIPTION`, `PLUGIN_GITHUB_WEBSITE`,
`PLUGIN_NAME`, `PLUGIN_REQUIRES_PHP`, `PLUGIN_REQUIRES_WORDPRESS`,
`PLUGIN_TESTED_WORDPRESS`, `PLUGIN_TAGLINE`, `PLUGIN_TAGS`, `PLUGIN_TILL_YEAR`,
`PLUGIN_TITLE`, `PLUGIN_URI`, `PLUGIN_VERSION`, and the `^Version: .*$` header
line — all sourced from `package.json` + build time (`bundler/config.mjs`).

The version lives **only** in `package.json`.

## Release ZIPs

- **WordPress.org** (`build:wporg`) → `release/wporg/{version}/{name}/`. Excludes:
  repo meta (`.git*`, `.editorconfig`, `.opencode/`, `bundler/`, `AGENTS.md`,
  lock files, `wp-cli.phar`, `package.json`, `README.md`, `LICENSE`, tests,
  `release/`, `.sass-cache`, `**/*.map`), `includes/pro/`, asset sources
  (`assets/sass`, `assets/scripts/src`, `assets/scss`,
  `assets/styles/frontend`), the GitHub self-updater class
  (`includes/iworks/simple-revision-control/class-simple-revision-control-github.php`),
  `languages/*.po|*.mo`. `readme.txt` **is** included (required by wp.org).
- **GitHub** (`build:github`) → `release/github/{version}/{name}/`.
  Base excludes plus `readme.txt`; asset sources and `.po` **are** included,
  `.mo` compiled by `make-mo`. Pairs with the GitHub self-updater class.
- Copy rules are ordered and evaluated **first match wins** (grunt semantics) —
  the catch-all `**/*` include must stay last (`bundler/config.mjs`).
- A missing main plugin file logs a warning; staging proceeds.

## Tests

- `bun run test` → `phpunit --configuration tests/php/phpunit.xml`.
  Needs a WordPress test environment (the `tests/` dir may not exist yet).

## Runtime quirks / pitfalls (do not fix without approval)

- The build's prettier step **rewrites source files** — that is intended.
  A diff after `bun run build` containing only formatting is expected.
- Copy rules: negations must precede the catch-all `**/*` include — reordering
  silently leaks sources into the ZIPs.
- `assets/scripts/src/admin/options.js` is listed in the concat order but does
  not exist in the repo — the JS pipeline concatenates only existing files.
- `bun run make-pot` needs network on the first run (wp-cli.phar download).
- Terser mangles variable names differently than grunt-uglify — minified
  bundles are not byte-identical to old artifacts, only equivalent.
