<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'LinkedIn Daily Posts')</title>
    <style>
        :root { color-scheme: light; --ink:#182230; --muted:#667085; --line:#d0d5dd; --soft:#f4f7fb; --brand:#0a66c2; --ok:#067647; --warn:#b54708; --bad:#b42318; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif; color:var(--ink); background:#f7f9fc; }
        a { color:var(--brand); text-decoration:none; }
        .shell { display:grid; grid-template-columns:250px 1fr; min-height:100vh; }
        .sidebar { background:#101828; color:white; padding:24px 18px; }
        .brand { font-size:20px; font-weight:800; margin-bottom:28px; }
        .nav a, .logout { display:block; width:100%; color:#d0d5dd; padding:10px 12px; border-radius:8px; margin-bottom:4px; background:transparent; border:0; text-align:left; font:inherit; cursor:pointer; }
        .nav a:hover, .logout:hover { background:#1d2939; color:white; }
        .main { padding:28px; }
        .topbar { display:flex; justify-content:space-between; gap:16px; align-items:center; margin-bottom:24px; }
        h1 { font-size:28px; margin:0; letter-spacing:0; }
        h2 { font-size:18px; margin:0 0 14px; }
        .grid { display:grid; gap:16px; }
        .stats { grid-template-columns:repeat(4,minmax(0,1fr)); }
        .two { grid-template-columns:1fr 1fr; }
        .card { background:white; border:1px solid #eaecf0; border-radius:8px; padding:18px; box-shadow:0 1px 2px rgba(16,24,40,.04); }
        .stat { font-size:30px; font-weight:800; margin-top:6px; }
        .muted { color:var(--muted); }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:38px; border:1px solid var(--line); border-radius:8px; padding:8px 12px; background:white; color:var(--ink); font-weight:650; cursor:pointer; }
        .btn.primary { background:var(--brand); border-color:var(--brand); color:white; }
        .btn.danger { color:var(--bad); }
        .table { width:100%; border-collapse:collapse; background:white; border:1px solid #eaecf0; border-radius:8px; overflow:hidden; }
        .table th, .table td { padding:12px; border-bottom:1px solid #eaecf0; text-align:left; vertical-align:top; }
        .table th { font-size:12px; color:var(--muted); text-transform:uppercase; background:#f9fafb; }
        .badge { display:inline-flex; border-radius:999px; padding:3px 9px; font-size:12px; font-weight:750; background:#eef4ff; color:#1849a9; }
        .posted { background:#ecfdf3; color:var(--ok); } .failed { background:#fef3f2; color:var(--bad); } .pending { background:#fffaeb; color:var(--warn); } .cancelled { background:#f2f4f7; color:#344054; }
        input, textarea, select { width:100%; border:1px solid var(--line); border-radius:8px; padding:10px 12px; font:inherit; background:white; }
        textarea { min-height:170px; resize:vertical; }
        label { display:block; font-weight:700; margin-bottom:6px; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .full { grid-column:1 / -1; }
        .alert { border-radius:8px; padding:12px 14px; margin-bottom:16px; background:#ecfdf3; color:var(--ok); }
        .errors { background:#fef3f2; color:var(--bad); }
        .filters { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)) auto; gap:10px; align-items:end; margin-bottom:16px; }
        .calendar { display:grid; grid-template-columns:repeat(7,1fr); border:1px solid #eaecf0; background:white; border-radius:8px; overflow:hidden; }
        .day { min-height:118px; padding:10px; border-right:1px solid #eaecf0; border-bottom:1px solid #eaecf0; }
        .day:nth-child(7n) { border-right:0; }
        .auth { min-height:100vh; display:grid; place-items:center; padding:20px; }
        .auth .card { width:min(460px,100%); }
        @media (max-width: 900px) { .shell { grid-template-columns:1fr; } .sidebar { position:static; } .stats,.two,.form-grid,.filters { grid-template-columns:1fr; } .calendar { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@auth
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">LinkedIn Posts</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('linkedin.account.edit') }}">LinkedIn Account</a>
                <a href="{{ route('posts.index') }}">Posts</a>
                <a href="{{ route('posts.create') }}">Create Post</a>
                <a href="{{ route('posts.generate') }}">Generate Posts</a>
                <a href="{{ route('posts.calendar') }}">Calendar</a>
                <a href="{{ route('posts.failed') }}">Failed Posts</a>
                <a href="{{ route('logs.index') }}">Logs</a>
                <form method="post" action="{{ route('logout') }}">@csrf <button class="logout">Logout</button></form>
            </nav>
        </aside>
        <main class="main">
            <div class="topbar">
                <h1>@yield('heading', 'Dashboard')</h1>
                <span class="muted">{{ auth()->user()->name }} · {{ auth()->user()->timezone }}</span>
            </div>
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
@else
    <main class="auth">
        @include('partials.flash')
        @yield('content')
    </main>
@endauth
</body>
</html>
