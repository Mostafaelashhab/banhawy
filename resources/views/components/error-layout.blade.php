@props([
    'code'      => 500,
    'title'     => 'حصلت مشكلة',
    'message'   => 'حاول تاني بعد شوية.',
    'accent'    => '#0D9488',   // teal
    'soft'      => 'rgba(13,148,136,.10)',
    'showHome'  => true,
    'showBack'  => true,
])
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<meta name="theme-color" content="#001B2A">
<title>{{ $code }} · {{ $title }} — بنهاوي</title>

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
        --navy:   #001B2A;
        --bg:     #F7F9FB;
    }

    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; }
    body {
        font-family: 'Cairo', system-ui, -apple-system, "Segoe UI", sans-serif;
        background: var(--bg);
        color: var(--ink-1);
        min-height: 100dvh;
        display: grid;
        place-items: center;
        padding: 24px;
        padding-top: calc(24px + env(safe-area-inset-top));
        padding-bottom: calc(24px + env(safe-area-inset-bottom));
        -webkit-font-smoothing: antialiased;
    }

    .err-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 30px 80px -20px rgba(0, 27, 42, 0.18),
                    0 8px 24px -8px rgba(0, 27, 42, 0.08);
        padding: 36px 28px 28px;
        max-width: 460px;
        width: 100%;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .err-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 5px;
        background: linear-gradient(90deg, {{ $accent }}, rgba(13,148,136,.4));
    }

    .err-illu {
        margin: 0 auto 18px;
        width: 140px;
        height: 140px;
        display: grid;
        place-items: center;
        background: {{ $soft }};
        border-radius: 50%;
        animation: errFloat 4s ease-in-out infinite;
    }
    @keyframes errFloat {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-8px); }
    }

    .err-code {
        font-size: 13px;
        font-weight: 800;
        color: {{ $accent }};
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .err-title {
        font-size: 26px;
        font-weight: 900;
        color: var(--ink-1);
        line-height: 1.3;
        margin: 0 0 10px;
        letter-spacing: -0.3px;
    }
    .err-msg {
        font-size: 14.5px;
        line-height: 1.8;
        color: var(--ink-3);
        font-weight: 600;
        margin: 0 0 24px;
    }

    .err-actions {
        display: flex;
        gap: 8px;
        flex-direction: column;
    }
    @media (min-width: 420px) {
        .err-actions { flex-direction: row; justify-content: center; }
    }

    .err-btn {
        padding: 13px 20px;
        border-radius: 12px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        min-width: 140px;
    }
    .err-btn:active { transform: scale(.97); }
    .err-btn-primary {
        background: var(--navy);
        color: white;
        box-shadow: 0 8px 20px -8px rgba(0, 27, 42, .35);
    }
    .err-btn-primary:hover { background: #002a40; }
    .err-btn-ghost {
        background: white;
        color: var(--ink-1);
        border: 1px solid var(--line);
    }
    .err-btn-ghost:hover { background: #FAFBFC; }

    .err-foot {
        margin-top: 22px;
        font-size: 11.5px;
        color: var(--ink-4);
        font-weight: 700;
        letter-spacing: .3px;
    }
    .err-foot a {
        color: var(--ink-3);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    /* Big faded code in the background */
    .err-bigcode {
        position: absolute;
        bottom: -22px;
        right: -8px;
        font-size: 140px;
        font-weight: 900;
        color: {{ $accent }};
        opacity: 0.05;
        line-height: 1;
        letter-spacing: -4px;
        user-select: none;
        pointer-events: none;
    }
</style>
</head>
<body>

<div class="err-card">
    <div class="err-bigcode">{{ $code }}</div>

    <div class="err-illu">
        {{ $slot }}
    </div>

    <div class="err-code">خطأ · {{ $code }}</div>
    <h1 class="err-title">{{ $title }}</h1>
    <p class="err-msg">{{ $message }}</p>

    <div class="err-actions">
        @if($showBack)
            <button type="button" class="err-btn err-btn-ghost" onclick="history.length > 1 ? history.back() : (window.location.href='/discover')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
                رجوع للخلف
            </button>
        @endif
        @if($showHome)
            <a href="/discover" class="err-btn err-btn-primary">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                الصفحة الرئيسية
            </a>
        @endif
    </div>

    <div class="err-foot">
        بنهاوي · دليل أنشطة بنها
    </div>
</div>

</body>
</html>
