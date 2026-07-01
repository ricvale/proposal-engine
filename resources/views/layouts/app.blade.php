<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Proposal Engine' }}</title>
    <style>
        :root {
            --ink: #1a2332;
            --muted: #6b7280;
            --paper: #fafaf7;
            --card: #ffffff;
            --line: #e4e2db;
            --accent: #0f6b5c;
            --accent-soft: #e3f0ed;
            --danger: #a33a2a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font: 16px/1.6 "Segoe UI", system-ui, sans-serif;
        }
        header.site {
            border-bottom: 1px solid var(--line);
            background: var(--card);
        }
        .shell { max-width: 880px; margin: 0 auto; padding: 0 24px; }
        header.site .shell { display: flex; align-items: baseline; gap: 24px; padding-top: 18px; padding-bottom: 18px; }
        .brand { font-family: Georgia, 'Times New Roman', serif; font-size: 20px; font-weight: 700; color: var(--ink); text-decoration: none; }
        .brand em { color: var(--accent); font-style: normal; }
        nav a { color: var(--muted); text-decoration: none; margin-right: 16px; font-size: 14px; }
        nav a:hover, nav a.active { color: var(--accent); }
        main { padding: 32px 0 64px; }
        h1 { font-family: Georgia, 'Times New Roman', serif; font-size: 28px; margin: 0 0 6px; }
        .sub { color: var(--muted); font-size: 14px; margin: 0 0 28px; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 8px; padding: 24px; margin-bottom: 20px; }
        label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
        .hint { font-weight: 400; color: var(--muted); }
        input[type=text], textarea {
            width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 6px;
            font: inherit; font-size: 15px; background: #fff; color: var(--ink); margin-bottom: 18px;
        }
        input[type=text]:focus, textarea:focus { outline: 2px solid var(--accent-soft); border-color: var(--accent); }
        textarea { resize: vertical; }
        .btn {
            display: inline-block; padding: 10px 20px; border-radius: 6px; border: 1px solid var(--accent);
            background: var(--accent); color: #fff; font: inherit; font-size: 15px; font-weight: 600;
            cursor: pointer; text-decoration: none;
        }
        .btn:hover { filter: brightness(1.08); }
        .btn.secondary { background: transparent; color: var(--accent); }
        .btn.danger { background: transparent; border-color: var(--line); color: var(--danger); font-weight: 400; font-size: 13px; padding: 6px 12px; }
        .flash { background: var(--accent-soft); border: 1px solid var(--accent); color: var(--accent); border-radius: 6px; padding: 10px 16px; margin-bottom: 24px; font-size: 14px; }
        .error { color: var(--danger); font-size: 13px; margin: -12px 0 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); padding: 8px 12px; border-bottom: 2px solid var(--line); }
        td { padding: 12px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        td a.title-link { color: var(--ink); font-weight: 600; text-decoration: none; }
        td a.title-link:hover { color: var(--accent); }
        .pill { display: inline-block; font-size: 12px; padding: 2px 10px; border-radius: 99px; border: 1px solid var(--line); color: var(--muted); }
        .pill.final { border-color: var(--accent); color: var(--accent); background: var(--accent-soft); }
        .row-actions { display: flex; gap: 8px; align-items: center; justify-content: flex-end; }
        .empty { text-align: center; color: var(--muted); padding: 48px 0; }
        .generating { display: none; margin-top: 12px; color: var(--muted); font-size: 14px; }
    </style>
    @livewireStyles
</head>
<body>
    <header class="site">
        <div class="shell">
            <a class="brand" href="{{ route('proposals.index') }}">Proposal<em>Engine</em></a>
            <nav>
                <a href="{{ route('proposals.index') }}" @class(['active' => request()->routeIs('proposals.*')])>Proposals</a>
                <a href="{{ route('profile.edit') }}" @class(['active' => request()->routeIs('profile.*')])>Your profile</a>
            </nav>
        </div>
    </header>
    <main>
        <div class="shell">
            @if (session('status'))
                <div class="flash">{{ session('status') }}</div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </main>
    @livewireScripts
</body>
</html>
