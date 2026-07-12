<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
| Binds the Laravel TestCase to every Pest-style test in these folders.
| Class-based PHPUnit tests (AuthTest, …) keep working unchanged — Pest
| runs both styles side by side.
*/

// Core lane: everything under Feature/Core runs with NO business module
// enabled (config penova.modules is empty - D-026), the honest post-decouple
// default. Class-based tests (AuthTest, ExampleTest) declare their own base.
uses(TestCase::class)->in('Feature/Core');
