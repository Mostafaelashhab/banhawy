@extends('layouts.mobile')

@section('title', 'بنهاوي · دليل أنشطة بنها على الخريطة')
@section('page-title', 'بنهاوي')
@section('shell-class', 'is-landing')

@section('content')

{{-- ════════════════════════════════════════════════════════
     MOBILE ONBOARDING SLIDER (visible < 1024px)
     ════════════════════════════════════════════════════════ --}}
<div class="splash-mobile" id="onboard">
    <div class="splash-mobile-inner">
        <div class="splash-deco"></div>
        <div class="splash-deco-2"></div>

        {{-- Skip --}}
        <a href="{{ route('home') }}" class="onboard-skip" id="onboard-skip">تخطي</a>

        {{-- Slides rail --}}
        <div class="onboard-rail" id="onboard-rail" tabindex="0">

            {{-- Slide 1 — Welcome --}}
            <section class="onboard-slide" data-slide="0">
                <div class="splash-logo">
                    <svg viewBox="0 0 24 24" width="42" height="42" fill="white">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                    </svg>
                </div>
                <h1 class="splash-title">بنهاوي</h1>
                <p class="splash-sub">اكتشف أفضل الأنشطة<br>في بنها بضغطة واحدة</p>
            </section>

            {{-- Slide 2 — Map --}}
            <section class="onboard-slide" data-slide="1">
                <div class="onboard-visual onboard-visual-map">
                    <div class="map-bg map-roads" style="position: absolute; inset: 0; border-radius: 24px;"></div>
                    <div class="onboard-pin onboard-pin-1">
                        <svg viewBox="0 0 28 32" width="34" height="38">
                            <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#0D9488"/>
                            <circle cx="14" cy="14" r="5" fill="white"/>
                        </svg>
                    </div>
                    <div class="onboard-pin onboard-pin-2">
                        <svg viewBox="0 0 28 32" width="28" height="32">
                            <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#1E3A8A"/>
                            <circle cx="14" cy="14" r="5" fill="white"/>
                        </svg>
                    </div>
                    <div class="onboard-pin onboard-pin-3">
                        <svg viewBox="0 0 28 32" width="26" height="30">
                            <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#25D366"/>
                            <circle cx="14" cy="14" r="5" fill="white"/>
                        </svg>
                    </div>
                </div>
                <h2 class="splash-title" style="font-size: 26px;">كل بنها على الخريطة</h2>
                <p class="splash-sub">مطاعم، كافيهات، عيادات، صالونات<br>كله في مكان واحد قريب منك</p>
            </section>

            {{-- Slide 3 — WhatsApp --}}
            <section class="onboard-slide" data-slide="2">
                <div class="onboard-visual onboard-visual-wa">
                    <div class="onboard-wa-bubble onboard-wa-bubble-1">
                        <div style="font-size: 11px; font-weight: 800;">طلب جديد · #0241</div>
                        <div style="font-size: 10px; opacity: .7; margin-top: 4px;">2× بيتزا بيبروني · 1× مارجريتا</div>
                    </div>
                    <div class="onboard-wa-mark">
                        <svg viewBox="0 0 24 24" width="32" height="32" fill="white">
                            <path d="M12 2a10 10 0 0 0-8.94 14.5L2 22l5.66-1.5A10 10 0 1 0 12 2zm5 13.4c-.2.6-1.1 1-1.7 1.1-.4 0-1 .1-3-1.1-2.5-1.5-4-4-4.1-4.2-.1-.2-1-1.3-1-2.5s.6-1.8.8-2c.2-.2.5-.3.6-.3h.5c.2 0 .4 0 .6.4l.8 2c.1.1.1.3 0 .5l-.3.5c-.1.2-.3.4-.1.7.2.3.7 1.2 1.6 1.9 1 .9 1.9 1.2 2.1 1.3.2.1.4.1.6-.1l.7-.8c.1-.2.4-.3.6-.2l1.8.9c.2.1.4.2.4.4 0 .2.1.5-.1 1z"/>
                        </svg>
                    </div>
                    <div class="onboard-wa-bubble onboard-wa-bubble-2">
                        <div style="font-size: 10px; opacity: .7;">العميل</div>
                        <div style="font-size: 11px; font-weight: 700;">عاوز بيتزا توصلني الساعة 10</div>
                    </div>
                </div>
                <h2 class="splash-title" style="font-size: 26px;">اطلب على واتساب</h2>
                <p class="splash-sub">طلبات وحجوزات مباشرة لصاحب النشاط<br>من غير تطبيقات وسيطة</p>
            </section>
        </div>

        {{-- Dots indicator --}}
        <div class="onboard-dots" id="onboard-dots">
            <button data-go="0" class="active" aria-label="شاشة 1"></button>
            <button data-go="1" aria-label="شاشة 2"></button>
            <button data-go="2" aria-label="شاشة 3"></button>
        </div>

        {{-- CTA --}}
        <div class="splash-cta">
            <button id="onboard-cta" class="btn btn-teal btn-full" style="padding: 14px;">
                <span id="onboard-cta-label">التالي</span>
                <x-icon name="arrow-l" :size="16" stroke="white" w="2.2"/>
            </button>
            <p class="splash-link">
                عندك نشاط؟
                <a href="{{ route('register.step1') }}">اعمل صفحة لنشاطك</a>
            </p>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     DESKTOP LANDING (visible ≥ 1024px)
     ════════════════════════════════════════════════════════ --}}
