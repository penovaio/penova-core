<?php

use App\Core\Support\FrontendPackageCheck;
use App\Core\Support\FrontendRegistry;
use App\Core\Support\ManifestRegistry;
use Tests\Fixtures\AcmeCatalogServiceProvider;

/**
 * P4 -- EXTERNAL-SHAPED fixture (RFC-006 / D-028). A fake Module whose provider
 * owns a frontend coordinate DISTINCT from the in-repo `@/Modules/{key}` default
 * and declares a separate frontend package pairing. It is package-shaped but
 * LOCAL: no publishing, no Composer/npm package, no file moves. It proves the
 * SAME generic generator/resolver path Core uses for any Module -- resolving a
 * provider-owned coordinate and enforcing pairing/peers, with Core naming no
 * Module. It relies on no bundled module.
 */
beforeEach(function () {
    config()->set('penova.modules', [AcmeCatalogServiceProvider::class]);
    $this->registry = new ManifestRegistry();
});

test('Core reads the provider-owned coordinate generically - distinct from the in-repo default', function () {
    $modules = $this->registry->frontendModules();

    expect($modules)->toHaveCount(1);
    expect($modules[0]['source'])->toBe('@acme-catalog');
    expect($modules[0]['source'])->not->toBe('@/Modules/acme-catalog'); // the default it overrides
});

test('widget and page contributions resolve through the SAME generic generator/resolver path', function () {
    $map = FrontendRegistry::build(
        $this->registry->frontendModules(),
        fn (string $specifier): bool => str_starts_with($specifier, '@acme-catalog/'), // the package's own resolver
    );

    expect($map['widgets'])->toBe(['catalog-featured' => '@acme-catalog/Widgets/FeaturedCard.vue']);
    expect($map['pages'])->toBe(['Acme/Catalog/Index' => '@acme-catalog/Pages/Catalog/Index.vue']);
});

test('the declared package pairing surfaces through the provider boundary and passes when matched', function () {
    $packages = $this->registry->frontendPackages();

    expect($packages)->toBe([[
        'key' => 'acme-catalog',
        'package' => ['name' => '@acme/catalog-frontend', 'version' => '^1.0', 'peers' => ['vue' => '^3.5', '@inertiajs/vue3' => '^2.0']],
    ]]);

    // Passes (no throw) - a matched package with compatible peers.
    FrontendPackageCheck::verify(
        $packages,
        ['@acme/catalog-frontend' => '1.4.0'],
        ['vue' => '3.5.13', '@inertiajs/vue3' => '2.0.11'],
    );

    expect(true)->toBeTrue();
});

test('a mismatched installed package for the external-shaped Module fails loudly', function () {
    FrontendPackageCheck::verify(
        $this->registry->frontendPackages(),
        [], // the paired package is not installed
        ['vue' => '3.5.13', '@inertiajs/vue3' => '2.0.11'],
    );
})->throws(InvalidArgumentException::class, 'module frontend package mismatch');
