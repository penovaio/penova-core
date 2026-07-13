# Changelog

Developer-facing changes to Penova Core. For step-by-step migration guidance see
[docs/guides/upgrading-core.md](docs/guides/upgrading-core.md); if a fresh install
misbehaves see [docs/guides/troubleshooting-core.md](docs/guides/troubleshooting-core.md).

Penova Core follows semantic versioning: breaking changes to the public contract
land only on a MAJOR, always with a documented migration path.

## [1.1.0] - 2026-07-12

- **Removed the bundled reference module.** Core now ships with no business
  module at all - `app/Modules/` carries only the module author guide. A fresh
  install is unaffected, because Core already enabled no module by default. The
  installer's starter profiles are now `minimal` and `standard` (the demo
  profile that enabled the reference module is gone). To build your own module,
  see the module author guide at `app/Modules/README.md`.
- **Philosophy-led identity.** The landing page, README, and docs now lead with
  Penova Core's positioning - *the shared foundation for modular Laravel
  products* - and its three design principles: keep the Core small, share what
  every product needs, and build everything else as modules.
- **Redesigned landing page.** A cleaner `/` with the tagline, a Get Started
  step, "what's in the Core", the module directions, and the design principles.
- **Plain hyphens.** Replaced typographic dashes with plain hyphens across the
  source and docs.

## [1.0.4] - 2026-07-12

A patch that smooths out installation. No changes to the public contract.

- **Faster install.** The optimized autoloader is no longer forced during
  installation, so `composer create-project` finishes noticeably faster - it
  builds the full classmap (thousands of classes) only when you ask for it.
  Optimize for production the usual way: `composer install --optimize-autoloader`.
- **No half-shown prompt.** The post-install setup now runs non-interactively,
  because Composer runs it without a usable terminal. It applies safe defaults,
  migrates, seeds and builds, then points you to `php artisan penova:setup` -
  which asks the questions interactively when you run it yourself.

## [1.0.3] - 2026-07-12

A patch that makes the install a single command. No changes to the public contract.

- **One-command install.** `composer create-project` now finishes a ready-to-run
  project: `penova:setup` configures `.env`, migrates, seeds the Operator account,
  and installs and builds the front-end. `php artisan serve` is the only step left.
- **Docs.** README and getting-started updated to the one-command flow.

## [1.0.2] - 2026-07-12

A patch focused on the install experience. No changes to the public contract.

- **Portable setup prompts.** `php artisan penova:setup` now uses the framework's
  built-in question helpers, so the language, timezone, starter profile and
  database choices work the same on Windows, macOS and Linux. Its output is
  ASCII-only. When the environment is non-interactive it applies safe defaults
  and points to `php artisan penova:setup` for later customization.
- **Quieter install.** Moved the inline test fixtures into their own files, so
  `composer create-project` no longer prints PSR-4 autoload warnings.

## [1.0.1] - 2026-07-12

A first patch focused on the install experience and public-release housekeeping.
No changes to the public contract.

- **Interactive setup.** `composer create-project` now finishes with a short
  guided setup (`php artisan penova:setup`) - interface language, fallback
  language, timezone, starter profile (minimal / standard / full), database
  driver, and an optional front-end build - then writes `.env`, migrates, and
  seeds the Core baseline. It falls back to safe defaults in non-interactive
  environments (CI, no TTY), so an automated install never blocks.
- **Configurable timezone.** The application timezone is now driven by
  `APP_TIMEZONE` (defaults to `UTC`).
- **License.** Added the MIT License.
- **Documentation.** The README is now bilingual (English, then Persian).

## [1.0.0] - 2026-07-12

First stable release of Penova Core. The public surface below is now stable under
semantic versioning.

- **Stable** (SemVer-guaranteed): the Manifest sections (`identity`, `menu`,
  `widgets`, `permissions`), the configuration format, and the Workspace
  routes/prefix (`penova.workspace.*`) with the Operator role.
- **Experimental** (may change or be withdrawn without a MAJOR): the module-frontend
  seam - the Manifest `frontend` section and its coordinate.
- **Internal** (not a contract): the concrete Resource shape and the Workspace
  widget pipeline.

Install: `composer create-project penovaio/core my-app`.

## [1.0.0-rc.1] - 2026-07-12

First release candidate toward 1.0.0.

- **Store decoupled.** Core boots complete with no business module enabled; the
  `config/penova.php` `modules` array ships empty and the Core seeder adds no
  module. Store stays in the repository as a disabled-by-default reference module.
- **Locale-neutral UI.** English is the base and fallback language; a fresh install
  renders English, left-to-right, with Latin numerals. Persian is a first-party
  opt-in locale (`APP_LOCALE=fa`). Regional formatting stays out of Core.
- **Experimental module-frontend seam.** A Module contributes Workspace pages and
  widgets through a typed Manifest `frontend` section resolved via a generated
  registry (`php artisan penova:frontend-registry`, run by `npm run build` / `dev`).
- **Operator / Workspace naming.** The authenticated environment is the Workspace
  (`/workspace`) and the seeded role is the Operator; install, login and docs use
  this vocabulary. The legacy `PENOVA_ADMIN_*` env vars are honoured for one cycle.

## [0.1.0] - 2026-07-06

- White Label / Branding in Settings.
- Onboarding Workspace.
