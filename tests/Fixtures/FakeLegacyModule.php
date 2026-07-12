<?php

namespace Tests\Fixtures;

use App\Core\Support\LegacyModuleManifest;

/**
 * The one-way deprecation bridge: a Module still on the pre-D-023
 * scattered hooks is adapted into a single Manifest, and a deprecation is
 * signalled. Defends that legacy Modules keep working for one cycle
 * (D-023 §4; guardrail 3).
 */
class FakeLegacyModule implements LegacyModuleManifest
{
    public static function menu(): array
    {
        return [['key' => 'legacy', 'label' => 'Legacy', 'route' => 'legacy.index', 'icon' => 'squares', 'order' => 800]];
    }

    public static function widgets(): array
    {
        return [['key' => 'legacy-widget', 'type' => 'card', 'title' => 'Legacy', 'component' => 'Modules/Legacy/Widgets/Legacy', 'cols' => 1, 'order' => 800]];
    }

    public static function permissions(): array
    {
        return ['legacy.view'];
    }

    public static function manifest(): array
    {
        return ['key' => 'legacy', 'name' => 'Legacy Module', 'description' => 'A legacy module.', 'version' => '0.9.0'];
    }
}
