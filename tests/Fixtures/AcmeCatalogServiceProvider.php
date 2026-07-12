<?php

namespace Tests\Fixtures;

use App\Core\Support\DeclaresFrontendPackage;
use App\Core\Support\DeclaresFrontendSource;
use App\Core\Support\Manifest;
use App\Core\Support\PenovaModule;
use Illuminate\Support\ServiceProvider;

/**
 * P4 -- EXTERNAL-SHAPED fixture (RFC-006 / D-028). A fake Module whose provider
 * owns a frontend coordinate DISTINCT from the in-repo `@/Modules/{key}` default
 * and declares a separate frontend package pairing. It is package-shaped but
 * LOCAL: no publishing, no Composer/npm package, no file moves. It proves the
 * SAME generic generator/resolver path Core uses for any Module -- resolving a
 * provider-owned coordinate and enforcing pairing/peers, with Core naming no
 * Module. It relies on no bundled module.
 */
class AcmeCatalogServiceProvider extends ServiceProvider implements DeclaresFrontendPackage, DeclaresFrontendSource, PenovaModule
{
    public static function manifest(): Manifest
    {
        return Manifest::for('acme-catalog', 'Acme Catalog', 'External-shaped reference module.', '1.0.0')
            ->widgets([
                ['key' => 'catalog-featured', 'type' => 'card', 'title' => 'Featured', 'cols' => 1, 'order' => 100, 'area' => 'catalog'],
            ])
            ->frontend([
                'widgets' => [['key' => 'catalog-featured', 'entry' => 'Widgets/FeaturedCard']],
                'pages' => [['name' => 'Acme/Catalog/Index', 'entry' => 'Pages/Catalog/Index']],
            ]);
    }

    // Provider-owned coordinate -- a package-style root, NOT the @/Modules/{key} default.
    public static function frontendSource(): string
    {
        return '@acme-catalog';
    }

    public static function frontendPackage(): array
    {
        return [
            'name' => '@acme/catalog-frontend',
            'version' => '^1.0',
            'peers' => ['vue' => '^3.5', '@inertiajs/vue3' => '^2.0'],
        ];
    }
}
