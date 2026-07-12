<?php

namespace App\Core\Support\Commands;

use App\Core\Settings\Services\SettingsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;

/**
 * Interactive first-time setup for a fresh Penova Core project.
 *
 *   php artisan penova:setup            # ask a few questions, then bootstrap
 *   php artisan penova:setup --defaults # accept safe defaults, no prompts
 *
 * Runs automatically after `composer create-project` (see the
 * post-create-project-cmd script). It gathers a handful of choices —
 * interface language, fallback language, timezone, starter profile, database
 * driver, and whether to build the front-end — writes them to `.env`, refreshes
 * the runtime configuration, then generates the app key, prepares the database,
 * runs the migrations and seeds the Core baseline.
 *
 * In a non-interactive environment (CI, no TTY, or --no-interaction) it skips
 * every prompt and uses the safe defaults, so an automated install never blocks.
 */
class PenovaSetupCommand extends Command
{
    protected $signature = 'penova:setup
        {--defaults : Skip all prompts and use safe defaults}';

    protected $description = 'Interactive first-time setup for a fresh Penova Core project';

    /** Interface / fallback language choices. */
    private const LANGUAGES = [
        'en' => 'English',
        'fa' => 'فارسی (Persian)',
    ];

    /** A short, common timezone shortlist; any IANA name is accepted via prompt. */
    private const TIMEZONES = [
        'UTC' => 'UTC',
        'Asia/Tehran' => 'Asia/Tehran',
        'Asia/Dubai' => 'Asia/Dubai',
        'Europe/London' => 'Europe/London',
        'Europe/Berlin' => 'Europe/Berlin',
        'America/New_York' => 'America/New_York',
    ];

    public function handle(): int
    {
        intro('Penova Core — project setup');

        $interactive = $this->input->isInteractive() && ! $this->option('defaults');

        if (! $interactive) {
            note('Non-interactive run — applying safe defaults.');
        }

        $answers = $interactive ? $this->collectAnswers() : $this->defaults();

        $this->applyEnvironment($answers);
        $this->prepareApplicationKey();
        $this->prepareDatabaseFile($answers['driver']);

        if (! $this->migrateAndSeed()) {
            return self::FAILURE;
        }

        $this->applyProfile($answers['profile']);

        if ($answers['build']) {
            $this->buildAssets();
        }

        outro(sprintf(
            'Setup complete. Run `php artisan serve`, then sign in at /%s with %s (password: %s).',
            config('penova.workspace.prefix'),
            config('penova.operator.email'),
            config('penova.operator.password'),
        ));

        return self::SUCCESS;
    }

    /**
     * Ask the interactive questions.
     *
     * @return array{locale:string,fallback:string,timezone:string,profile:string,driver:string,db:array<string,string>,build:bool}
     */
    private function collectAnswers(): array
    {
        $locale = select('Interface language', self::LANGUAGES, default: 'en');
        $fallback = select('Fallback language', self::LANGUAGES, default: 'en');
        $timezone = select('Timezone', self::TIMEZONES, default: 'UTC');

        $profile = select('Starter profile', [
            'minimal' => 'Minimal — Core, migrated and seeded (Operator account)',
            'standard' => 'Standard — Minimal + default branding settings',
            'full' => 'Full — Standard + the Store reference module (demo)',
        ], default: 'standard');

        $driver = select('Database driver', [
            'sqlite' => 'SQLite — zero configuration, great for getting started',
            'mysql' => 'MySQL / MariaDB',
            'pgsql' => 'PostgreSQL',
        ], default: 'sqlite');

        $db = [];
        if ($driver !== 'sqlite') {
            $db = [
                'DB_HOST' => text('Database host', default: '127.0.0.1', required: true),
                'DB_PORT' => text('Database port', default: $driver === 'pgsql' ? '5432' : '3306', required: true),
                'DB_DATABASE' => text('Database name', default: 'penova', required: true),
                'DB_USERNAME' => text('Database username', default: $driver === 'pgsql' ? 'postgres' : 'root', required: true),
                'DB_PASSWORD' => text('Database password (leave blank if none)'),
            ];
        }

        $build = confirm('Install and build the front-end now (npm)?', default: false);

        return compact('locale', 'fallback', 'timezone', 'profile', 'driver', 'db', 'build');
    }

    /**
     * Safe defaults for a non-interactive run: English, UTC, standard profile,
     * SQLite, no asset build.
     *
     * @return array{locale:string,fallback:string,timezone:string,profile:string,driver:string,db:array<string,string>,build:bool}
     */
    private function defaults(): array
    {
        return [
            'locale' => 'en',
            'fallback' => 'en',
            'timezone' => 'UTC',
            'profile' => 'standard',
            'driver' => 'sqlite',
            'db' => [],
            'build' => false,
        ];
    }

    /**
     * Write the chosen values to `.env` and refresh the already-booted runtime
     * configuration so the rest of this command uses them.
     *
     * @param  array{locale:string,fallback:string,timezone:string,profile:string,driver:string,db:array<string,string>,build:bool}  $answers
     */
    private function applyEnvironment(array $answers): void
    {
        $values = array_merge([
            'APP_LOCALE' => $answers['locale'],
            'APP_FALLBACK_LOCALE' => $answers['fallback'],
            'APP_FAKER_LOCALE' => $answers['locale'] === 'fa' ? 'fa_IR' : 'en_US',
            'APP_TIMEZONE' => $answers['timezone'],
            'DB_CONNECTION' => $answers['driver'],
        ], $answers['db']);

        spin(fn () => $this->writeEnv($values), 'Writing configuration to .env…');

        // The framework booted with the previous .env, so env()/config() are
        // frozen. Refresh the values this command still relies on (locale and
        // the database connection) so migrate/seed use the new choices.
        config([
            'app.locale' => $answers['locale'],
            'app.fallback_locale' => $answers['fallback'],
            'app.timezone' => $answers['timezone'],
            'database.default' => $answers['driver'],
        ]);

        if ($answers['driver'] !== 'sqlite') {
            $c = "database.connections.{$answers['driver']}";
            config([
                "{$c}.host" => $answers['db']['DB_HOST'],
                "{$c}.port" => $answers['db']['DB_PORT'],
                "{$c}.database" => $answers['db']['DB_DATABASE'],
                "{$c}.username" => $answers['db']['DB_USERNAME'],
                "{$c}.password" => $answers['db']['DB_PASSWORD'],
            ]);
        }
    }