<div class="splash-desktop">

    {{-- ── LANDING NAV ─────────────────────────────────── --}}
    <nav class="land-nav">
        <a href="{{ route('home') }}" class="land-brand">
            <span class="land-brand-mark">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="white"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
            </span>
            <span>بنهاوي</span>
        </a>
        <div class="land-nav-links">
            <a href="{{ route('map') }}">الخريطة</a>
            <a href="{{ route('search') }}">الأنشطة</a>
            <a href="{{ route('track') }}">تتبّع طلب</a>
            <a href="#للأنشطة">لأصحاب الأنشطة</a>
        </div>
        <div class="land-nav-cta">
            @auth
                <a href="{{ route('merchant.dashboard') }}" class="btn btn-navy" style="padding: 9px 16px; font-size: 12px;">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="land-nav-link">تسجيل الدخول</a>
                <a href="{{ route('register.step1') }}" class="btn btn-teal" style="padding: 9px 18px; font-size: 12px;">أضف نشاطك</a>
            @endauth
        </div>
    </nav>

    {{-- ── HERO ────────────────────────────────────────── --}}
    <section class="hero">
        <div class="hero-text">
            <span class="hero-eyebrow">
                <span class="hero-eyebrow-dot"></span>
                دليل أنشطة بنها · الإصدار التجريبي
            </span>
            <h1 class="hero-title">
                اكتشف أفضل أنشطة بنها<br>
                <span style="color: var(--teal);">في مكان واحد</span>
            </h1>
            <p class="hero-lead">
                من المطاعم والكافيهات للعيادات والخدمات — كل أنشطة بنها على خريطة واحدة،
                وكل صاحب نشاط له صفحة كاملة بمنيو وحجوزات وطلبات واتساب.
            </p>
            <div class="hero-actions">
                <a href="{{ route('home') }}" class="btn btn-teal" style="padding: 14px 22px; font-size: 14px;">
                    <x-icon name="pin" :size="16" stroke="white" w="2.2"/>
                    استكشف الخريطة
                </a>
                <a href="{{ route('register.step1') }}" class="btn btn-line" style="padding: 14px 22px; font-size: 14px; border: 1.5px solid var(--line-2);">
                    أضف نشاطك مجانًا
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-n">{{ $stats['businesses'] }}+</div>
                    <div class="hero-stat-l">نشاط مسجّل</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-n">{{ $stats['categories'] }}</div>
                    <div class="hero-stat-l">تصنيف رئيسي</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-n">24/7</div>
                    <div class="hero-stat-l">وصول دائم</div>
                </div>
            </div>
        </div>

        {{-- Visual: a mini map preview with pins --}}
        <div class="hero-visual">
            <div class="hero-map">
                <div class="map-bg map-roads" style="position: absolute; inset: 0;"></div>

                @php
                    $lats = $featured->pluck('lat')->map(fn($v) => (float) $v);
                    $lngs = $featured->pluck('lng')->map(fn($v) => (float) $v);
                    $minLat = $lats->min() - 0.002;
                    $maxLat = $lats->max() + 0.002;
                    $minLng = $lngs->min() - 0.002;
                    $maxLng = $lngs->max() + 0.002;
                @endphp
                @foreach($featured->take(5) as $b)
                    @php
                        $top = 100 - ((((float) $b->lat - $minLat) / max(0.001, $maxLat - $minLat)) * 70) - 15;
                        $right = ((((float) $b->lng - $minLng) / max(0.001, $maxLng - $minLng)) * 70) + 15;
                        $color = $b->is_featured ? '#0D9488' : ($b->plan?->slug === 'pro' ? '#1E3A8A' : '#001B2A');
                    @endphp
                    <div class="map-pin" style="top: {{ $top }}%; right: {{ $right }}%;">
                        <svg viewBox="0 0 28 32" width="32" height="36">
                            <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="{{ $color }}"/>
                            <circle cx="14" cy="14" r="5" fill="white"/>
                        </svg>
                    </div>
                @endforeach

                {{-- Floating business card preview --}}
                @php $top1 = $featured->first(); @endphp
                @if($top1)
                    <div class="hero-map-card">
                        <div class="ph ph-{{ $top1->type->slug }}" style="width: 44px; height: 44px; border-radius: 10px; font-size: 12px; flex-shrink: 0;">
                            {{ mb_substr($top1->name, 0, 2) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 800; font-size: 13px; color: var(--navy);">{{ $top1->name }}</div>
                            <div style="display: flex; gap: 6px; align-items: center; margin-top: 2px;">
                                <span class="stars" style="font-size: 11px;"><x-icon name="star-f" :size="10"/> {{ number_format($top1->rating, 1) }}</span>
                                <span class="dot"></span>
                                <span class="label-meta">{{ $top1->category }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ── CATEGORIES ────────────────────────────────────── --}}
    <section class="land-section">
        <div class="section-head">
            <h2 class="section-title">تصفّح حسب التصنيف</h2>
            <a href="{{ route('home') }}" class="section-link">عرض الكل ›</a>
        </div>
        <div class="cat-grid">
            @foreach($types as $t)
                <a href="{{ route('search', ['q' => $t->name_ar]) }}" class="cat-tile">
                    <span class="cat-icon"><x-icon :name="$t->icon" :size="22" stroke="#0D9488"/></span>
                    <span class="cat-name">{{ $t->name_ar }}</span>
                    <span class="cat-sub">{{ $t->description_ar }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ── FEATURED ──────────────────────────────────────── --}}
    <section class="land-section">
        <div class="section-head">
            <h2 class="section-title">الأنشطة المميزة في بنها</h2>
            <a href="{{ route('search') }}" class="section-link">شوف كل النتائج ›</a>
        </div>
        <div class="feat-grid">
            @foreach($featured->take(4) as $b)
                <a href="{{ route('business.show', $b) }}" class="feat-card">
                    <div class="feat-cover ph ph-{{ $b->type->slug }}">
                        @if($b->is_featured)
                            <span class="feat-badge">مميز</span>
                        @endif
                    </div>
                    <div class="feat-body">
                        <div class="feat-row">
                            <span class="feat-name">{{ $b->name }}</span>
                            <span class="stars"><x-icon name="star-f" :size="11"/> {{ number_format($b->rating, 1) }}</span>
                        </div>
                        <div class="feat-meta">{{ $b->category }}</div>
                        <div class="feat-row" style="margin-top: 10px;">
                            @if($b->isOpenNow())
                                <span class="chip open" style="padding: 2px 7px; font-size: 10px;">مفتوح</span>
                            @else
                                <span class="chip closed" style="padding: 2px 7px; font-size: 10px;">مغلق</span>
                            @endif
                            <span class="feat-cta">عرض الصفحة <x-icon name="chev-l" :size="12"/></span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ── MERCHANT CTA ──────────────────────────────────── --}}
    <section class="land-section">
        <div class="merch-cta">
            <div class="merch-text">
                <span class="hero-eyebrow" style="background: rgba(255,255,255,.1); color: white;">
                    <span class="hero-eyebrow-dot" style="background: var(--teal);"></span>
                    لأصحاب الأنشطة
                </span>
                <h2 class="merch-title">اعمل صفحة لنشاطك في دقيقتين</h2>
                <p class="merch-lead">
                    حط منيو/منتجات، استقبل طلبات على واتساب، خلّي عملاءك يحجزوا أونلاين،
                    وشوف تحليلات نشاطك — كل ده من غير ما تعمل website من الصفر.
                </p>
                <div class="merch-features">
                    <div class="merch-feat"><x-icon name="check" :size="14" stroke="#25D366" w="3"/> طلبات تيجي على واتساب فورًا</div>
                    <div class="merch-feat"><x-icon name="check" :size="14" stroke="#25D366" w="3"/> صفحة كاملة بـ QR للمحل</div>
                    <div class="merch-feat"><x-icon name="check" :size="14" stroke="#25D366" w="3"/> تحليلات + ظهور على الخريطة</div>
                </div>
                <a href="{{ route('register.step1') }}" class="btn btn-teal" style="padding: 14px 24px; font-size: 14px;">
                    ابدأ مجانًا
                    <x-icon name="arrow-l" :size="16" stroke="white" w="2.2"/>
                </a>
            </div>
            <div class="merch-mock">
                <div class="mock-card">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                        <div class="ph ph-restaurant" style="width: 32px; height: 32px; border-radius: 9px; font-size: 10px;">PZ</div>
                        <div>
                            <div style="font-weight: 900; font-size: 12px;">Pizza Zone</div>
                            <div class="tiny" style="color: var(--ink-3);">طلب جديد · #0241</div>
                        </div>
                    </div>
                    <div style="background: var(--gray-100); border-radius: 10px; padding: 8px 10px; font-size: 11px; font-weight: 600; color: var(--gray-700);">
                        1× مارجريتا · 2× بيبروني · 1× بيبسي
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 10px; align-items: center;">
                        <span style="font-weight: 800; font-size: 13px; color: var(--navy);">375ج</span>
                        <span class="btn btn-wa" style="padding: 6px 10px; font-size: 11px;">
                            <x-icon name="whatsapp" :size="12" stroke="white" w="2"/> تأكيد
                        </span>
                    </div>
                </div>
                <div class="mock-card mock-card-2">
                    <div class="muted" style="margin-bottom: 6px;">زيارات الأسبوع</div>
                    <div style="font-weight: 900; font-size: 22px; color: var(--navy);">248</div>
                    <span class="chip teal" style="padding: 2px 8px; font-size: 10px;">+18%</span>
                </div>
            </div>
        </div>
    </section>

    <footer class="land-foot">
        <div>بنهاوي · دليل أنشطة بنها والقليوبية</div>
        <div class="muted">جميع الحقوق محفوظة © 2026</div>
    </footer>
