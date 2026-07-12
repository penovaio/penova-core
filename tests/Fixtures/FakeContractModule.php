<?php

namespace Tests\Fixtures;

use App\Core\Support\Manifest;
use App\Core\Support\PenovaModule;

/**
 * A conforming Module whose Manifest contributions compose into the Platform.
 * Used to defend the Core<->Module seam (T2) by presence (key / contains),
 * never by index, order, or carrier internals (D-023; 16).
 */
class FakeContractModule implements PenovaModule
{
    public static function manifest(): Manifest
    {
        return Manifest::for(
            key: 'fake',
            name: 'Fake Module',
            description: 'A conforming module used to defend the contract.',
            version: '1.2.3',
        )
            ->menu([['key' => 'fake', 'label' => 'Fake', 'route' => 'fake.index', 'icon' => 'squares', 'order' => 700, 'permission' => 'fake.view']])
            ->widgets([['key' => 'fake-widget', 'type' => 'card', 'title' => 'Fake', 'cols' => 1, 'order' => 700, 'area' => 'fake', 'permission' => 'fake.view']])
            ->permissions(['fake.view', 'fake.manage']);
    }
}
