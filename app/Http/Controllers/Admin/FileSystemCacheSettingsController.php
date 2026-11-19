<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Throwable;

class FileSystemCacheSettingsController extends Controller
{
    private const FILESYSTEM_DRIVERS = ['local', 'public', 's3'];
    private const CACHE_DRIVERS = ['file', 'database', 'redis', 'array'];
    private const SESSION_DRIVERS = ['file', 'cookie', 'database', 'redis', 'array'];

    public function index()
    {
        $settings = Setting::getAllAsArray();

        return view('admin.settings.file-system-cache', [
            'filesystemDriver' => $this->resolveDriver($settings['filesystem_driver'] ?? config('filesystems.default'), self::FILESYSTEM_DRIVERS, 'local'),
            'awsAccessKeyId' => $settings['aws_access_key_id'] ?? config('filesystems.disks.s3.key', ''),
            'awsSecretConfigured' => ! empty($settings['aws_secret_access_key']) || ! empty(config('filesystems.disks.s3.secret')),
            'awsDefaultRegion' => $settings['aws_default_region'] ?? config('filesystems.disks.s3.region', ''),
            'awsBucket' => $settings['aws_bucket'] ?? config('filesystems.disks.s3.bucket', ''),
            'awsUrl' => $settings['aws_url'] ?? config('filesystems.disks.s3.url', ''),
            'cacheDriver' => $this->resolveDriver($settings['cache_driver'] ?? config('cache.default'), self::CACHE_DRIVERS, 'file'),
            'sessionDriver' => $this->resolveDriver($settings['session_driver'] ?? config('session.driver'), self::SESSION_DRIVERS, 'file'),
            'redisHost' => $settings['redis_host'] ?? config('database.redis.default.host', '127.0.0.1'),
            'redisPasswordConfigured' => ! empty($settings['redis_password']) || ! empty(config('database.redis.default.password')),
            'redisPort' => (string) ($settings['redis_port'] ?? config('database.redis.default.port', 6379)),
        ]);
    }

    public function updateFilesystem(Request $request)
    {
        $validated = $request->validate([
            'filesystem_driver' => ['required', Rule::in(self::FILESYSTEM_DRIVERS)],
            'aws_access_key_id' => ['required_if:filesystem_driver,s3', 'nullable', 'string', 'max:255'],
            'aws_secret_access_key' => ['nullable', 'string', 'max:4096'],
            'aws_default_region' => ['required_if:filesystem_driver,s3', 'nullable', 'string', 'max:100'],
            'aws_bucket' => ['required_if:filesystem_driver,s3', 'nullable', 'string', 'max:255'],
            'aws_url' => ['nullable', 'string', 'max:500'],
        ]);

        $awsSecretConfigured = $request->filled('aws_secret_access_key')
            || ! empty(Setting::get('aws_secret_access_key'))
            || ! empty(config('filesystems.disks.s3.secret'));

        if ($validated['filesystem_driver'] === 's3' && ! $awsSecretConfigured) {
            return back()->withErrors(['aws_secret_access_key' => 'Enter AWS secret access key to use S3.'])->withInput();
        }

        Setting::set('filesystem_driver', $validated['filesystem_driver']);
        Setting::set('aws_access_key_id', (string) ($validated['aws_access_key_id'] ?? ''));
        Setting::set('aws_default_region', (string) ($validated['aws_default_region'] ?? ''));
        Setting::set('aws_bucket', (string) ($validated['aws_bucket'] ?? ''));
        Setting::set('aws_url', (string) ($validated['aws_url'] ?? ''));

        if ($request->filled('aws_secret_access_key')) {
            Setting::set('aws_secret_access_key', Crypt::encryptString($validated['aws_secret_access_key']));
        }

        return redirect()->route('admin.settings.file-system-cache')->with('success', 'File system settings updated.');
    }

    public function updateCache(Request $request)
    {
        $validated = $request->validate([
            'cache_driver' => ['required', Rule::in(self::CACHE_DRIVERS)],
            'session_driver' => ['required', Rule::in(self::SESSION_DRIVERS)],
            'redis_host' => ['nullable', 'string', 'max:255'],
            'redis_password' => ['nullable', 'string', 'max:4096'],
            'redis_port' => ['nullable', 'integer', 'between:1,65535'],
        ]);

        $needsRedis = in_array($validated['cache_driver'], ['redis'], true) || in_array($validated['session_driver'], ['redis'], true);
        if ($needsRedis && empty($validated['redis_host']) && empty(Setting::get('redis_host')) && empty(config('database.redis.default.host'))) {
            return back()->withErrors(['redis_host' => 'Redis host is required when using Redis driver.'])->withInput();
        }

        Setting::set('cache_driver', $validated['cache_driver']);
        Setting::set('session_driver', $validated['session_driver']);
        Setting::set('redis_host', (string) ($validated['redis_host'] ?? config('database.redis.default.host', '127.0.0.1')));
        Setting::set('redis_port', (string) ($validated['redis_port'] ?? config('database.redis.default.port', 6379)));

        if ($request->filled('redis_password')) {
            Setting::set('redis_password', Crypt::encryptString($validated['redis_password']));
        }

        return redirect()->route('admin.settings.file-system-cache')
            ->with('success', 'Cache and session settings updated. If you changed drivers, clear config/cache and restart workers as needed.');
    }

    public static function decryptSecret(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }

    private function resolveDriver($value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
