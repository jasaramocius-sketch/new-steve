<?php

namespace App\Console\Commands;

use App\Support\ErrorDebugger;
use Illuminate\Console\Command;

class DebugLastError extends Command
{
    protected $signature = 'debug:last-error
                            {--limit=10 : Number of recent entries to show (max 50)}
                            {--json : Print raw JSON instead of a human-readable report}';

    protected $description = 'Show the most recent captured 500-level errors and their root cause';

    public function handle(): int
    {
        $entries = ErrorDebugger::latest((int) $this->option('limit'));

        if ($this->option('json')) {
            $this->line(json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        if ($entries === []) {
            $this->warn('No captured 500 errors yet. The log will be created at: '.ErrorDebugger::path());

            return self::SUCCESS;
        }

        foreach ($entries as $index => $entry) {
            $this->line('');
            $this->line(sprintf('<info>#%d  %s</info>', $index + 1, $entry['id'] ?? 'unknown'));
            $this->line('  Time     : '.($entry['time'] ?? '-'));
            $this->line('  Location : '.trim(($entry['method'] ?? '').' '.($entry['path'] ?? '')));
            $this->line('  Route    : '.($entry['route'] ?? $entry['route_uri'] ?? '-'));
            $this->line('  User     : '.($entry['user_id'] ?? 'guest'));
            $this->line('  Exception: '.($entry['exception'] ?? '-'));
            $this->line('  Message  : <error>'.($entry['message'] ?? '-').'</error>');
            $this->line('  File     : '.($entry['file'] ?? '-').':'.($entry['line'] ?? '-'));

            if (! empty($entry['frames']) && is_array($entry['frames'])) {
                $this->line('  Trace (first '.count($entry['frames']).' frames):');
                foreach ($entry['frames'] as $frame) {
                    $call = $frame['call'] ? ' -> '.$frame['call'] : '';
                    $this->line('    - '.($frame['file'] ?? '-').':'.($frame['line'] ?? '-').$call);
                }
            }

            $this->line('  PHP      : '.($entry['php'] ?? '-').'  Memory peak: '.($entry['memory_peak_mb'] ?? '-').' MB');
        }

        $this->line('');
        $this->line('Full log: '.ErrorDebugger::path());

        return self::SUCCESS;
    }
}
