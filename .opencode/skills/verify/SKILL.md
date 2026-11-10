---
name: verify
description: post-work verification — full project build and a check that everything works
---

## Plugin purpose

The "Simple Revision Control" plugin controls and limits post revisions in
WordPress:

- **Per-post-type revision limits** — unlimited, none, or a custom number of
  revisions per post type, enforced via `wp_revisions_to_keep`.
- **Revision cleanup** — delete existing revisions per post (row action) or in
  bulk from the post list table, with an admin notice afterwards.
- **Utilization report** — revision counts per post type on the settings page.
- **Compatibility** — works with popular WordPress themes.
- **Localization** — translated into multiple languages.

Use this context when judging whether a change makes sense and what to verify after it.

When a task that changed code (PHP, JS, SCSS) is finished, **before reporting success** run:

## 1. Sanity-check diffs

- `git diff --stat` — compare the changes against the list of files you actually edited. **Any changed file you did not edit is an alarm signal** (a stray build/formatter step overwriting sources).
- The build's prettier step rewrites JS sources in place — formatting-only diffs in `assets/scripts/src/**` right after a build are expected, anything else is not.
- Compiled files (`assets/styles/`, `assets/scripts/admin*.js`, `languages/*.mo`) are build outputs — they should never appear in a diff unless the task was explicitly about them.

## 2. Verification with a full build

Do not declare success based on a single quick command — run the **full pipeline** for the changed asset type (each must pass end-to-end):

- **JS (`assets/scripts/src/**`) and SCSS (`assets/sass/**`)**: full `bun run build:github` — must pass end-to-end (prettier → css → js → i18n → ZIP). Use `bun run build:wporg` when the wp.org release path is affected (note: Plugin Check fails on 1 pre-existing code error — ABSPATH guards in `etc/options.php` and the stub `index.php` files — compare against that baseline, don't introduce new ones; `PLUGIN_CHECK_SKIP=1` skips).
- **PHP (`includes/iworks/simple-revision-control/`, `etc/`, main file)**: `php -l` on every changed file (new files included).
- **Bundler changes (`bundler/**`)**: `bun run compile` + `bun run build` (both flavors) and check the staged ZIP contents (copy-rules order is load-bearing — see `.opencode/docs/build.md`).

## 3. Report

Summarize the build result and the list of verified files. If the build does not pass — fix it before reporting task completion (or clearly list the blockers for the user to decide). Never write "done" without a successful completion of step 2.
