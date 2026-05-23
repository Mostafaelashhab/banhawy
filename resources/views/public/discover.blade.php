@extends('layouts.mobile')

@section('title', 'الرئيسية · بنهاوي')
@section('page-title', 'اكتشف بنها')

@section('content')

{{-- ── HERO STRIP (greeting + search) ───────────────────────── --}}
<div class="disc-hero">
    <div class="disc-hero-inner">
        <div>
            <div class="disc-greet">
                @auth أهلًا، {{ explode(' ', auth()->user()->name)[0] }} 👋 @else أهلًا بك في بنهاوي 👋 @endauth
            </div>
            <div class="disc-sub">إيه اللي بتدور عليه في بنها النهارده؟</div>
        </div>
        <form method="get" action="{{ route('map') }}" class="disc-search">
            <span style="color: var(--ink-4);"><x-icon name="search" :size="18"/></span>
            <input type="text" name="q" placeholder="مطعم · كافيه · دكتور · صالون ...">
            <a href="{{ route('map') }}" class="disc-search-map" aria-label="الخريطة">
                <x-icon name="map" :size="16" stroke="white"/>
            </a>
        </form>
        @if($activeOrder ?? null)
            {{-- Logged in + has an in-flight order → show that specific order --}}
            <a href="{{ route('track', ['code' => $activeOrder->code]) }}" class="disc-track-link disc-track-active">
                <span class="disc-track-pulse"></span>
                <span style="flex: 1;">
                    طلبك <strong style="direction: ltr; display: inline-block;">{{ $activeOrder->code }}</strong>
                    من <strong>{{ $activeOrder->business->name }}</strong>
                    · <span style="color: var(--teal-600);">{{ $activeOrder->statusLabel() }}</span>
                </span>
                <x-icon name="chev-l" :size="12" stroke="#0D9488"/>
            </a>
        @elseif(! auth()->check())
            {{-- Guest → generic entry point so they can still track via code --}}
            <a href="{{ route('track') }}" class="disc-track-link">
                <x-icon name="clock" :size="13" stroke="#0D9488"/>
                <span style="flex: 1;">عندك طلب؟ <strong>تتبّع حالته من هنا</strong></span>
                <x-icon name="chev-l" :size="12" stroke="#0D9488"/>
            </a>
        @endif
        {{-- Logged-in users with no active order → no banner (intentional) --}}
    </div>
</div>

