@props(['title' => 'لوحة التحكم'])
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} · بنهاوي · لوحة التحكم</title>

<link rel="icon" type="image/png" href="/favicon.png">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    :root {
        --ink-1:  #001B2A;
        --ink-2:  #2A3744;
        --ink-3:  #5E6A77;
        --ink-4:  #94A1AE;
        --line:   #E6EAEE;
        --teal:   #0D9488;
        --teal-50: rgba(13,148,136,.08);
        --navy:   #001B2A;
        --bg:     #F5F7FA;
        --surface: #FFFFFF;
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: 'Cairo', system-ui, sans-serif;
        background: var(--bg);
        color: var(--ink-1);
        min-height: 100vh;
        -webkit-font-smoothing: antialiased;
    }

    /* Layout */
    .a-app { display: flex; min-height: 100vh; }
    .a-side {
        width: 240px;
        background: var(--navy);
        color: white;
        flex-shrink: 0;
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
        padding: 18px 14px;
    }
    .a-side::-webkit-scrollbar { width: 0; }
    .a-side { scrollbar-width: none; }

    .a-brand {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 6px 16px;
        border-bottom: 1px solid rgba(255,255,255,.08);
        margin-bottom: 12px;
    }
    .a-brand-mark {
        width: 36px; height: 36px;
        background: var(--teal);
        border-radius: 11px;
        display: grid; place-items: center;
        color: white; font-weight: 900;
    }
    .a-brand-text { font-weight: 900; font-size: 14px; }
    .a-brand-sub { font-size: 10.5px; color: rgba(255,255,255,.55); font-weight: 700; margin-top: 2px; }

    .a-nav-section { font-size: 10.5px; font-weight: 800; color: rgba(255,255,255,.4); letter-spacing: .5px; text-transform: uppercase; margin: 14px 8px 6px; }

    .a-link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px;
        color: rgba(255,255,255,.75);
        text-decoration: none;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        transition: background .15s ease, color .15s ease;
        margin-bottom: 2px;
    }
    .a-link:hover { background: rgba(255,255,255,.06); color: white; }
    .a-link.is-active {
        background: var(--teal);
        color: white;
        box-shadow: 0 4px 12px -4px rgba(13,148,136,.5);
    }
    .a-link svg { width: 17px; height: 17px; flex-shrink: 0; }
    .a-link-badge {
        margin-right: auto;
        background: rgba(245,158,11,.20);
        color: #FBBF24;
        font-size: 10px;
        font-weight: 800;
        padding: 1px 7px;
        border-radius: 8px;
        min-width: 22px;
        text-align: center;
    }
    .a-link.is-active .a-link-badge { background: rgba(255,255,255,.18); color: white; }

    /* Main */
    .a-main { flex: 1; min-width: 0; }
    .a-top {
        background: white;
        border-bottom: 1px solid var(--line);
        padding: 14px 28px;
        display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; z-index: 10;
    }
    .a-top h1 { margin: 0; font-size: 18px; font-weight: 900; }
    .a-top-user { display: flex; align-items: center; gap: 10px; }
    .a-top-user .a-avatar {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: var(--teal-50);
        color: var(--teal);
        display: grid; place-items: center;
        font-weight: 900;
    }
    .a-top-user-name { font-size: 13px; font-weight: 800; }
    .a-top-user-role { font-size: 11px; color: var(--ink-4); font-weight: 700; }

    .a-page { padding: 24px 28px; }

    /* Flash */
    .a-flash {
        background: rgba(16,185,129,.10);
        color: #047857;
        border-right: 4px solid #10B981;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        margin-bottom: 16px;
    }
    .a-flash.is-warn { background: rgba(245,158,11,.10); color: #92400E; border-right-color: #F59E0B; }
    .a-flash.is-error { background: rgba(220,38,38,.10); color: #991B1B; border-right-color: #DC2626; }

    /* Cards */
    .a-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 18px;
    }
    .a-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .a-card-title { font-size: 14px; font-weight: 900; }

    /* Stats */
    .a-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px; }
    .a-stat { background: var(--surface); border: 1px solid var(--line); border-radius: 14px; padding: 16px; }
    .a-stat-label { font-size: 11.5px; color: var(--ink-3); font-weight: 800; letter-spacing: .3px; text-transform: uppercase; }
    .a-stat-value { font-size: 26px; font-weight: 900; color: var(--ink-1); margin-top: 6px; line-height: 1.1; }
    .a-stat-meta { font-size: 11.5px; color: var(--ink-4); font-weight: 700; margin-top: 4px; }
    .a-stat .a-stat-icon { float: left; width: 40px; height: 40px; border-radius: 11px; display: grid; place-items: center; }

    /* Tables */
    .a-table-wrap { background: white; border: 1px solid var(--line); border-radius: 14px; overflow: hidden; }
    .a-table { width: 100%; border-collapse: collapse; }
    .a-table th, .a-table td { padding: 11px 14px; text-align: right; }
    .a-table thead th {
        background: #FAFBFC;
        border-bottom: 1px solid var(--line);
        font-size: 11px;
        font-weight: 800;
        color: var(--ink-3);
        letter-spacing: .3px;
        text-transform: uppercase;
    }
    .a-table tbody tr { border-bottom: 1px solid var(--line); transition: background .12s ease; }
    .a-table tbody tr:last-child { border-bottom: none; }
    .a-table tbody tr:hover { background: #FAFBFC; }
    .a-table td { font-size: 13px; font-weight: 600; vertical-align: middle; }
    .a-table .a-pill {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 7px;
        font-size: 11px;
        font-weight: 800;
    }
    .a-pill-gray   { background: var(--line); color: var(--ink-3); }
    .a-pill-teal   { background: rgba(13,148,136,.12); color: var(--teal); }
    .a-pill-amber  { background: rgba(245,158,11,.15); color: #B45309; }
    .a-pill-green  { background: rgba(16,185,129,.14); color: #047857; }
    .a-pill-red    { background: rgba(220,38,38,.12); color: #B91C1C; }
    .a-pill-blue   { background: rgba(14,165,233,.12); color: #0369A1; }

    .a-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform .12s ease, background .15s ease;
    }
    .a-btn:active { transform: scale(.97); }
    .a-btn-primary { background: var(--navy); color: white; }
    .a-btn-primary:hover { background: #002a40; }
    .a-btn-teal { background: var(--teal); color: white; }
    .a-btn-teal:hover { background: #0E7C72; }
    .a-btn-line { background: white; color: var(--ink-1); border: 1px solid var(--line); }
    .a-btn-line:hover { background: #FAFBFC; }
    .a-btn-danger { background: #DC2626; color: white; }
    .a-btn-danger:hover { background: #B91C1C; }
    .a-btn-sm { padding: 5px 10px; font-size: 11.5px; }

    .a-form-row { display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px; }
    .a-form-row > label { font-size: 12px; font-weight: 700; color: var(--ink-2); }
    .a-form-row input, .a-form-row select, .a-form-row textarea {
        font-family: inherit; font-size: 13.5px;
        padding: 10px 12px;
        border: 1px solid var(--line);
        border-radius: 10px;
        background: white;
        color: var(--ink-1);
        width: 100%;
    }
    .a-form-row input:focus, .a-form-row select:focus, .a-form-row textarea:focus {
        outline: none; border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(13,148,136,.12);
    }
    .a-form-actions { display: flex; gap: 8px; margin-top: 8px; }

    .a-empty {
        text-align: center;
        padding: 36px 16px;
        color: var(--ink-3);
        font-weight: 700;
    }

    /* Filters / chips */
    .a-filters {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .a-chip {
        background: white;
        border: 1px solid var(--line);
        padding: 7px 12px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 800;
        color: var(--ink-2);
        text-decoration: none;
        transition: background .12s ease, border-color .12s ease;
    }
    .a-chip:hover { background: #FAFBFC; }
    .a-chip.is-active { background: var(--navy); color: white; border-color: var(--navy); }

    /* Pagination */
    .a-pager { display: flex; justify-content: center; gap: 4px; margin-top: 18px; }
    .a-pager .a-pager-link {
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 800;
        border-radius: 9px;
        background: white;
        border: 1px solid var(--line);
        color: var(--ink-2);
        text-decoration: none;
    }
    .a-pager .a-pager-link.is-active { background: var(--navy); color: white; border-color: var(--navy); }
    .a-pager .a-pager-link.is-disabled { color: var(--ink-4); pointer-events: none; }

    @media (max-width: 900px) {
        .a-side { position: fixed; transform: translateX(100%); transition: transform .25s ease; z-index: 50; }
        .a-side.is-open { transform: translateX(0); }
        .a-main { width: 100%; }
        .a-mobile-toggle { display: grid; }
    }
    .a-mobile-toggle {
        display: none;
        background: white; border: 1px solid var(--line);
        width: 36px; height: 36px;
        border-radius: 10px;
        place-items: center;
        cursor: pointer;
    }
</style>
@stack('head')
</head>
<body>

@php
    $route = request()->route()?->getName() ?? '';
    $counts = [
        'claims_pending' => \App\Models\BusinessClaim::where('status', 'pending')->count(),
        'reports_pending' => \App\Models\BusinessReport::where('status', 'pending')->count(),
    ];
@endphp

<div class="a-app">
    <aside class="a-side" id="a-side">
        <div class="a-brand">
            <span class="a-brand-mark">ب</span>
            <div>
                <div class="a-brand-text">بنهاوي</div>
                <div class="a-brand-sub">لوحة التحكم</div>
            </div>
        </div>

        <div class="a-nav-section">عام</div>
        <a href="{{ route('admin.dashboard') }}" class="a-link @if($route === 'admin.dashboard') is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
            الرئيسية
        </a>

        <div class="a-nav-section">إدارة</div>
        <a href="{{ route('admin.businesses.index') }}" class="a-link @if(str_starts_with($route, 'admin.businesses')) is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9"/><path d="M3 9v11h18V9"/><path d="M3 9h18"/></svg>
            المتاجر
        </a>
        <a href="{{ route('admin.claims.index') }}" class="a-link @if(str_starts_with($route, 'admin.claims')) is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            طلبات الملكية
            @if($counts['claims_pending'] > 0)<span class="a-link-badge">{{ $counts['claims_pending'] }}</span>@endif
        </a>
        <a href="{{ route('admin.reports.index') }}" class="a-link @if(str_starts_with($route, 'admin.reports')) is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22V4a3 3 0 0 1 3-3h11l-3 5 3 5H7"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            البلاغات
            @if($counts['reports_pending'] > 0)<span class="a-link-badge">{{ $counts['reports_pending'] }}</span>@endif
        </a>
        <a href="{{ route('admin.reviews.index') }}" class="a-link @if(str_starts_with($route, 'admin.reviews')) is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            التقييمات
        </a>
        <a href="{{ route('admin.users.index') }}" class="a-link @if(str_starts_with($route, 'admin.users')) is-active @endif">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            المستخدمين
        </a>

        <div class="a-nav-section">حساب</div>
        <a href="{{ route('home') }}" class="a-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            رجوع للموقع
        </a>
        <form method="post" action="{{ route('logout') }}" style="display:contents;">
            @csrf
            <button type="submit" class="a-link" style="background: transparent; border: none; width: 100%; text-align: right; cursor: pointer;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                تسجيل خروج
            </button>
        </form>
    </aside>

    <main class="a-main">
        <div class="a-top">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="a-mobile-toggle" id="a-mobile-toggle" aria-label="القائمة">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1>{{ $title }}</h1>
            </div>
            <div class="a-top-user">
                <div>
                    <div class="a-top-user-name">{{ auth()->user()->name }}</div>
                    <div class="a-top-user-role">سوبر أدمن</div>
                </div>
                <div class="a-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            </div>
        </div>

        <div class="a-page">
            @if(session('flash'))
                <div class="a-flash">{{ session('flash') }}</div>
            @endif
            @if(session('flash_error'))
                <div class="a-flash is-error">{{ session('flash_error') }}</div>
            @endif
            @if(session('flash_warn'))
                <div class="a-flash is-warn">{{ session('flash_warn') }}</div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>

<script>
document.getElementById('a-mobile-toggle')?.addEventListener('click', function () {
    document.getElementById('a-side').classList.toggle('is-open');
});
</script>

@stack('scripts')
</body>
</html>
