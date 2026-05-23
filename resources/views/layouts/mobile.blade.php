<!DOCTYPE html>
<html lang="ar-EG" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover">
    <style>
        html, body { touch-action: pan-x pan-y; -ms-content-zooming: none; -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }
        input, select, textarea { font-size: 16px; }
    </style>
    <meta name="theme-color" content="#001B2A">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'بنهاوي · اكتشف بنها')</title>

    {{-- PWA --}}
    @php
        $iconVer = @file_get_contents(public_path('icons/.version')) ?: '1';
    @endphp
    <link rel="manifest" href="/manifest.json?v={{ $iconVer }}">
    <link rel="icon" type="image/png" href="/favicon.png?v={{ $iconVer }}">
    <link rel="shortcut icon" type="image/png" href="/favicon.png?v={{ $iconVer }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png?v={{ $iconVer }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="بنهاوي">
    <meta name="mobile-web-app-capable" content="yes">
    @auth
        <meta name="push-vapid-key" content="{{ config('webpush.vapid.public_key') }}">
        <meta name="push-subscribed" content="0" id="push-subscribed-meta">
    @endauth

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/banhawy.css') }}?v={{ @filemtime(public_path('css/banhawy.css')) }}">
    @stack('head')
</head>
<body class="app">

<div class="shell @yield('shell-class')">

    {{-- ── Sidebar (desktop ≥ 1024px) ───────────────────────────── --}}
    <aside class="sidebar" aria-label="التنقّل">
        <a href="{{ route('home') }}" class="brand">
            <img src="/icons/banhawy-mark.svg" alt="" width="38" height="38" class="brand-mark" style="padding: 0; background: transparent; box-shadow: 0 6px 14px -4px rgba(13,148,136,.45);">
            <span class="brand-text">
                <span class="brand-name">بنهاوي</span>
                <span class="brand-sub">دليل أنشطة بنها</span>
            </span>
        </a>

        @include('partials.sidebar-nav')

        <div class="sidebar-foot">
            @auth
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="sidebar-item" style="width: 100%; background: none; border: none;">
                        <x-icon name="logout" :size="18"/><span>تسجيل الخروج</span>
                    </button>
                </form>
            @else
                <a href="{{ route('register.step1') }}" class="sidebar-cta">
                    <x-icon name="plus" :size="14" stroke="white" w="2.4"/>
                    أضف نشاطك
                </a>
            @endauth
        </div>
    </aside>

    {{-- ── Main ─────────────────────────────────────────────────── --}}
    <main class="screen @yield('screen-class')">

      

        {{-- Desktop topbar (shown ≥ 1024px) --}}
        <header class="topbar">
            <h1 class="topbar-title">@yield('page-title', 'بنهاوي')</h1>
            @auth
                <a href="{{ route('merchant.dashboard') }}" class="topbar-user">
                    <span class="topbar-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span>{{ auth()->user()->name }}</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="topbar-user topbar-user--ghost">
                    <x-icon name="user" :size="14"/> دخول
                </a>
            @endauth
        </header>

        @yield('content')

        @hasSection('bottom-nav')
            @yield('bottom-nav')
        @endif
    </main>
</div>

{{-- ── Offline indicator ────────────────────────────────────── --}}
<div id="net-toast" hidden
     style="position: fixed; right: 12px; left: 12px; top: calc(8px + env(safe-area-inset-top)); z-index: 70;
            background: #B91C1C; color: white;
            border-radius: 12px; padding: 10px 14px;
            display: flex; align-items: center; gap: 10px;
            font-size: 12px; font-weight: 700; text-align: center;
            box-shadow: 0 6px 20px rgba(185,28,28,.25);
            transition: transform .2s ease, opacity .2s ease;
            transform: translateY(-12px); opacity: 0;">
    <span style="width: 8px; height: 8px; border-radius: 50%; background: white; flex-shrink: 0;"></span>
    <span id="net-toast-text" style="flex: 1;">بدون اتصال — بتشتغل من الكاش</span>
