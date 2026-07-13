# Penova Core

_Read this in: **English** (below) · [فارسی](#فارسی)_

**The shared foundation for modular Laravel products.**

Penova Core provides the shared infrastructure that modular Laravel products are
built on - authentication, access control, settings, notifications, and a unified
Workspace - so every module builds on a consistent foundation.

## Design principles

Penova Core is shaped by three principles:

- **Keep the Core small.**
- **Share what every product needs.**
- **Build everything else as modules.**

The Core stays focused on shared infrastructure; the capabilities that make each
product unique live in independent modules. **Core never depends on modules;
modules build on Core.**

## What's in the Core

- **Authentication & access** - login, optional registration, password reset, and
  Workspace screens for users, roles, and permissions.
- **Workspace** - a unified interface (Vue 3, Inertia 2, Tailwind 4) for managing
  your application and hosting product modules.
- **Shared services** - runtime settings with white-label branding, database
  notifications, an activity log, and a reusable UI component set.
- **DataTable pattern** - a server-side builder plus a matching `DataTable.vue` for
  any CRUD screen.

This repository is **Core only** - it ships no business module. Business
capabilities (CRM, booking, blog, …) install on top of Core as separate modules.

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+ and npm (for the Vite frontend)

## Installation

Install with Composer - a short **interactive setup** runs automatically:

```bash
composer create-project penovaio/core my-app
```

Once the project is created, the setup asks a few questions - interface language,
fallback language, timezone, starter profile (minimal / standard / full), and
database driver - then it configures your `.env`, runs the initial migration,
seeds the Operator account, and installs and builds the front-end. In a
non-interactive environment (CI) it uses safe defaults and never blocks.

When it finishes, the project is ready. Just serve it:

```bash
cd my-app
php artisan serve
```

Prefer a manual clone? Clone the repository, install dependencies, then run the
setup yourself:

```bash
git clone https://github.com/penovaio/penova-core.git && cd penova-core
composer install
cp .env.example .env
php artisan key:generate
php artisan penova:setup      # interactive; or: php artisan penova:install
npm install && npm run build
php artisan serve
```

> **Front-end build.** `npm run build` / `npm run dev` regenerate the
> module-frontend registry before Vite. Build the frontend through these scripts -
> a bare `vite build` fails on a fresh checkout.

## Quick start

After the server is running:

- `/` shows the welcome page, `/login` the login form, `/workspace` the
  authenticated Workspace.
- Default development credentials (change them anywhere real):

  | | |
  |---|---|
  | Email | `operator@example.com` |
  | Password | `password` |

The interface is English by default; the setup can switch it to Persian, or set
`APP_LOCALE=fa` in `.env` yourself. If anything goes wrong, see the
[troubleshooting guide](docs/guides/troubleshooting-core.md).

## Extend with modules

Product capabilities belong in modules. Each module ships its own service provider
and declares what it contributes through a single Manifest. Wire one in by adding
its provider to `config/penova.php`:

```php
'modules' => [
    App\Modules\Blog\BlogServiceProvider::class,
],
```

See the [module author guide](app/Modules/README.md) for the full contract.

## Documentation

- [Getting started](docs/getting-started.md)
- [Architecture overview](docs/architecture.md)
- [Module author guide](app/Modules/README.md)
- [Upgrading Core](docs/guides/upgrading-core.md)
- [Troubleshooting](docs/guides/troubleshooting-core.md)

## License

Penova Core is released under the **MIT License** - see [LICENSE](LICENSE).

## Contributing

Suggestions, bug reports and pull requests that improve Core (not product modules)
are welcome - especially Workspace UX/UI, wider integration test coverage, and the
module contract.

---

# فارسی

_[English](#penova-core) بالا · **فارسی** در این بخش_

**زیرساخت مشترک برای محصولات ماژولار Laravel**

Penova Core زیرساختِ مشترکی را فراهم می‌کند که محصولاتِ ماژولارِ Laravel بر پایهٔ آن ساخته
می‌شوند - احراز هویت، کنترلِ دسترسی، تنظیمات، اعلان‌ها و یک Workspaceِ یکپارچه - تا هر
ماژول بر پایه‌ای یکسان توسعه پیدا کند.

## اصولِ طراحی

Penova Core بر پایهٔ سه اصل شکل گرفته است:

- **Core را کوچک و متمرکز نگه دارید.**
- **آنچه میان همه‌ی محصولات مشترک است را در Core قرار دهید.**
- **تمام قابلیت‌های اختصاصی را در قالب ماژول توسعه دهید.**

هسته روی زیرساختِ مشترک متمرکز می‌ماند و قابلیت‌هایی که هر محصول را منحصربه‌فرد می‌کنند در
ماژول‌های مستقل قرار می‌گیرند. **Core هرگز به Modules وابسته نیست؛ Modules روی Core ساخته
می‌شوند.**

## آنچه در Core قرار دارد

- **احراز هویت و دسترسی** - ورود، ثبت‌نامِ اختیاری، بازیابیِ رمز، و صفحاتِ میزکار برای
  کاربران، نقش‌ها و مجوزها.
- **Workspace** - یک محیطِ یکپارچه (Vue 3، Inertia 2، Tailwind 4) برای مدیریتِ محصول و
  میزبانی از ماژول‌ها.
- **سرویس‌های مشترک** - تنظیماتِ runtime با برندینگِ White Label، اعلان‌ها، لاگِ فعالیت و
  مجموعه‌کامپوننت‌های UIِ قابل‌استفادهٔ مجدد.
- **الگوی DataTable** - یک builderِ سمت‌سرور و `DataTable.vue`ِ متناظر برای هر صفحهٔ CRUD.

این مخزن **فقط Core** است و هیچ ماژولِ بیزنسی ندارد. قابلیت‌های بیزنسی (CRM، Booking،
blog، …) به‌صورتِ ماژول‌های جداگانه روی Core نصب می‌شوند.

## پیش‌نیازها

- PHP 8.2+
- Composer
- Node.js 18+ و npm (برای frontendِ Vite)

## نصب

با Composer نصب کنید - یک **setup تعاملیِ کوتاه** خودکار اجرا می‌شود:

```bash
composer create-project penovaio/core my-app
```

پس از ساختِ پروژه، setup چند پرسش می‌پرسد - زبانِ رابط، زبانِ fallback، timezone،
پروفایلِ شروع (minimal / standard / full)، و درایورِ پایگاه‌داده - سپس `.env` را پیکربندی
می‌کند، migrationِ اولیه را اجرا می‌کند، حسابِ Operator را seed می‌کند و فرانت‌اند را نصب و
build می‌کند. در محیطِ غیرتعاملی (CI) از مقادیرِ امنِ پیش‌فرض استفاده می‌کند و هرگز متوقف
نمی‌شود.

وقتی تمام شد، پروژه آماده است. کافی است اجرایش کنید:

```bash
cd my-app
php artisan serve
```

نصبِ دستی با clone را ترجیح می‌دهید؟ مخزن را clone کنید، وابستگی‌ها را نصب کنید، و setup را
خودتان اجرا کنید:

```bash
git clone https://github.com/penovaio/penova-core.git && cd penova-core
composer install
cp .env.example .env
php artisan key:generate
php artisan penova:setup      # تعاملی؛ یا: php artisan penova:install
npm install && npm run build
php artisan serve
```

> **ساختِ frontend.** `npm run build` / `npm run dev` پیش از Vite رجیستریِ فرانت‌اندِ
> ماژول‌ها را می‌سازند. frontend را از طریقِ همین اسکریپت‌ها build کنید - اجرای مستقیمِ
> `vite build` روی checkoutِ تازه با خطا مواجه می‌شود.

## شروعِ سریع

پس از اجرای سرور:

- `/` صفحهٔ خوش‌آمد، `/login` فرمِ ورود، و `/workspace` میزکارِ احرازشده را نشان می‌دهد.
- هویتِ پیش‌فرضِ توسعه (در هر محیطِ واقعی تغییرش دهید):

  | | |
  |---|---|
  | ایمیل | `operator@example.com` |
  | رمز | `password` |

رابط به‌صورتِ پیش‌فرض انگلیسی است؛ setup می‌تواند آن را به فارسی تغییر دهد، یا خودتان
`APP_LOCALE=fa` را در `.env` قرار دهید. اگر مشکلی پیش آمد، [راهنمای عیب‌یابی](docs/guides/troubleshooting-core.md)
را ببینید.

## توسعه با ماژول‌ها

قابلیت‌های اختصاصی هر محصول در قالبِ ماژول توسعه پیدا می‌کنند. هر ماژول ServiceProviderِ
خودش را دارد و آنچه را ارائه می‌دهد از طریقِ یک Manifest اعلام می‌کند. یک ماژول را با افزودنِ
providerش به `config/penova.php` وصل کنید:

```php
'modules' => [
    App\Modules\Blog\BlogServiceProvider::class,
],
```

راهنمای کاملِ نویسندهٔ ماژول را در [app/Modules/README.md](app/Modules/README.md) ببینید.

## مستندات

- [شروع به کار](docs/getting-started.md)
- [مروری بر معماری](docs/architecture.md)
- [راهنمای نویسندهٔ ماژول](app/Modules/README.md)
- [ارتقای Core](docs/guides/upgrading-core.md)
- [عیب‌یابی](docs/guides/troubleshooting-core.md)

## لایسنس

Penova Core تحت لایسنس **MIT** منتشر می‌شود - متنِ کامل در فایل [LICENSE](LICENSE).

## مشارکت

پیشنهادها، گزارشِ باگ و Pull Requestهایی که Core را بهتر می‌کنند (نه ماژول‌های محصولی)
استقبال می‌شوند - به‌ویژه UX/UIِ میزکار، پوششِ بیشترِ تست‌های یکپارچه، و قراردادِ ماژول.