</div>

@endsection

@push('scripts')
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var rail   = document.getElementById('onboard-rail');
        var dots   = document.querySelectorAll('#onboard-dots button');
        var cta    = document.getElementById('onboard-cta');
        var label  = document.getElementById('onboard-cta-label');
        if (!rail || !cta) return;

        var slides = rail.querySelectorAll('.onboard-slide');
        var count  = slides.length;
        var current = 0;
        var isRTL = document.documentElement.dir === 'rtl';

        function scrollToSlide(i) {
            var slide = slides[i];
            if (!slide) return;
            slide.scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
        }

        function setActive(i) {
            current = i;
            dots.forEach(function (d, idx) { d.classList.toggle('active', idx === i); });
            label.textContent = (i === count - 1) ? 'ابدأ الآن' : 'التالي';
        }

        // Dot clicks
        dots.forEach(function (d, idx) {
            d.addEventListener('click', function () { scrollToSlide(idx); });
        });

        // CTA — advance or finish
        cta.addEventListener('click', function () {
            if (current < count - 1) {
                scrollToSlide(current + 1);
            } else {
                try { localStorage.setItem('banhawy_seen_splash', '1'); } catch (e) {}
                window.location.href = '{{ route('home') }}';
            }
        });

        // Skip — mark as seen and go home
        var skip = document.getElementById('onboard-skip');
        if (skip) {
            skip.addEventListener('click', function () {
                try { localStorage.setItem('banhawy_seen_splash', '1'); } catch (e) {}
            });
        }

        // Detect active slide on scroll. In RTL the rail's scrollLeft is negative
        // or zero-at-start depending on the browser; use offset-based detection.
        var ticking = false;
        rail.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                var railRect = rail.getBoundingClientRect();
                var bestIdx = 0;
                var bestDist = Infinity;
                slides.forEach(function (s, idx) {
                    var rect = s.getBoundingClientRect();
                    var dist = Math.abs(rect.left + rect.width / 2 - (railRect.left + railRect.width / 2));
                    if (dist < bestDist) { bestDist = dist; bestIdx = idx; }
                });
                if (bestIdx !== current) setActive(bestIdx);
                ticking = false;
            });
        });

        // Init: in RTL, browsers start at scrollLeft = 0 OR the right-most position.
        // Force the first slide on load so it's predictable.
        setTimeout(function () {
            scrollToSlide(0);
            setActive(0);
        }, 0);
    });
})();
</script>
@endpush

