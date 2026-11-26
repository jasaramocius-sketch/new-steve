<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <style>
        :root { --bg:#f5f7fb; --card:#fff; --text:#1f2937; --muted:#6b7280; --danger:#dc2626; --danger-bg:#fef2f2; --border:#e5e7eb; --dark:#111827; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; background:var(--bg); color:var(--text); }
        .wrap { max-width: 1100px; margin: 32px auto; padding: 0 16px; }
        .card { background:var(--card); border:1px solid var(--border); border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.04); overflow:hidden; }
        .head { padding:20px 24px; border-bottom:1px solid var(--border); background:var(--danger-bg); }
        .head h1 { margin:0; font-size:22px; color:var(--danger); display:flex; gap:10px; align-items:center; }
        .head p { margin:8px 0 0; color:#7f1d1d; font-size:14px; }
        .body { padding:24px; }
        .meta { display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom:20px; }
        .meta-item { padding:12px 14px; background:#f9fafb; border:1px solid var(--border); border-radius:8px; }
        .meta-item .k { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); margin-bottom:4px; }
        .meta-item .v { font-size:13px; word-break:break-word; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
        .block { margin-top:18px; }
        .block h2 { font-size:13px; text-transform:uppercase; letter-spacing:.04em; color:var(--muted); margin:0 0 8px; }
        pre { margin:0; padding:16px; background:var(--dark); color:#e5e7eb; border-radius:8px; overflow:auto; font-size:12px; line-height:1.6; white-space:pre-wrap; word-break:break-word; }
        .msg { padding:14px 16px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; border-radius:8px; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size:13px; white-space:pre-wrap; word-break:break-word; }
        .actions { margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; }
        .btn { display:inline-block; padding:10px 14px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:600; }
        .btn-primary { background:#2563eb; color:#fff; }
        .btn-light { background:#fff; color:var(--text); border:1px solid var(--border); }
        .generic { padding:40px 24px; text-align:center; }
        .generic h1 { margin:0 0 8px; font-size:28px; color:var(--danger); }
        .generic p { color:var(--muted); margin:0 0 20px; }
    </style>
</head>
<body>
@php
    try { $user = auth()->user(); } catch (\Throwable $t) { $user = null; }
    $isAdmin = $user && in_array($user->role ?? null, ['master_admin', 'admin', 'staff'], true);
    $e = $exception ?? null;
    try { $fullUrl = request()->fullUrl(); } catch (\Throwable $t) { $fullUrl = ''; }
    try { $method = request()->method(); } catch (\Throwable $t) { $method = ''; }
    try { $routeName = optional(request()->route())->getName() ?: (optional(request()->route())->uri() ?: '—'); } catch (\Throwable $t) { $routeName = '—'; }
@endphp
<div class="wrap">
    <div class="card">
        @if($isAdmin && $e)
            <div class="head">
                <h1>500 Server Error</h1>
                <p>An error occurred on this page. Details are shown below.</p>
            </div>
            <div class="body">
                <div class="meta">
                    <div class="meta-item"><div class="k">URL</div><div class="v">{{ $fullUrl }}</div></div>
                    <div class="meta-item"><div class="k">Method</div><div class="v">{{ $method }}</div></div>
                    <div class="meta-item"><div class="k">Route</div><div class="v">{{ $routeName }}</div></div>
                    <div class="meta-item"><div class="k">Time</div><div class="v">{{ now()->toDateTimeString() }}</div></div>
                    <div class="meta-item"><div class="k">User</div><div class="v">{{ $user->email ?? ('ID:'.$user->id) }}</div></div>
                    <div class="meta-item"><div class="k">Exception</div><div class="v">{{ get_class($e) }}</div></div>
                </div>

                <div class="block">
                    <h2>Message</h2>
                    <div class="msg">{{ $e->getMessage() ?: '(no message)' }}</div>
                </div>

                <div class="block">
                    <h2>Location</h2>
                    <pre>{{ $e->getFile() }}:{{ $e->getLine() }}</pre>
                </div>

                <div class="block">
                    <h2>Stack Trace (top 20)</h2>
                    <pre>{{ collect($e->getTrace())->take(20)->map(function($t, $i) {
    $file = $t['file'] ?? '[internal]';
    $line = isset($t['line']) ? ':'.$t['line'] : '';
    $call = trim(($t['class'] ?? '').($t['type'] ?? '').($t['function'] ?? ''));
    return '#'.$i.' '.$file.$line.($call ? '  '.$call.'()' : '');
})->implode("\n") }}</pre>
                </div>

                <div class="actions">
                    <a href="{{ url()->previous() }}" class="btn btn-light">Go Back</a>
                    <a href="{{ $fullUrl ?: url()->current() }}" class="btn btn-primary">Retry</a>
                </div>
            </div>
        @else
            <div class="generic">
                <h1>500 - Server Error</h1>
                <p>Something went wrong. Please try again later.</p>
                <div class="actions" style="justify-content:center;">
                    <a href="{{ url('/') }}" class="btn btn-primary">Home</a>
                    <a href="{{ url()->previous() }}" class="btn btn-light">Go Back</a>
                </div>
            </div>
        @endif
    </div>
</div>
</body>
</html>