</div>

{{-- ── PWA install prompt UI ───────────────────────────────── --}}
<div id="pwa-install-banner" hidden
     style="position: fixed; right: 12px; left: 12px; bottom: 76px; z-index: 60;
            background: var(--navy); color: white;
            border-radius: 16px; padding: 12px 14px;
            display: flex; align-items: center; gap: 12px;
            box-shadow: 0 12px 32px rgba(0,27,42,.25);">
    <span id="pwa-install-icon" style="width: 36px; height: 36px; border-radius: 10px; background: var(--teal); display: grid; place-items: center; flex-shrink: 0;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="white"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
    </span>
    <div style="flex: 1; min-width: 0;">
        <div id="pwa-install-title" style="font-weight: 800; font-size: 13px;">ثبّت بنهاوي على موبايلك</div>
        <div id="pwa-install-sub" style="font-size: 11px; color: rgba(255,255,255,.7); font-weight: 600;">سرعة + يشتغل بدون نت</div>
    </div>
    <button id="pwa-install-btn" style="background: var(--teal); color: white; border: none; padding: 8px 14px; border-radius: 10px; font-weight: 800; font-size: 12px;">ثبّت</button>
    <button id="pwa-install-close" aria-label="إغلاق" style="background: transparent; border: none; color: rgba(255,255,255,.6); padding: 4px; font-size: 18px; line-height: 1;">×</button>
</div>

{{-- iOS Safari install instructions (full-screen sheet) --}}
<div id="ios-install-sheet" hidden
     style="position: fixed; inset: 0; z-index: 80; background: rgba(0,27,42,.6); backdrop-filter: blur(4px);
            display: flex; align-items: flex-end; justify-content: center;">
    <div style="background: white; border-radius: 24px 24px 0 0; padding: 24px 20px 32px; width: 100%; max-width: 480px;">
        <div style="width: 40px; height: 4px; border-radius: 2px; background: var(--gray-200); margin: 0 auto 18px;"></div>
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 18px;">
            <span style="width: 48px; height: 48px; border-radius: 14px; background: var(--teal); display: grid; place-items: center; flex-shrink: 0;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="white"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
            </span>
            <div>
                <div style="font-weight: 900; font-size: 16px; color: var(--navy);">ثبّت بنهاوي على شاشتك</div>
                <div class="label-meta">يشتغل بدون نت ويفتح بسرعة</div>
            </div>
        </div>
        <ol style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
            <li style="display: flex; gap: 12px; align-items: center;">
                <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--gray-100); display: grid; place-items: center; font-weight: 900; font-size: 13px; flex-shrink: 0;">١</span>
                <span style="font-size: 13px; font-weight: 700;">دوس على زر المشاركة
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#0D9488" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -3px;">
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/>
                    </svg>
                    في شريط Safari تحت
                </span>
            </li>
            <li style="display: flex; gap: 12px; align-items: center;">
                <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--gray-100); display: grid; place-items: center; font-weight: 900; font-size: 13px; flex-shrink: 0;">٢</span>
                <span style="font-size: 13px; font-weight: 700;">انزل واختار <strong style="color: var(--teal);">"Add to Home Screen"</strong></span>
            </li>
            <li style="display: flex; gap: 12px; align-items: center;">
                <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--gray-100); display: grid; place-items: center; font-weight: 900; font-size: 13px; flex-shrink: 0;">٣</span>
                <span style="font-size: 13px; font-weight: 700;">دوس <strong style="color: var(--teal);">Add</strong> فوق على اليمين</span>
            </li>
        </ol>
        <button id="ios-install-close" class="btn btn-navy btn-full" style="padding: 13px; font-size: 13px; margin-top: 22px;">تمام</button>
    </div>
</div>