@push('head')
{{-- ─── Synchronous redirect guard ───────────────────────────────
     Runs in <head> BEFORE any paint. If the user has already seen
     the onboarding (mobile only), inject a black overlay AND
     navigate away — no flash of the splash content.
     -------------------------------------------------------------- --}}
<script>
(function () {
    try {
        var isMobile = window.matchMedia('(max-width: 1023px)').matches;
        var seen     = localStorage.getItem('banhawy_seen_splash') === '1';
        var force    = new URLSearchParams(location.search).has('intro');
        if (isMobile && seen && !force) {
            // Inject a synchronous full-screen cover so the splash never paints.
            // document.write is intentional — it runs during HTML parsing, before
            // <body> is constructed, and reliably prevents the flash.
            document.write(
                '<style>html,body{background:#001B2A!important;}' +
                'body>*,header,aside,main,nav,div{visibility:hidden!important;}' +
                '</style>'
            );
            location.replace('{{ route('home') }}');
        }
    } catch (e) { /* localStorage blocked — fall through and show splash */ }
})();
</script>

<style>
/* ─── MOBILE ONBOARDING (default) ────────────────────────── */
.splash-desktop { display: none; }
.splash-mobile {
    background: var(--navy);
    color: white;
    flex: 1;
    width: 100%;
    display: flex;
    min-height: 100vh;
    min-height: 100dvh;
    overflow: hidden;
}
.splash-mobile-inner {
    position: relative;
    flex: 1;
    display: flex;
    flex-direction: column;
    width: 100%;
    color: white;
}