<div class="scroll disc-scroll">

    {{-- ── CATEGORY TILES ───────────────────────────────────── --}}
    <section class="disc-section">
        <div class="disc-section-head">
            <h2 class="disc-section-title">التصنيفات</h2>
        </div>
        <div class="disc-cats">
            @foreach($types as $t)
                <a href="{{ route('search', ['type' => $t->slug]) }}" class="disc-cat">
                    <span class="disc-cat-icon">
                        <x-icon :name="$t->icon" :size="20" stroke="#0D9488"/>
                    </span>
                    <span class="disc-cat-name">{{ $t->name_ar }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ── FEATURED CAROUSEL ────────────────────────────────── --}}
    @if($featured->isNotEmpty())
        <section class="disc-section">
            <div class="disc-section-head">
                <h2 class="disc-section-title">
                    <span class="disc-badge">مميز</span>
                    الأنشطة المختارة
                </h2>
                <a href="{{ route('search') }}" class="disc-section-link">عرض الكل</a>
            </div>
            <div class="disc-rail">
                @foreach($featured as $b)
                    <a href="{{ route('business.show', $b) }}" class="disc-card">
                        <div class="disc-card-cover ph ph-{{ $b->type->slug }}">
                            <span class="disc-card-badge">مميز</span>
                        </div>
                        <div class="disc-card-body">
                            <div class="disc-card-row">
                                <span class="disc-card-name">{{ $b->name }}</span>
                                <span class="stars"><x-icon name="star-f" :size="11"/> {{ number_format($b->rating, 1) }}</span>
                            </div>
                            <div class="disc-card-meta">{{ $b->category }}</div>
                            <div class="disc-card-row" style="margin-top: 8px;">
                                @if($b->isOpenNow())
                                    <span class="chip open" style="padding: 1px 7px; font-size: 10px;">مفتوح</span>
                                @else
                                    <span class="chip closed" style="padding: 1px 7px; font-size: 10px;">مغلق</span>
                                @endif
                                @if($b->delivery)
                                    <span class="label-meta">· توصيل</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── OPEN NOW ─────────────────────────────────────────── --}}
    @if($openNow->isNotEmpty())
        <section class="disc-section">
            <div class="disc-section-head">
                <h2 class="disc-section-title">
                    <span class="disc-pulse"></span>
                    مفتوح الآن
                </h2>
                <a href="{{ route('search', ['filter' => 'open']) }}" class="disc-section-link">المزيد</a>
            </div>
            <div class="disc-list">
                @foreach($openNow->take(4) as $b)
                    <a href="{{ route('business.show', $b) }}" class="disc-row">
                        <div class="ph ph-{{ $b->type->slug }}" style="width: 56px; height: 56px; flex-shrink: 0; font-size: 13px;">
                            {{ mb_substr($b->name, 0, 2) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="label-strong">{{ $b->name }}</span>
                                <span class="stars"><x-icon name="star-f" :size="10"/> {{ number_format($b->rating, 1) }}</span>
                            </div>
                            <div class="label-meta">{{ $b->category }}</div>
                            <div style="display: flex; gap: 6px; margin-top: 4px; align-items: center;">
                                <span class="chip open" style="padding: 1px 7px; font-size: 10px;">مفتوح</span>
                                @if($b->delivery)<span class="label-meta">· توصيل</span>@endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── LATEST ───────────────────────────────────────────── --}}
    @if($latest->isNotEmpty())
        <section class="disc-section">
            <div class="disc-section-head">
                <h2 class="disc-section-title">آخر الإضافات</h2>
                <a href="{{ route('search') }}" class="disc-section-link">عرض الكل</a>
            </div>
            <div class="disc-list">
                @foreach($latest->take(4) as $b)
                    <a href="{{ route('business.show', $b) }}" class="disc-row">
                        <div class="ph ph-{{ $b->type->slug }}" style="width: 56px; height: 56px; flex-shrink: 0; font-size: 13px;">
                            {{ mb_substr($b->name, 0, 2) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="label-strong">{{ $b->name }}</span>
                                @if($b->is_verified)
                                    <span class="chip teal" style="padding: 1px 7px; font-size: 10px;">موثّق</span>
                                @endif
                            </div>
                            <div class="label-meta">{{ $b->category }} · {{ $b->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

  
    {{-- ── MERCHANT CTA (visitors only) ─────────────────────── --}}
    @guest
        <section class="disc-section">
            <div class="disc-merch">
                <div class="disc-merch-text">
                    <span class="disc-merch-eyebrow">لأصحاب الأنشطة</span>
                    <div class="disc-merch-title">عندك نشاط في بنها؟</div>
                    <div class="disc-merch-sub">اعمله صفحة في دقيقتين واستقبل طلباتك على واتساب.</div>
                </div>
                <a href="{{ route('register.step1') }}" class="btn btn-teal" style="padding: 11px 16px; font-size: 13px;">ابدأ مجانًا</a>
            </div>
        </section>
    @endguest
</div>

@include('partials.visitor-nav')
@endsection

@push('head')
<style>
/* ── Hero strip ──────────────────────────────────────────── */
.disc-hero {
    background: var(--surface);
    padding: 16px 14px 18px;
    border-bottom: 1px solid var(--line);
    flex-shrink: 0;
}
.disc-hero-inner { display: flex; flex-direction: column; gap: 14px; }
.disc-greet { font-weight: 900; font-size: 18px; color: var(--navy); letter-spacing: -.3px; }
.disc-sub { font-size: 12px; color: var(--ink-3); font-weight: 600; margin-top: 2px; }
.disc-search {
    background: var(--gray-100);
    border-radius: 14px;
    padding: 10px 12px 10px 4px;
    display: flex; gap: 10px; align-items: center;
}
.disc-search input { flex: 1; border: none; outline: none; background: transparent; font-size: 13px; font-weight: 700; color: var(--ink); }
.disc-search input::placeholder { color: var(--ink-4); font-weight: 600; }
.disc-search-map {
    width: 36px; height: 36px; border-radius: 10px;
    background: var(--teal); color: white;
    display: grid; place-items: center; flex-shrink: 0;
}

/* Contextual active-order banner — only shown to logged-in users with an in-flight order */
.disc-track-link {
    display: flex; align-items: center; gap: 8px;
    margin-top: 10px;
    padding: 10px 12px; border-radius: 12px;
    background: var(--teal-50); color: var(--teal-600);
    font-size: 12px; font-weight: 700;
    border: 1px solid var(--teal-100);
}
.disc-track-link strong { color: var(--navy); font-weight: 800; }
.disc-track-link:hover { background: var(--teal-100); }

.disc-track-pulse {
    width: 8px; height: 8px; border-radius: 50%; background: var(--teal);
    flex-shrink: 0;
    animation: discTrackPulse 1.6s ease-out infinite;
}
@keyframes discTrackPulse {
    0%   { box-shadow: 0 0 0 0 rgba(13,148,136,.6); }
    100% { box-shadow: 0 0 0 10px rgba(13,148,136,0); }
}

/* ── Sections ──────────────────────────────────────────── */
.disc-scroll { background: var(--gray-50); }
.disc-section { padding: 18px 14px 6px; }
.disc-section-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 12px; }
.disc-section-title {
    font-weight: 900; font-size: 15px; color: var(--navy);
    display: flex; align-items: center; gap: 8px;
}
.disc-section-link { font-size: 11px; font-weight: 800; color: var(--teal); }
.disc-badge {
    background: var(--teal); color: white;
    padding: 2px 8px; border-radius: 999px;
    font-size: 10px; font-weight: 800;
}
.disc-pulse {
    width: 8px; height: 8px; border-radius: 50%; background: var(--wa);
    position: relative;
    box-shadow: 0 0 0 4px rgba(37,211,102,.15);
    animation: pulse 1.6s ease-out infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(37,211,102,.4); }
    100% { box-shadow: 0 0 0 8px rgba(37,211,102,0); }
}

/* ── Categories ─────────────────────────────────────────── */
.disc-cats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.disc-cat {
    background: white; border: 1px solid var(--line); border-radius: 14px;
    padding: 14px 8px; display: flex; flex-direction: column;
    align-items: center; gap: 6px; text-align: center;
}
.disc-cat-icon {
    width: 40px; height: 40px; border-radius: 12px;
    background: var(--teal-50); display: grid; place-items: center;
}
.disc-cat-name { font-weight: 800; font-size: 11px; color: var(--navy); line-height: 1.3; }

/* ── Horizontal carousel ─────────────────────────────────── */
.disc-rail {
    display: flex; gap: 10px; overflow-x: auto;
    padding-bottom: 4px;
    margin: 0 -14px; padding-inline: 14px;
    scrollbar-width: none;
}
.disc-rail::-webkit-scrollbar { display: none; }
.disc-card {
    flex-shrink: 0;
    width: 220px;
    background: white; border: 1px solid var(--line); border-radius: 16px;
    overflow: hidden;
}
.disc-card-cover { height: 110px; position: relative; }
.disc-card-badge {
    position: absolute; top: 8px; right: 8px;
    background: var(--teal); color: white;
    padding: 2px 8px; border-radius: 999px;
    font-size: 9px; font-weight: 800;
}
.disc-card-body { padding: 10px 12px 12px; }
.disc-card-row { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
.disc-card-name { font-weight: 800; font-size: 13px; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.disc-card-meta { font-size: 11px; color: var(--ink-3); font-weight: 600; margin-top: 2px; }

/* ── Vertical list rows ──────────────────────────────────── */
.disc-list { display: flex; flex-direction: column; gap: 8px; }
.disc-row {
    background: white; border: 1px solid var(--line); border-radius: 14px;
    padding: 10px;
    display: flex; gap: 12px; align-items: center;
}

/* ── Map teaser ──────────────────────────────────────────── */
.disc-map-teaser {
    position: relative;
    display: block;
    height: 140px; border-radius: 18px; overflow: hidden;
    box-shadow: var(--shadow);
}
.disc-map-overlay { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(0,27,42,.55), rgba(0,27,42,.85)); }
.disc-map-content {
    position: relative; height: 100%;
    display: flex; align-items: center; gap: 14px;
    padding: 0 18px; color: white;
}
.disc-map-icon {
    width: 48px; height: 48px; border-radius: 14px;
    background: rgba(13,148,136,.4); backdrop-filter: blur(8px);
    display: grid; place-items: center;
    flex-shrink: 0;
}
.disc-map-title { font-weight: 900; font-size: 15px; }
.disc-map-sub { font-size: 11px; opacity: .8; font-weight: 600; margin-top: 2px; }
.disc-map-cta {
    display: inline-flex; align-items: center; gap: 4px;
    background: rgba(255,255,255,.15); backdrop-filter: blur(8px);
    padding: 8px 12px; border-radius: 10px;
    font-size: 12px; font-weight: 800; margin-right: auto;
}

/* ── Merchant CTA inline ─────────────────────────────────── */
.disc-merch {
    background: var(--navy); color: white;
    border-radius: 18px; padding: 16px;
    display: flex; align-items: center; gap: 12px;
    position: relative; overflow: hidden;
}
.disc-merch::before {
    content: ''; position: absolute;
    top: -40px; left: -40px;
    width: 140px; height: 140px; border-radius: 50%;
    background: rgba(13,148,136,.2); filter: blur(40px);
    pointer-events: none;
}
.disc-merch-text { flex: 1; position: relative; }
.disc-merch .btn { position: relative; }
.disc-merch-eyebrow {
    font-size: 10px; font-weight: 800; color: var(--teal);
    text-transform: uppercase; letter-spacing: .5px;
}
.disc-merch-title { font-weight: 900; font-size: 15px; margin-top: 4px; }
.disc-merch-sub { font-size: 11px; color: rgba(255,255,255,.7); font-weight: 600; margin-top: 4px; line-height: 1.6; }


/* ═══ DESKTOP ≥ 1024px ════════════════════════════════════ */
@media (min-width: 1024px) {
    .disc-hero {
        padding: 28px max(32px, calc((100% - var(--content-max)) / 2));
        background: var(--surface);
    }
    .disc-hero-inner {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        max-width: var(--content-max);
        margin: 0 auto;
    }
    .disc-greet { font-size: 24px; }
    .disc-sub { font-size: 13px; }
    .disc-search { width: 440px; padding: 12px 14px 12px 6px; }
    .disc-search input { font-size: 14px; }

    .disc-section {
        padding-left: max(32px, calc((100% - var(--content-max)) / 2));
        padding-right: max(32px, calc((100% - var(--content-max)) / 2));
        padding-top: 32px;
        padding-bottom: 8px;
    }
    .disc-section-title { font-size: 20px; letter-spacing: -.3px; }
    .disc-section-link { font-size: 13px; }

    .disc-cats { grid-template-columns: repeat(6, 1fr); gap: 14px; }
    .disc-cat { padding: 20px 12px; gap: 10px; }
    .disc-cat-icon { width: 48px; height: 48px; }
    .disc-cat-name { font-size: 13px; }

    /* Featured grid (not horizontal scroll on desktop) */
    .disc-rail {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        overflow: visible;
        margin: 0; padding: 0;
        gap: 16px;
    }
    .disc-card { width: auto; }
    .disc-card-cover { height: 140px; }
    .disc-card-body { padding: 14px; }
    .disc-card-name { font-size: 14px; }

    /* Lists become 2-column on desktop */
    .disc-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .disc-row { padding: 14px; }

    /* Map teaser bigger */
    .disc-map-teaser { height: 200px; }
    .disc-map-title { font-size: 18px; }
    .disc-map-sub { font-size: 13px; }

    /* Merchant CTA inline more spacious */
    .disc-merch { padding: 28px; }
    .disc-merch-title { font-size: 20px; }
    .disc-merch-sub { font-size: 13px; }
}

@media (min-width: 1440px) {
    .disc-rail { grid-template-columns: repeat(4, 1fr); }
    .disc-list { grid-template-columns: repeat(3, 1fr); }
}
</style>
@endpush