    /**
     * Upsert KEY=value pairs into `.env`, creating it from `.env.example` first
     * if it is missing. An existing line (even commented out) is replaced in
     * place; otherwise the pair is appended.
     *
     * @param  array<string,string>  $values
     */
    private function writeEnv(array $values): void
    {
        $path = base_path('.env');

        if (! File::exists($path)) {
            File::copy(base_path('.env.example'), $path);
        }

        $contents = File::get($path);

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->escapeEnvValue($value);
            $pattern = '/^#?\s*'.preg_quote($key, '/').'=.*$/m';

            $contents = preg_match($pattern, $contents)
                ? preg_replace($pattern, $line, $contents, 1)
                : rtrim($contents, "\n")."\n".$line."\n";
        }

        File::put($path, $contents);
    }

    /** Quote a value that contains whitespace or a leading/trailing hash. */
    private function escapeEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return preg_match('/\s|#/', $value) ? '"'.str_replace('"', '\"', $value).'"' : $value;
    }

    /** Generate APP_KEY only when one is not set yet. */
    private function prepareApplicationKey(): void
    {
        if (blank(config('app.key'))) {
            $this->callSilent('key:generate', ['--force' => true]);
        }
    }

    /** Create the SQLite database file when SQLite is the chosen driver. */
    private function prepareDatabaseFile(string $driver): void
    {
        if ($driver !== 'sqlite') {
            return;
        }

        $path = database_path('database.sqlite');

        if (! File::exists($path)) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, '');
        }
    }

    /** Run the migrations and seed the Core baseline, reporting failures cleanly. */
    private function migrateAndSeed(): bool
    {
        try {
            $migrated = spin(fn () => $this->callSilent('migrate', ['--force' => true]), 'Running migrations…');

            if ($migrated !== self::SUCCESS) {
                throw new \RuntimeException('The migration step reported an error.');
            }

            spin(fn () => $this->callSilent('db:seed', ['--force' => true]), 'Seeding the Core baseline…');
        } catch (Throwable $e) {
            note('Could not set up the database. Check your database settings in .env, then run: php artisan penova:install');
            $this->components->error($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Apply the starter profile on top of the migrated + seeded baseline.
     *
     * - minimal:  nothing extra (baseline only).
     * - standard: seed default branding so the White Label screen starts filled.
     * - full:     standard, plus enable the Store reference module (demo).
     */
    private function applyProfile(string $profile): void
    {
        if ($profile === 'minimal') {
            return;
        }

        $this->seedDefaultBranding();

        if ($profile === 'full') {
            $this->enableStoreModule();
        }
    }

    /** Persist the config branding defaults as runtime settings. */
    private function seedDefaultBranding(): void
    {
        try {
            app(SettingsManager::class)->set('branding', config('penova.branding'), 'branding');
        } catch (Throwable) {
            // Branding is optional; a failure here must not block setup.
        }
    }

    /**
     * Enable the in-repo Store reference module: add its provider to
     * config/penova.php, then migrate and seed it in a fresh process so the
     * provider boots and its migrations register.
     */
    private function enableStoreModule(): void
    {
        $provider = 'App\\Modules\\Store\\StoreServiceProvider::class';
        $configPath = base_path('config/penova.php');
        $contents = File::get($configPath);

        // Already enabled (uncommented)? Then just make sure it is migrated/seeded.
        $enabled = (bool) preg_match('/^\s*'.preg_quote($provider, '/').'/m', $contents);

        if (! $enabled) {
            $contents = str_replace(
                "    'modules' => [\n",
                "    'modules' => [\n        {$provider},\n",
                $contents,
            );
            File::put($configPath, $contents);
        }

        // A separate process boots with Store enabled, so its migrations
        // register and only they run (the Core tables already exist).
        $this->runArtisan(['migrate', '--force'], 'Migrating the Store module…');
        $this->runArtisan(
            ['db:seed', '--class=App\\Modules\\Store\\Database\\Seeders\\StorePermissionsSeeder', '--force'],
            'Seeding Store permissions…',
        );
    }

    /** Install and build the front-end assets, tolerating a missing toolchain. */
    private function buildAssets(): void
    {
        $this->runProcess(['npm', 'install'], 'Installing front-end packages…');
        $this->runProcess(['npm', 'run', 'build'], 'Building front-end assets…');
    }

    /** Run an artisan subcommand in a fresh process (fresh application boot). */
    private function runArtisan(array $arguments, string $message): void
    {
        $this->runProcess(array_merge([PHP_BINARY, base_path('artisan')], $arguments), $message);
    }

    /** Run an external process under a spinner; a failure is reported, not fatal. */
    private function runProcess(array $command, string $message): void
    {
        $process = new Process($command, base_path());
        $process->setTimeout(600);

        try {
            spin(fn () => $process->run(), $message);

            if (! $process->isSuccessful()) {
                note('Step could not be completed automatically: '.implode(' ', $command));
            }
        } catch (Throwable) {
            note('Step could not be completed automatically: '.implode(' ', $command));
        }
    }
}
