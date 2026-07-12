<?php

use App\Core\Support\ManifestRegistry;
use Tests\Fixtures\FakeLegacyModule;

test('a legacy module is adapted one-way into a manifest, with a deprecation signal', function () {
    config(['penova.modules' => [FakeLegacyModule::class]]);
    app()->forgetInstance(ManifestRegistry::class);

    // Capture (and swallow) the expected deprecation so it does not surface
    // as a failure, and so we can assert it fired.
    $deprecation = null;
    set_error_handler(function (int $errno, string $message) use (&$deprecation) {
        $deprecation = $message;

        return true;
    }, E_USER_DEPRECATED);

    try {
        $registry = app(ManifestRegistry::class);
    } finally {
        restore_error_handler();
    }

    // The legacy hooks are adapted into the one resolved manifest set.
    expect($registry->has('legacy'))->toBeTrue();
    expect($registry->get('legacy')['name'])->toBe('Legacy Module');
    expect(collect($registry->menuItems())->pluck('key'))->toContain('legacy');
    expect(collect($registry->widgetDescriptors())->pluck('key'))->toContain('legacy-widget');
    expect($registry->permissionSlugs())->toContain('legacy.view');

    // And the deprecation was signalled, naming the offending provider.
    expect($deprecation)->toContain('deprecated');
    expect($deprecation)->toContain(FakeLegacyModule::class);
});
