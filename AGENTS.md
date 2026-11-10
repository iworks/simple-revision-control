# AGENTS.md — Simple Revision Control

> Working rules only. Technical knowledge lives in topic docs — read on demand, never preload.

## What the project is

The "Simple Revision Control" WordPress plugin:
main file + PHP classes (`/includes/iworks/simple-revision-control`) + options config (`/etc/options.php`)
+ assets (`/assets`), built with a bun bundler (`/bundler` → `release/` ZIPs);
sources contain build-time placeholders (`PLUGIN_VERSION`, `BUILDTIME`, …)

## Docs map — read only what the task needs

| Task | Read first |
| --- | --- |
| PHP classes, revision control, options, AJAX | `.opencode/docs/classes.md` |
| Bun bundler, build, dev watcher, ZIPs, placeholders, translations (.pot/.po) | `.opencode/docs/build.md` |
| Loading flow, assets, enqueue, i18n, dependencies, project conventions | `.opencode/docs/project.md` |
| After any task that changed code | skill `verify` (`.opencode/skills/verify/SKILL.md`) |

- Read ONLY the docs relevant to the current task — do not load everything upfront.
- When a change alters documented behavior, update the corresponding doc in the same task.

## Evergreen rules (every task)

- All documentation and README in English.
- Never commit, push, amend or create PRs without an explicit request.
- Naming: kebab-case files (`class-simple-revision-control-{name}.php`);
  PHP classes `iWorks_Simple_Revision_Control_` prefix (existing style), snake_case methods.
- Version lives only in `package.json` — placeholders (`PLUGIN_VERSION`,
  `BUILDTIME`) are replaced by the bundler during build; never hardcode real values in sources.
- Do not fix known typos/pitfalls without approval (see topic docs).

## Commands

- `bun run dev` — watcher, compile assets on change
- `bun run compile` — one-shot asset compile
- `bun run build` — production build, both ZIPs (wporg + github)
- `bun run build:wporg` — production build, wp.org ZIP only (+ Plugin Check)
- `bun run build:github` — production build, GitHub ZIP only (+ `.mo`)
- `bun run make-pot` — translations (.pot + .po update; downloads `wp-cli.phar` on first run)
- `bun run update-libs` — git pull on the installed iWorks submodules
- `bun run test` — PHPUnit (needs a WordPress test environment)