.onboard-skip {
    position: absolute; top: 22px; left: 22px;
    color: rgba(255,255,255,.6); font-size: 13px; font-weight: 700;
    z-index: 3;
}
.onboard-skip:hover { color: white; }

.onboard-rail {
    flex: 1;
    display: flex;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: none;
    position: relative;
    z-index: 2;
    outline: none;
}
.onboard-rail::-webkit-scrollbar { display: none; }
.onboard-slide {
    flex: 0 0 100%;
    scroll-snap-align: start;
    scroll-snap-stop: always;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 60px 28px 20px;
    text-align: center;
}

/* Slide 2 — map visual */
.onboard-visual {
    width: 80%; max-width: 280px;
    aspect-ratio: 1;
    border-radius: 28px;
    margin-bottom: 36px;
    position: relative;
    overflow: hidden;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
}
.onboard-visual-map { background: var(--surface); }
.onboard-pin {
    position: absolute;
    transform: translate(50%, -100%);
    filter: drop-shadow(0 6px 12px rgba(0,27,42,.3));
}
.onboard-pin-1 { top: 50%; right: 50%; transform: translate(50%, -100%) scale(1.1); animation: pinFloat 3s ease-in-out infinite; }
.onboard-pin-2 { top: 28%; right: 70%; animation: pinFloat 3s ease-in-out .8s infinite; }
.onboard-pin-3 { top: 70%; right: 25%; animation: pinFloat 3s ease-in-out 1.5s infinite; }
@keyframes pinFloat {
    0%, 100% { transform: translate(50%, -100%); }
    50% { transform: translate(50%, calc(-100% - 6px)); }
}

/* Slide 3 — WhatsApp visual */
.onboard-visual-wa {
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, rgba(37,211,102,.15), rgba(37,211,102,.05));
    border: 1px solid rgba(37,211,102,.2);
}
.onboard-wa-mark {
    width: 70px; height: 70px; border-radius: 22px;
    background: var(--wa);
    display: grid; place-items: center;
    box-shadow: 0 10px 24px rgba(37,211,102,.4);
}
.onboard-wa-bubble {
    position: absolute;
    background: white; color: var(--ink);
    border-radius: 14px;
    padding: 10px 14px;
    box-shadow: 0 10px 24px rgba(0,0,0,.2);
    max-width: 70%;
    text-align: right;
}
.onboard-wa-bubble-1 { top: 22px; right: 22px; animation: bubbleIn 4s ease-in-out infinite; }
.onboard-wa-bubble-2 { bottom: 22px; left: 22px; animation: bubbleIn 4s ease-in-out 2s infinite; }
@keyframes bubbleIn {
    0%, 90%, 100% { opacity: 0; transform: translateY(8px); }
    10%, 80%      { opacity: 1; transform: translateY(0); }
}

