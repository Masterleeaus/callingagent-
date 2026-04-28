<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Chatbot Overlay' }}</title>
<style>
body{font-family:Arial,sans-serif;margin:0;background:#f4f6fb;color:#1f2937}
.wrap{max-width:1200px;margin:0 auto;padding:24px}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px}
.topbar h1{margin:0;font-size:28px}.topbar .muted{font-size:14px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px;margin-bottom:16px;box-shadow:0 2px 10px rgba(0,0,0,.03)}
.kpi{font-size:28px;font-weight:700;margin-top:8px}.label{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#6b7280}
.muted{color:#6b7280}.actions{display:flex;gap:10px;flex-wrap:wrap}.btn{display:inline-block;background:#111827;color:#fff;padding:10px 14px;border-radius:10px;text-decoration:none;border:0;cursor:pointer}.btn.alt{background:#fff;color:#111827;border:1px solid #d1d5db}.btn.warn{background:#b91c1c}
.table{width:100%;border-collapse:collapse;background:#fff}.table th,.table td{border-bottom:1px solid #e5e7eb;padding:12px 10px;text-align:left;vertical-align:top}.table th{font-size:12px;text-transform:uppercase;color:#6b7280}
.input,.select,textarea{width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:10px;background:#fff;box-sizing:border-box}textarea{min-height:110px}
.row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.row3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.stack>*+*{margin-top:12px}
.badge{display:inline-block;padding:5px 9px;border-radius:999px;font-size:12px;background:#eef2ff;color:#4338ca}.msg{padding:12px 0;border-bottom:1px solid #eef2f7}.msg:last-child{border-bottom:none}.msg small{color:#6b7280}
@media (max-width:760px){.row,.row3{grid-template-columns:1fr}.wrap{padding:16px}}
</style>
</head>
<body>
<div class="wrap">
    <div class="topbar">
        <div>
            <h1>{{ $title ?? 'Titan Chatbot Overlay' }}</h1>
            <div class="muted">Command, Agent, and Customer orchestration on top of the core chatbot engine.</div>
        </div>
        <div class="actions">
            <a class="btn alt" href="{{ route('dashboard.chatbot.overlay.command.index') }}">Command</a>
            <a class="btn alt" href="{{ route('dashboard.chatbot.overlay.agent.index') }}">Agent</a>
            <a class="btn alt" href="{{ route('dashboard.chatbot.overlay.customer.index') }}">Customer</a>
        </div>
    </div>

    @if(session('success'))
        <div class="card" style="border-color:#bbf7d0;background:#f0fdf4">{{ session('success') }}</div>
    @endif

    @yield('content')
</div>
</body>
</html>