{{-- ── PWA: service worker + install + push helpers ───────── --}}
<script>
(function () {
    if (!('serviceWorker' in navigator)) return;

    // Register SW after load to avoid contending with the page bootstrap
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function (e) {
            console.warn('[Banhawy] SW registration failed:', e);
        });
    });

    // ── Install prompt ─────────────────────────────────────
    var deferredPrompt = null;
    var banner       = document.getElementById('pwa-install-banner');
    var installBtn   = document.getElementById('pwa-install-btn');
    var closeBtn     = document.getElementById('pwa-install-close');
    var titleEl      = document.getElementById('pwa-install-title');
    var subEl        = document.getElementById('pwa-install-sub');
    var iosSheet     = document.getElementById('ios-install-sheet');
    var iosCloseBtn  = document.getElementById('ios-install-close');

    var ua = navigator.userAgent || '';
    var isIOS       = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
    var isAndroid   = /Android/.test(ua);
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true;

    function dismissed() {
        var v = localStorage.getItem('pwa_install_dismissed_at');
        return v && (Date.now() - parseInt(v, 10)) < 7 * 24 * 3600 * 1000;
    }
    function markDismissed() {
        try { localStorage.setItem('pwa_install_dismissed_at', Date.now().toString()); } catch (e) {}
    }
    function hideBanner() {
        if (banner) banner.hidden = true;
        markDismissed();
    }

    // ── Path 1 — Chrome/Edge (Android + desktop): native prompt ──
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        if (banner && !dismissed() && !isStandalone) {
            titleEl.textContent = 'ثبّت بنهاوي على موبايلك';
            subEl.textContent   = 'سرعة + يشتغل بدون نت';
            installBtn.textContent = 'ثبّت';
            banner.hidden = false;
        }
    });

    // ── Path 2 — iOS Safari: no event, show "Add to Home Screen" sheet ──
    function isIOSSafari() {
        return isIOS && /Safari/.test(ua) && !/CriOS|FxiOS|EdgiOS/.test(ua);
    }
    function showIOSBanner() {
        if (!banner || dismissed() || isStandalone) return;
        titleEl.textContent = 'ثبّت بنهاوي على شاشة آيفونك';
        subEl.textContent   = 'دوس "كيف" للتعليمات';
        installBtn.textContent = 'كيف؟';
        banner.hidden = false;
    }

    // ── Path 3 — Android Firefox + others: simple manual hint ────
    function showManualBanner() {
        if (!banner || dismissed() || isStandalone) return;
        titleEl.textContent = 'ثبّت بنهاوي';
        subEl.textContent   = 'من قائمة المتصفح ⋮ → "إضافة للشاشة الرئيسية"';
        installBtn.hidden = true; // no actionable button
        banner.hidden = false;
    }

    if (isIOSSafari()) {
        // Slight delay so the user gets a moment with the page first
        setTimeout(showIOSBanner, 1500);
    } else if (isAndroid && !window.chrome) {
        // Android Firefox / Samsung Internet — no beforeinstallprompt
        setTimeout(showManualBanner, 1500);
    }

    if (installBtn) {
        installBtn.addEventListener('click', async function () {
            if (deferredPrompt) {
                banner.hidden = true;
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
                return;
            }
            // iOS path → open the instructions sheet
            if (isIOSSafari() && iosSheet) {
                iosSheet.hidden = false;
            }
        });
    }
    if (closeBtn) closeBtn.addEventListener('click', hideBanner);

    function closeIosSheet() {
        if (iosSheet) iosSheet.hidden = true;
        hideBanner();
    }
    if (iosCloseBtn) iosCloseBtn.addEventListener('click', closeIosSheet);
    // Backdrop click (outside the white card) also closes
    if (iosSheet) iosSheet.addEventListener('click', function (e) {
        if (e.target === iosSheet) closeIosSheet();
    });

    window.addEventListener('appinstalled', function () {
        if (banner) banner.hidden = true;
        try { localStorage.setItem('pwa_installed', '1'); } catch (e) {}
    });

    // ── Offline / online toast ─────────────────────────────
    var toast    = document.getElementById('net-toast');
    var toastTxt = document.getElementById('net-toast-text');
    var reconnectTimer = null;

    function showToast(msg, color) {
        if (!toast) return;
        toastTxt.textContent = msg;
        toast.style.background = color;
        toast.hidden = false;
        // double-rAF so the transition catches the just-shown element
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            });
        });
    }
    function hideToast(delay) {
        if (!toast) return;
        setTimeout(function () {
            toast.style.transform = 'translateY(-12px)';
            toast.style.opacity = '0';
            setTimeout(function () { toast.hidden = true; }, 220);
        }, delay || 0);
    }

    window.addEventListener('offline', function () {
        clearTimeout(reconnectTimer);
        showToast('بدون اتصال — بتشتغل من الكاش', '#B91C1C');
    });
    window.addEventListener('online', function () {
        showToast('رجع الاتصال ✓', '#15803D');
        reconnectTimer = setTimeout(function () { hideToast(); }, 1800);
    });

    // On first paint: if already offline, surface it
    if (!navigator.onLine) {
        showToast('بدون اتصال — بتشتغل من الكاش', '#B91C1C');
    }

    // ── Push helpers (used by the notifications-opt-in UI) ─
    function urlB64ToUint8(b64) {
        var padding = '='.repeat((4 - b64.length % 4) % 4);
        var base64 = (b64 + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw = atob(base64);
        var out = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
        return out;
    }

    window.banhawyPush = {
        async subscribe() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                throw new Error('الإشعارات مش مدعومة على المتصفح ده');
            }
            var perm = await Notification.requestPermission();
            if (perm !== 'granted') throw new Error('لازم تسمح بالإشعارات');

            var reg = await navigator.serviceWorker.ready;
            var vapidMeta = document.querySelector('meta[name="push-vapid-key"]');
            if (!vapidMeta || !vapidMeta.content) throw new Error('VAPID key missing');

            var sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlB64ToUint8(vapidMeta.content),
            });

            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var res = await fetch('/push/subscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(sub.toJSON()),
            });
            if (!res.ok) throw new Error('فشل تسجيل الاشتراك');
            return sub;
        },

        async unsubscribe() {
            var reg = await navigator.serviceWorker.ready;
            var sub = await reg.pushManager.getSubscription();
            if (!sub) return;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ endpoint: sub.endpoint }),
            });
            await sub.unsubscribe();
        },

        async isSubscribed() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) return false;
            try {
                var reg = await navigator.serviceWorker.ready;
                var sub = await reg.pushManager.getSubscription();
                return !!sub;
            } catch (e) { return false; }
        },
    };
})();
</script>