/* Dots */
.onboard-dots {
    display: flex; gap: 6px;
    justify-content: center;
    padding: 12px 0 8px;
    z-index: 2;
}
.onboard-dots button {
    width: 8px; height: 4px; border-radius: 2px;
    background: rgba(255,255,255,.2); border: none;
    transition: width .25s ease, background .25s ease;
    padding: 0;
    cursor: pointer;
}
.onboard-dots button.active { width: 24px; background: var(--teal); }
.splash-deco {
    position: absolute; top: -60px; left: -60px;
    width: 220px; height: 220px; border-radius: 50%;
    background: rgba(13,148,136,.18); filter: blur(50px);
    pointer-events: none;
}
.splash-deco-2 {
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
    background-size: 18px 18px; pointer-events: none;
}
.splash-logo {
    width: 84px; height: 84px; border-radius: 26px;
    background: var(--teal); display: grid; place-items: center;
    margin-bottom: 28px;
    box-shadow: 0 12px 32px rgba(13,148,136,.4);
}
.splash-title { color: white; font-size: 32px; font-weight: 900; letter-spacing: -.5px; margin-bottom: 10px; }
.splash-sub { color: rgba(255,255,255,.7); font-size: 14px; font-weight: 600; text-align: center; line-height: 1.7; }
.splash-cta { padding: 0 24px 32px; position: relative; z-index: 2; }
.splash-link { text-align: center; color: rgba(255,255,255,.55); font-size: 11px; font-weight: 600; margin-top: 14px; }
.splash-link a { color: white; text-decoration: underline; font-weight: 800; }

