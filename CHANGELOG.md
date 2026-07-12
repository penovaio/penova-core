# Changelog

Developer-facing changes to Penova Core. For step-by-step migration guidance see
[docs/guides/upgrading-core.md](docs/guides/upgrading-core.md); if a fresh install
misbehaves see [docs/guides/troubleshooting-core.md](docs/guides/troubleshooting-core.md).

Penova Core follows semantic versioning: breaking changes to the public contract
land only on a MAJOR, always with a documented migration path.

## [1.0.0] — 2026-07-12

First stable release of Penova Core. The public surface below is now stable under
semantic versioning.

- **Stable** (SemVer-guaranteed): the Manifest sections (`identity`, `menu`,
  `widgets`, `permissions`), the configuration format, and the Workspace
  routes/prefix (`penova.workspace.*`) with the Operator role.
- **Experimental** (may change or be withdrawn without a MAJOR): the module-frontend
  seam — the Manifest `frontend` section and its coordinate.
- **Internal** (not a contract): the concrete Resource shape and the Workspace
  widget pipeline.

Install: `composer create-project penovaio/core my-app`.

## [1.0.0-rc.1] — 2026-07-12

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

## [0.1.0] — 2026-07-06

- White Label / Branding in Settings.
- Onboarding Workspace.
