<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class SystemStatusController extends Controller
{
    public function serverStatus()
    {
        $database = [
            'connected' => false,
            'driver' => config('database.default'),
            'host' => config('database.connections.'.config('database.default').'.host'),
            'database' => config('database.connections.'.config('database.default').'.database'),
            'version' => null,
            'table_count' => null,
        ];

        try {
            DB::connection()->getPdo();
            $database['connected'] = true;

            $driver = config('database.default');
            if ($driver === 'mysql') {
                $row = DB::selectOne('select version() as version');
                $database['version'] = $row->version ?? null;
                $database['table_count'] = count(DB::select('show tables'));
            } elseif ($driver === 'pgsql') {
                $row = DB::selectOne('select version() as version');
                $database['version'] = $row->version ?? null;
            } elseif ($driver === 'sqlite') {
                $database['version'] = DB::selectOne('select sqlite_version() as version')->version ?? null;
            }
        } catch (Throwable $exception) {
            $database['error'] = $exception->getMessage();
        }

        $queue = ['pending' => null, 'failed' => null];
        try {
            $queue['pending'] = DB::table('jobs')->count();
            $queue['failed'] = DB::table('failed_jobs')->count();
        } catch (Throwable) {
            // Queue tables may not exist.
        }

        $diskTotal = @disk_total_space(base_path());
        $diskFree = @disk_free_space(base_path());

        return view('admin.system.server-status', [
            'runtime' => [
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? php_uname('s'),
                'os' => php_uname('s').' '.php_uname('r'),
                'environment' => app()->environment(),
                'debug' => config('app.debug') ? 'Enabled' : 'Disabled',
                'timezone' => config('app.timezone'),
                'url' => config('app.url'),
            ],
            'database' => $database,
            'storage' => [
                'disk_total' => $diskTotal,
                'disk_free' => $diskFree,
                'disk_used' => ($diskTotal && $diskFree) ? ($diskTotal - $diskFree) : null,
                'storage_writable' => is_writable(storage_path()),
                'cache_writable' => is_writable(base_path('bootstrap/cache')),
            ],
            'drivers' => [
                'cache' => config('cache.default'),
                'session' => config('session.driver'),
                'queue' => config('queue.default'),
                'filesystem' => config('filesystems.default'),
            ],
            'queue' => $queue,
            'limits' => [
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'max_input_vars' => ini_get('max_input_vars'),
            ],
            'extensions' => $this->extensionStatus(),
        ]);
    }

    public function update()
    {
        $pendingMigrations = [];
        $ranMigrations = null;

        try {
            $files = collect(File::glob(database_path('migrations/*.php')))
                ->map(fn ($path) => basename($path, '.php'))
                ->values();

            $ran = DB::table('migrations')->pluck('migration')->all();
            $ranMigrations = count($ran);
            $pendingMigrations = $files->diff($ran)->values()->all();
        } catch (Throwable) {
            // Database unavailable — skip migration checks.
        }

        return view('admin.system.update', [
            'current' => [
                'app_name' => config('app.name'),
                'app_version' => config('app.version', '1.0.0'),
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'environment' => app()->environment(),
            ],
            'git' => $this->gitInfo(),
            'migrationCount' => $ranMigrations,
            'pendingMigrations' => $pendingMigrations,
            'maintenance' => [
                'config_cached' => app()->configurationIsCached(),
                'routes_cached' => app()->routesAreCached(),
                'view_cache_files' => count(File::glob(storage_path('framework/views/*.php'))),
            ],
        ]);
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');

        return back()->with('success', 'Application cache cleared.');
    }

    public function clearView()
    {
        Artisan::call('view:clear');

        return back()->with('success', 'Compiled views cleared.');
    }

    public function clearConfig()
    {
        Artisan::call('config:clear');

        return back()->with('success', 'Configuration cache cleared.');
    }

    public function clearRoute()
    {
        Artisan::call('route:clear');

        return back()->with('success', 'Route cache cleared.');
    }

    public function clearAll()
    {
        Artisan::call('optimize:clear');

        return back()->with('success', 'All caches cleared.');
    }

    private function gitInfo(): array
    {
        $info = ['branch' => null, 'commit' => null, 'commit_date' => null, 'available' => false];

        try {
            $headPath = base_path('.git/HEAD');
            if (! is_file($headPath)) {
                return $info;
            }

            $head = trim((string) file_get_contents($headPath));

            if (str_starts_with($head, 'ref: ')) {
                $ref = trim(substr($head, 5));
                $info['branch'] = basename($ref);
                $refPath = base_path('.git/'.$ref);
                if (is_file($refPath)) {
                    $info['commit'] = trim((string) file_get_contents($refPath));
                }
            } else {
                $info['commit'] = $head;
            }

            if ($info['commit']) {
                $info['short_commit'] = substr($info['commit'], 0, 7);
            }

            $info['available'] = true;
        } catch (Throwable) {
            // Git metadata not readable.
        }

        return $info;
    }

    private function extensionStatus(): array
    {
        $required = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl', 'gd', 'zip'];
        $status = [];

        foreach ($required as $extension) {
            $status[$extension] = extension_loaded($extension);
        }

        return $status;
    }
}