/* ─── DESKTOP LANDING (≥ 1024px) ─────────────────────────── */
@media (min-width: 1024px) {
    .splash-mobile { display: none; }
    .splash-desktop { display: block; }

    /* Landing nav */
    .land-nav {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px max(32px, calc((100% - var(--content-max)) / 2));
        background: rgba(255,255,255,.85);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--line);
        position: sticky; top: 0; z-index: 50;
    }
    .land-brand {
        display: flex; align-items: center; gap: 10px;
        font-weight: 900; font-size: 18px; color: var(--navy);
    }
    .land-brand-mark {
        width: 32px; height: 32px; border-radius: 10px;
        background: var(--teal); display: grid; place-items: center;
        box-shadow: 0 3px 10px rgba(13,148,136,.3);
    }
    .land-nav-links { display: flex; gap: 28px; }
    .land-nav-links a { font-size: 13px; font-weight: 700; color: var(--ink-3); }
    .land-nav-links a:hover { color: var(--teal); }
    .land-nav-cta { display: flex; align-items: center; gap: 14px; }
    .land-nav-link { font-size: 13px; font-weight: 700; color: var(--ink-2); }
    .land-nav-link:hover { color: var(--teal); }

    .hero {
        display: grid;
        grid-template-columns: 1.1fr .9fr;
        gap: 48px;
        padding: 60px max(32px, calc((100% - var(--content-max)) / 2));
        align-items: center;
        background: var(--surface);
    }
    .hero-text { max-width: 600px; }
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 14px; border-radius: 999px;
        background: var(--teal-50); color: var(--teal-600);
        font-size: 12px; font-weight: 800;
        margin-bottom: 20px;
    }
    .hero-eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--teal); }
    .hero-title {
        font-size: 52px; font-weight: 900; line-height: 1.15;
        letter-spacing: -1px; color: var(--navy);
    }
    .hero-lead {
        font-size: 17px; font-weight: 600; color: var(--ink-3);
        margin-top: 18px; line-height: 1.75;
    }
    .hero-actions { display: flex; gap: 12px; margin-top: 28px; flex-wrap: wrap; }
    .hero-stats { display: flex; gap: 36px; margin-top: 40px; padding-top: 24px; border-top: 1px solid var(--line); }
    .hero-stat-n { font-weight: 900; font-size: 24px; color: var(--navy); }
    .hero-stat-l { font-size: 12px; font-weight: 700; color: var(--ink-3); margin-top: 2px; }

    .hero-visual {
        position: relative; display: flex; align-items: center; justify-content: center;
    }
    .hero-map {
        position: relative; width: 100%; aspect-ratio: 5/4;
        background: var(--gray-100);
        border-radius: 28px; overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--line);
    }
    .hero-map-card {
        position: absolute; bottom: 16px; right: 16px; left: 16px;
        background: white; border-radius: 14px; padding: 10px 12px;
        box-shadow: var(--shadow-lg);
        display: flex; gap: 10px; align-items: center;
    }

    /* Sections */
    .land-section {
        padding: 56px max(32px, calc((100% - var(--content-max)) / 2));
        background: var(--gray-50);
    }
    .land-section:nth-of-type(even) { background: var(--surface); }
    .section-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 28px; }
    .section-title { font-size: 28px; font-weight: 900; color: var(--navy); letter-spacing: -.5px; }
    .section-link { color: var(--teal); font-weight: 800; font-size: 13px; }

    /* Categories */
    .cat-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
    .cat-tile {
        background: white; border: 1px solid var(--line); border-radius: 16px;
        padding: 22px 14px; display: flex; flex-direction: column;
        align-items: center; gap: 10px; text-align: center;
        transition: transform .15s, box-shadow .15s, border-color .15s;
    }
    .cat-tile:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: var(--teal-100); }
    .cat-icon {
        width: 48px; height: 48px; border-radius: 14px;
        background: var(--teal-50); display: grid; place-items: center;
    }
    .cat-name { font-weight: 800; font-size: 13px; color: var(--navy); }
    .cat-sub { font-size: 11px; color: var(--ink-3); font-weight: 600; }

    /* Featured grid */
    .feat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
    .feat-card {
        background: white; border: 1px solid var(--line); border-radius: 18px;
        overflow: hidden;
        transition: transform .15s, box-shadow .15s;
        display: flex; flex-direction: column;
    }
    .feat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
    .feat-cover { height: 130px; position: relative; }
    .feat-badge {
        position: absolute; top: 10px; right: 10px;
        background: var(--teal); color: white;
        padding: 3px 10px; border-radius: 999px;
        font-size: 10px; font-weight: 800;
    }
    .feat-body { padding: 14px; flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .feat-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .feat-name { font-weight: 800; font-size: 14px; color: var(--navy); }
    .feat-meta { font-size: 12px; color: var(--ink-3); font-weight: 600; }
    .feat-cta { font-size: 12px; font-weight: 800; color: var(--teal); display: inline-flex; align-items: center; gap: 2px; }

    /* Merchant CTA */
    .merch-cta {
        background: var(--navy);
        border-radius: 28px;
        padding: 56px;
        color: white;
        display: grid;
        grid-template-columns: 1.1fr .9fr;
        gap: 56px;
        position: relative; overflow: hidden;
    }
    .merch-cta::before {
        content: '';
        position: absolute; top: -100px; left: -100px;
        width: 280px; height: 280px; border-radius: 50%;
        background: rgba(13,148,136,.2); filter: blur(60px);
    }
    .merch-text { position: relative; }
    .merch-title { font-size: 32px; font-weight: 900; letter-spacing: -.5px; margin: 16px 0 12px; line-height: 1.3; }
    .merch-lead { font-size: 14px; color: rgba(255,255,255,.75); line-height: 1.8; font-weight: 500; }
    .merch-features { display: flex; flex-direction: column; gap: 10px; margin: 20px 0 28px; }
    .merch-feat { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: rgba(255,255,255,.92); }

    .merch-mock { position: relative; display: flex; flex-direction: column; gap: 14px; justify-content: center; align-items: stretch; }
    .mock-card {
        background: white; color: var(--ink);
        border-radius: 16px; padding: 14px;
        box-shadow: 0 20px 40px rgba(0,0,0,.25);
        position: relative;
    }
    .mock-card-2 {
        align-self: flex-end;
        width: 60%;
    }

    /* Footer */
    .land-foot {
        padding: 28px max(32px, calc((100% - var(--content-max)) / 2));
        display: flex; justify-content: space-between; align-items: center;
        background: var(--surface);
        border-top: 1px solid var(--line);
        color: var(--ink-3);
        font-size: 12px; font-weight: 700;
    }
}

/* ─── LARGE DESKTOP TWEAKS ───────────────────────────────── */
@media (min-width: 1440px) {
    .hero-title { font-size: 60px; }
    .cat-grid { grid-template-columns: repeat(6, 1fr); }
}
</style>
@endpush
