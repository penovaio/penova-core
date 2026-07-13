<?php

use App\Core\Support\ManifestRegistry;

test('the registry is empty by default (D-026 - Core enables no module)', function () {
    // Core enables no business module by default, so the registry resolves
    // empty. Enabling a module is the application-level opt-in a real app makes
    // when it wires a provider into config/penova.php.
    $registry = app(ManifestRegistry::class);

    expect($registry->isEmpty())->toBeTrue();
    expect($registry->all())->toBe([]);
    expect($registry->get('blog'))->toBeNull();
});
