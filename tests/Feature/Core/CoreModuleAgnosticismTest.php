<?php

/**
 * Combined P2/P3 — static Module-agnosticism guard (RFC-006 / D-028; 13, Core's
 * first rule). Maintained Core source and its build configuration must never name,
 * import, or assume a specific Module, and must keep NO module-directory glob or
 * in-repo render-path convention.
 *
 * Scanned: Core's application source (`app/Core`), its frontend source
 * (`resources/js/Core`), the app entrypoint (`resources/js/app.js`), the build
 * ALIAS config (`vite.config.js` — where a Module path could otherwise be smuggled
 * in as an alias), and the Core CONFIG (`config/penova.php` — a shipped Core file
 * that must teach no specific business Module, even in commentary; its module list
 * is opaque provider class-strings).
 *
 * The ONLY exception is narrow and explicit: the generated registry
 * (`resources/js/generated`) is a git-ignored BUILD ARTIFACT, not Core source;
 * its concrete `@/Modules/<Name>` specifiers are registry OUTPUT, not Core
 * knowledge. Everything else — production Core code, config, and build aliases —
 * must name no specific Module. Generic placeholders (`@/Modules/<Name>`,
 * `@/Modules/{key}`) are fine; a concrete module directory name is not.
 */

function coreSourceFiles(): array
{
    $generated = base_path('resources/js/generated'); // git-ignored build artifact — excluded
    $files = [base_path('resources/js/app.js'), base_path('vite.config.js'), base_path('config/penova.php')];

    foreach ([base_path('app/Core'), base_path('resources/js/Core')] as $root) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if (str_starts_with($file->getPathname(), $generated)) {
                continue; // never scan the generated registry
            }
            if (in_array($file->getExtension(), ['php', 'vue', 'js'], true)) {
                $files[] = $file->getPathname();
            }
        }
    }

    return $files;
}

test('no Core source names a specific Module', function () {
    // A representative concrete module name: Core must never carry it in code.
    // Generic placeholders (@/Modules/<Name>, @/Modules/{key}) are allowed.
    foreach (coreSourceFiles() as $file) {
        $source = (string) file_get_contents($file);

        expect($source)->not->toContain('Modules/Blog');   // JS / Inertia name
        expect($source)->not->toContain('Modules\\Blog');  // PHP namespace
    }
});

test('no Core source keeps a module-directory glob or render-path convention', function () {
    foreach (coreSourceFiles() as $file) {
        $source = (string) file_get_contents($file);

        // No import.meta.glob that reaches into a Modules directory — module
        // frontend resolves ONLY through the generated registry now.
        expect(preg_match('#import\.meta\.glob\([^)]*Modules#', $source))->toBe(0);
    }
});
