<?php

use App\Core\Support\FrontendRegistry;

/**
 * Combined P2/P3 — the EXPERIMENTAL module-frontend registry generator
 * (RFC-006 / D-028): module-owned source coordinate, deterministic output, and
 * the full loud named-failure taxonomy. The registry is a git-ignored build
 * artifact — these tests exercise the generator directly with a sample module.
 */

function sampleModule(array $overrides = []): array
{
    return array_replace([
        'key' => 'blog',
        'source' => '@/Modules/Blog',
        'widgets' => [['key' => 'blog-recent-posts', 'area' => 'blog']],
        'frontend' => [
            'widgets' => [['key' => 'blog-recent-posts', 'entry' => 'Widgets/RecentPostsCard']],
            'pages' => [['name' => 'Modules/Blog/Posts/Index', 'entry' => 'Pages/Posts/Index']],
        ],
    ], $overrides);
}

test('build maps contributions to specifiers from each Module OWN coordinate', function () {
    $map = FrontendRegistry::build([sampleModule()]);

    expect($map)->toBe([
        'widgets' => ['blog-recent-posts' => '@/Modules/Blog/Widgets/RecentPostsCard.vue'],
        'pages' => ['Modules/Blog/Posts/Index' => '@/Modules/Blog/Pages/Posts/Index.vue'],
    ]);
});

test('build is deterministic — identical inputs render byte-for-byte identical, sorted output', function () {
    $modules = [sampleModule()];
    expect(FrontendRegistry::render(FrontendRegistry::build($modules)))
        ->toBe(FrontendRegistry::render(FrontendRegistry::build($modules)));
});

test('a fully paired module with a passing resolver builds cleanly', function () {
    $map = FrontendRegistry::build([sampleModule()], fn (string $s) => true);
    expect($map['widgets'])->toHaveKey('blog-recent-posts');
    expect($map['pages'])->toHaveKey('Modules/Blog/Posts/Index');
});

// --- Named failure taxonomy: one negative per category (all loud) ---

test('duplicate contribution — widget key across Modules', function () {
    FrontendRegistry::build([
        sampleModule(),
        sampleModule(['key' => 'other', 'source' => '@/Modules/Other', 'frontend' => [
            'widgets' => [['key' => 'blog-recent-posts', 'entry' => 'Widgets/Dup']],
            'pages' => [],
        ], 'widgets' => [['key' => 'blog-recent-posts', 'area' => 'x']]]),
    ]);
})->throws(InvalidArgumentException::class, 'duplicate contribution — widget key [blog-recent-posts]');

test('duplicate contribution — page name across Modules', function () {
    FrontendRegistry::build([
        sampleModule(),
        sampleModule(['key' => 'other', 'source' => '@/Modules/Other', 'widgets' => [], 'frontend' => [
            'widgets' => [],
            'pages' => [['name' => 'Modules/Blog/Posts/Index', 'entry' => 'Pages/X']],
        ]]),
    ]);
})->throws(InvalidArgumentException::class, 'duplicate contribution — page name [Modules/Blog/Posts/Index]');

test('duplicate contribution — a page name in the reserved Core namespace', function () {
    FrontendRegistry::build([sampleModule(['widgets' => [], 'frontend' => [
        'widgets' => [],
        'pages' => [['name' => 'Core/Users/Index', 'entry' => 'Pages/X']],
    ]])]);
})->throws(InvalidArgumentException::class, 'collides with the reserved Core namespace');

test('missing frontend entry — a backend widget with no frontend contribution', function () {
    FrontendRegistry::build([sampleModule(['frontend' => ['widgets' => [], 'pages' => []]])]);
})->throws(InvalidArgumentException::class, 'missing frontend entry — backend widget [blog-recent-posts]');

test('orphan frontend widget — a frontend widget with no backend declaration', function () {
    FrontendRegistry::build([sampleModule(['widgets' => [], 'frontend' => [
        'widgets' => [['key' => 'ghost', 'entry' => 'Widgets/Ghost']],
        'pages' => [],
    ]])]);
})->throws(InvalidArgumentException::class, 'orphan frontend widget — frontend widget [ghost]');

test('unknown target area — a backend widget declares an empty area', function () {
    FrontendRegistry::build([sampleModule(['widgets' => [['key' => 'blog-recent-posts', 'area' => '']]])]);
})->throws(InvalidArgumentException::class, 'unknown target area — backend widget [blog-recent-posts]');

test('unresolved entry — an entry that does not resolve through its coordinate', function () {
    FrontendRegistry::build([sampleModule()], fn (string $s) => false);
})->throws(InvalidArgumentException::class, 'unresolved entry');

// --- Provenance ---

test('a hand-edited artifact fails the integrity check (provenance)', function () {
    $artifact = FrontendRegistry::render(FrontendRegistry::build([sampleModule()]));
    expect(FrontendRegistry::verifyIntegrity($artifact))->toBeTrue();
    expect(FrontendRegistry::verifyIntegrity(str_replace('RecentPostsCard', 'Hacked', $artifact)))->toBeFalse();
});