{{-- Bottom nav: always visible (no auto-hide) --}}
<script>
(function () {
    var bnav = document.querySelector('.bnav');
    if (bnav) bnav.classList.remove('is-hidden');
})();
</script>

@stack('scripts')

<script>
(function () {
    // iOS Safari: block pinch gestures
    ['gesturestart', 'gesturechange', 'gestureend'].forEach(function (ev) {
        document.addEventListener(ev, function (e) { e.preventDefault(); }, { passive: false });
    });

    // Block multi-touch pinch on touch events
    document.addEventListener('touchmove', function (e) {
        if (e.touches && e.touches.length > 1) e.preventDefault();
    }, { passive: false });

    // Block double-tap zoom
    var lastTouchEnd = 0;
    document.addEventListener('touchend', function (e) {
        var now = Date.now();
        if (now - lastTouchEnd <= 350) e.preventDefault();
        lastTouchEnd = now;
    }, { passive: false });

    // Block Ctrl/⌘ + wheel zoom & Ctrl/⌘ + / - / 0 zoom on desktop
    window.addEventListener('wheel', function (e) {
        if (e.ctrlKey || e.metaKey) e.preventDefault();
    }, { passive: false });
    window.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && ['=', '+', '-', '_', '0'].indexOf(e.key) !== -1) {
            e.preventDefault();
        }
    });
})();
</script>
</body>
</html>
