# Penova Core

_Read this in: **English** (below) · [فارسی](#فارسی)_

Penova Core is the Laravel foundation for building modular monolith products with a
Core + Modules architecture.

It ships a production-ready **Workspace** - authentication, users, roles and
permissions, settings, notifications, an activity log, a UI component set and a
reusable server-side DataTable - so you can focus on your product instead of
rebuilding the same foundation every project.

This repository is **Core only**: it contains no business modules (CRM,
Booking, blog, …). Business capabilities install on top of Core as separate modules.

## What Penova Core is

- A **free, complete Laravel core** that solves the parts repeated in most projects
  once: auth, users, roles, settings, notifications, activity logs, UI and data
  tables.
- A **modular monolith**: one deployable application split into a product-agnostic
  `app/Core` layer and a business-specific `app/Modules` layer. **Core never depends
  on Modules; Modules build on Core.**

## Features

- **Authentication** - login, optional registration, forgot/reset password.
- **Users & Roles** - Workspace screens with a simple permission middleware, no
  extra package required.
- **Settings & White Label** - runtime settings plus branding (name, logo, color,
  footer) reflected across the Workspace.
- **Notifications** - Laravel database notifications as a shared surface, with an
  unread badge available to every module.
- **Activity Logs** - automatic created/updated/deleted logging via a trait.
- **Workspace UI & Components** - ready layouts (Vue 3, Inertia 2, Tailwind 4) and
  shared components: Button, TextInput, Modal, Toast, Pagination, DataTable.
- **DataTable pattern** - a backend builder for server-side search/sort/pagination
  and a matching `DataTable.vue` for any CRUD screen.

## What is *not* in Core

To keep Core product-agnostic, these install as separate modules: business modules
(CRM, Booking, blog, …), cart/checkout, payment gateways, SMS/messaging, and
domain-specific frontend pages.

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

## Adding modules

Core is built to host business modules. Each module ships its own service provider
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

Penova Core هستهٔ لاراولی برای ساختِ محصولاتِ modular monolith با معماری Core + Modules
است.

یک **میزکارِ (Workspace) آمادهٔ تولید** ارائه می‌دهد - احراز هویت، کاربران، نقش‌ها و
دسترسی‌ها، تنظیمات، اعلان‌ها، لاگِ فعالیت، مجموعه‌کامپوننت‌های UI و یک DataTableِ
سمت‌سرورِ قابل‌استفادهٔ مجدد - تا به‌جای بازسازیِ همان زیرساخت در هر پروژه، روی محصولِ
خودتان تمرکز کنید.

این مخزن **فقط Core** است؛ هیچ ماژولِ بیزنسی (CRM، Booking، blog، …) ندارد. قابلیت‌های
بیزنسی به‌صورتِ ماژول‌های جداگانه روی Core نصب می‌شوند.

## Penova Core چیست

- یک **هستهٔ رایگان و کاملِ لاراول** که بخش‌های تکرارشونده در بیشترِ پروژه‌ها را یک‌بار
  حل می‌کند: احراز هویت، کاربران، نقش‌ها، تنظیمات، اعلان‌ها، لاگِ فعالیت، UI و جدول‌های
  داده.
- یک **modular monolith**: یک اپلیکیشنِ قابل‌استقرار که به لایهٔ product-agnosticِ
  `app/Core` و لایهٔ بیزنسیِ `app/Modules` تقسیم شده است. **Core هرگز به Modules وابسته
  نیست؛ Modules روی Core ساخته می‌شوند.**

## ویژگی‌ها

- **احراز هویت** - ورود، ثبت‌نامِ اختیاری، فراموشی/بازیابیِ رمز.
- **کاربران و نقش‌ها** - صفحاتِ میزکار با یک middlewareِ ساده، بدونِ پکیجِ اضافه.
- **تنظیمات و White Label** - تنظیماتِ runtime به‌همراه برندینگ (نام، لوگو، رنگ، فوتر)
  که در سراسرِ میزکار بازتاب می‌یابد.
- **اعلان‌ها** - database notificationsِ لاراول به‌عنوان سطحِ مشترک، با badgeِ
  خوانده‌نشده برای همهٔ ماژول‌ها.
- **لاگِ فعالیت** - ثبتِ خودکارِ created/updated/deleted از طریقِ یک trait.
- **UI و کامپوننت‌های میزکار** - layoutهای آماده (Vue 3، Inertia 2، Tailwind 4) و
  کامپوننت‌های مشترک: Button، TextInput، Modal، Toast، Pagination، DataTable.
- **الگوی DataTable** - یک builder در backend برای search/sort/paginationِ سمت‌سرور و
  `DataTable.vue`ِ متناظر برای هر صفحهٔ CRUD.

## چه چیزهایی داخلِ Core نیست

برای این‌که Core محصول-محور نماند، این موارد به‌صورتِ ماژولِ جداگانه نصب می‌شوند: ماژول‌های
بیزنسی (CRM، Booking، blog، …)، سبد خرید/Checkout، درگاه‌های پرداخت، پیامک/پیام‌رسانی، و
صفحاتِ frontendِ دامین‌محور.

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

## افزودنِ ماژول‌ها

Core برای میزبانیِ ماژول‌های بیزنسی ساخته شده است. هر ماژول ServiceProviderِ خودش را دارد و
آنچه را ارائه می‌دهد از طریقِ یک Manifest اعلام می‌کند. یک ماژول را با افزودنِ providerش به
`config/penova.php` وصل کنید:

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
