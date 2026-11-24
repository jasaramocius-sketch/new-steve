<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>We'll be right back</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f6f7f9;
            color: #1f2937;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        }
        .card {
            max-width: 520px;
            margin: 24px;
            padding: 40px 32px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .08);
            text-align: center;
        }
        .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fff4ed;
            color: #e62e04;
            font-size: 26px;
        }
        h1 { margin: 0 0 10px; font-size: 1.6rem; }
        p { margin: 0; color: #6b7280; line-height: 1.6; }
        a { display: inline-block; margin-top: 22px; color: #e62e04; font-weight: 600; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon" aria-hidden="true">&#9881;</div>
        <h1>We'll be right back</h1>
        <p>The store is temporarily unavailable while we make some updates. Please try again shortly.</p>
        <a href="{{ url('/') }}">Refresh page</a>
    </div>
</body>
</html>
