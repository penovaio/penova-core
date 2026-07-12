<?php

namespace App\Core\Support\Commands;

use App\Core\Settings\Services\SettingsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Interactive first-time setup for a fresh Penova Core project.
 *
 *   php artisan penova:setup            # ask a few questions, then bootstrap
 *   php artisan penova:setup --defaults # accept safe defaults, no prompts
 *
 * Runs automatically after `composer create-project` (see the
 * post-create-project-cmd script). It gathers a handful of choices --
 * interface language, fallback language, timezone, starter profile, database
 * driver, and whether to build the front-end -- writes them to `.env`, refreshes
 * the runtime configuration, then generates the app key, prepares the database,
 * runs the migrations and seeds the Core baseline.
 *
 * The prompts use the framework's built-in question helpers so they behave the
 * same on Windows, macOS and Linux. In a non-interactive environment (CI, no
 * TTY, or --defaults) every prompt is skipped in favour of the safe defaults, so
 * an automated install never blocks; the user can re-run this command in a real
 * terminal at any time to change the choices.
 */
class PenovaSetupCommand extends Command
{
    protected $signature = 'penova:setup
        {--defaults : Skip all prompts and use safe defaults}';

    protected $description = 'Interactive first-time setup for a fresh Penova Core project';

    /** Interface / fallback language options (values written to APP_LOCALE). */
    private const LANGUAGES = ['en', 'fa'];

    /** A short, common timezone shortlist. */
    private const TIMEZONES = [
        'UTC',
        'Asia/Tehran',
        'Asia/Dubai',
        'Europe/London',
        'Europe/Berlin',
        'America/New_York',
    ];

    /** Starter profiles, from lightest to fullest. */
    private const PROFILES = ['minimal', 'standard', 'full'];

    /** Supported database drivers. */
    private const DRIVERS = ['sqlite', 'mysql', 'pgsql'];

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=cyan;options=bold>Penova Core</> - project setup');
        $this->newLine();

        $interactive = $this->input->isInteractive() && ! $this->option('defaults');

        $answers = $interactive ? $this->collectAnswers() : $this->useDefaults();

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

        $this->newLine();
        $this->components->info(sprintf(
            'Your project is ready. Start it with "php artisan serve", then sign in at /%s with %s (password: %s).',
            config('penova.workspace.prefix'),
            config('penova.operator.email'),
            config('penova.operator.password'),
        ));

        if (! $interactive) {
            // Composer runs create-project scripts without an interactive
            // terminal (there is no TTY on Windows), so the questions above were
            // skipped and safe defaults applied. Point the user at the
            // interactive command they can run themselves.
            $this->line('  Defaults were applied without prompts. To choose the interface language,');
            $this->line('  timezone, starter profile or database, run: php artisan penova:setup');
        }

        return self::SUCCESS;
    }

    /**
     * Ask the interactive questions using the portable question helpers.
     *
     * @return array{locale:string,fallback:string,timezone:string,profile:string,driver:string,db:array<string,string>,build:bool}
     */
    private function collectAnswers(): array
    {
        $locale = $this->choice('Interface language (en = English, fa = Persian)', self::LANGUAGES, 0);
        $fallback = $this->choice('Fallback language (en = English, fa = Persian)', self::LANGUAGES, 0);
        $timezone = $this->choice('Timezone', self::TIMEZONES, 0);
        $profile = $this->choice(
            'Starter profile (minimal = Core only, standard = + branding, full = + Store demo)',
            self::PROFILES,
            1,
        );
        $driver = $this->choice('Database driver', self::DRIVERS, 0);

        $db = [];
        if ($driver !== 'sqlite') {
            $db = [
                'DB_HOST' => (string) $this->ask('Database host', '127.0.0.1'),
                'DB_PORT' => (string) $this->ask('Database port', $driver === 'pgsql' ? '5432' : '3306'),
                'DB_DATABASE' => (string) $this->ask('Database name', 'penova'),
                'DB_USERNAME' => (string) $this->ask('Database username', $driver === 'pgsql' ? 'postgres' : 'root'),
                'DB_PASSWORD' => (string) $this->secret('Database password (leave blank if none)', true),
            ];
        }

        $build = $this->confirm('Install and build the front-end now (requires npm)?', true);

        return compact('locale', 'fallback', 'timezone', 'profile', 'driver', 'db', 'build');
    }

    /**
     * Safe defaults for a non-interactive run: English, UTC, standard profile,
     * SQLite, no asset build.
     *
     * @return array{locale:string,fallback:string,timezone:string,profile:string,driver:string,db:array<string,string>,build:bool}
     */
    private function useDefaults(): array
    {
        $this->components->info('Non-interactive run - applying safe defaults (English, UTC, standard profile, SQLite).');
        $this->newLine();

        return [
            'locale' => 'en',
            'fallback' => 'en',
            'timezone' => 'UTC',
            'profile' => 'standard',
            'driver' => 'sqlite',
            'db' => [],
            'build' => true,
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

        $this->line('  Writing configuration to .env...');
        $this->writeEnv($values);

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

    /** Quote a value that contains whitespace or a hash. */
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
            $this->line('  Running migrations...');

            if ($this->callSilent('migrate', ['--force' => true]) !== self::SUCCESS) {
                throw new \RuntimeException('The migration step reported an error.');
            }

            $this->line('  Seeding the Core baseline...');
            $this->callSilent('db:seed', ['--force' => true]);
        } catch (Throwable $e) {
            $this->components->error('Could not set up the database. Check your database settings in .env, then run: php artisan penova:install');
            $this->line('  '.$e->getMessage());

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
        $this->runArtisan(['migrate', '--force'], 'Migrating the Store module...');
        $this->runArtisan(
            ['db:seed', '--class=App\\Modules\\Store\\Database\\Seeders\\StorePermissionsSeeder', '--force'],
            'Seeding Store permissions...',
        );
    }

    /** Install and build the front-end assets, tolerating a missing toolchain. */
    private function buildAssets(): void
    {
        // Run npm through the shell so the right executable is found on every
        // platform (npm.cmd on Windows, npm elsewhere).
        $this->runProcess(Process::fromShellCommandline('npm install', base_path()), 'Installing front-end packages...', 'npm install');
        $this->runProcess(Process::fromShellCommandline('npm run build', base_path()), 'Building front-end assets...', 'npm run build');
    }

    /** Run an artisan subcommand in a fresh process (fresh application boot). */
    private function runArtisan(array $arguments, string $message): void
    {
        $command = array_merge([PHP_BINARY, base_path('artisan')], $arguments);
        $this->runProcess(new Process($command, base_path()), $message, implode(' ', $arguments));
    }

    /** Run an external process; a failure is reported, not fatal. */
    private function runProcess(Process $process, string $message, string $label): void
    {
        $this->line('  '.$message);

        $process->setTimeout(900);

        try {
            $process->run();

            if (! $process->isSuccessful()) {
                $this->components->warn("Could not complete '{$label}' automatically - you can run it by hand later.");
            }
        } catch (Throwable) {
            $this->components->warn("Could not complete '{$label}' automatically - you can run it by hand later.");
        }
    }
}
