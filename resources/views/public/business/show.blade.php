@extends('layouts.mobile')

@section('title', $business->name . ' · بنهاوي')
@section('page-title', $business->name)

@section('content')
@php
    $typeSlug = $business->type->slug;
    $orderableTypes = ['restaurant', 'shop'];
    $canOrder = in_array($typeSlug, $orderableTypes) && ($business->acceptsWebOrders() || $business->acceptsWhatsappOrders());
    $canBook  = $business->acceptsWebBookings() || $business->acceptsWhatsappBookings();
    $walkinOnly = $business->isWalkinOnly();
    $hasMenu  = $canOrder;
    $showPriceRange = in_array($typeSlug, ['restaurant', 'shop', 'salon']);
    $menuLabel  = ['restaurant' => 'المنيو', 'shop' => 'المنتجات'][$typeSlug] ?? 'المنتجات';
    $orderLabel = 'اطلب الآن';
    $bookLabel  = ['clinic' => 'احجز كشف', 'salon' => 'احجز موعد', 'education' => 'احجز', 'restaurant' => 'احجز طاولة', 'service' => 'اطلب خدمة'][$typeSlug] ?? 'احجز';
    $bookUrl    = $business->acceptsWebBookings()
        ? route('business.book.form', $business)
        : route('business.whatsapp', ['business' => $business, 'message' => 'مرحبًا، حابب أحجز']);
@endphp
<div class="biz">

    {{-- ── HERO (cover + name) ─────────────────────────────── --}}
    <div class="biz-cover ph ph-{{ $business->type->slug }}">
        <div class="biz-cover-overlay"></div>
        <div class="biz-cover-actions">
            <a href="{{ route('home') }}" class="biz-icon-btn" aria-label="رجوع للرئيسية"><x-icon name="chev-r" :size="18" stroke="white"/></a>
            <div style="display: flex; gap: 8px;">
                <button class="biz-icon-btn" onclick="navigator.share ? navigator.share({title: '{{ $business->name }}', url: window.location.href}) : navigator.clipboard.writeText(window.location.href)">
                    <x-icon name="share" :size="16" stroke="white"/>
                </button>
                @auth
                    @php $isFav = auth()->user()->hasFavorited($business); @endphp
                    <form method="post" action="{{ route('favorites.toggle', $business) }}" style="display: contents;">
                        @csrf
                        <button class="biz-icon-btn @if($isFav) is-fav @endif" aria-label="{{ $isFav ? 'إزالة من المفضلة' : 'أضف للمفضلة' }}">
                            @if($isFav)
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="#FF4D6D" stroke="#FF4D6D" stroke-width="1.5">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                            @else
                                <x-icon name="heart" :size="16" stroke="white"/>
                            @endif
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="biz-icon-btn" aria-label="سجّل دخولك للحفظ">
                        <x-icon name="heart" :size="16" stroke="white"/>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="biz-head">
        <div class="biz-logo">
            <div class="ph ph-{{ $business->type->slug }}" style="width: 100%; height: 100%; border-radius: 14px; font-size: 18px;">
                {{ mb_substr($business->name, 0, 2) }}
            </div>
        </div>
        <div class="biz-id">
            <div class="biz-name">{{ $business->name }}</div>
            <div class="label-meta">{{ $business->category }}</div>
            <div class="biz-meta">
                <span class="stars" style="font-size: 12px;">
                    <x-icon name="star-f" :size="12"/> {{ number_format($business->rating, 1) }}
                    <span style="color: var(--ink-4); font-weight: 600;">({{ $business->reviews_count }})</span>
                </span>
                <span class="dot"></span>
                @if($business->isOpenNow())
                    <span class="chip open" style="padding: 2px 7px; font-size: 10px;">مفتوح الآن</span>
                @else
                    <span class="chip closed" style="padding: 2px 7px; font-size: 10px;">مغلق</span>
                @endif
                @if($business->is_verified)
                    <span class="chip teal" style="padding: 2px 7px; font-size: 10px;">موثّق</span>
                @endif
            </div>
        </div>

        {{-- Desktop-only inline CTAs --}}
        <div class="biz-head-cta">
            @if($canOrder)
                <a href="{{ route('business.menu', $business) }}" class="btn btn-wa" style="padding: 12px 18px;">
                    <x-icon name="cart" :size="14" stroke="white"/> {{ $orderLabel }}
                </a>
            @endif
            @if($canBook)
                <a href="{{ $bookUrl }}" class="btn btn-navy" style="padding: 12px 18px;">{{ $bookLabel }}</a>
            @endif
            @if($walkinOnly && ! $canOrder)
                <span class="chip teal" style="padding: 8px 14px; font-size: 12px;">بدون حجز — تواجد مباشر</span>
            @endif
        </div>
    </div>

    {{-- ── TABS ─────────────────────────────────────────────── --}}
    <div class="biz-tabs">
        <span class="biz-tab is-active">الرئيسية</span>
        @if($hasMenu)
            <a href="{{ route('business.menu', $business) }}" class="biz-tab">{{ $menuLabel }}</a>
        @endif
        <span class="biz-tab">التقييمات</span>
    </div>

    {{-- ── BODY (mobile single column, desktop 2-column) ───── --}}
    <div class="biz-body">

        <div class="biz-main">
            {{-- Quick actions --}}
            <div class="biz-quick">
                <a href="tel:{{ $business->phone ?? $business->whatsapp }}" class="btn btn-line biz-quick-btn">
                    <span style="color: var(--teal);"><x-icon name="phone" :size="18" stroke="#0D9488"/></span>
                    اتصل
                </a>
                <a href="{{ route('business.whatsapp', $business) }}" class="btn btn-line biz-quick-btn">
                    <span style="color: var(--wa);"><x-icon name="whatsapp" :size="18" stroke="#25D366"/></span>
                    واتساب
                </a>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}" target="_blank" class="btn btn-line biz-quick-btn">
                    <span style="color: var(--navy);"><x-icon name="directions" :size="18" stroke="#001B2A"/></span>
                    الاتجاهات
                </a>
            </div>

            @if($business->description)
                <div class="card card-pad" style="margin-top: 14px;">
                    <div class="label-strong" style="margin-bottom: 6px;">عن النشاط</div>
                    <p class="muted" style="line-height: 1.75;">{{ $business->description }}</p>
                </div>
            @endif
        </div>

        <aside class="biz-side">
            <div class="card biz-info">
                <div class="biz-info-row">
                    <span class="biz-info-ico"><x-icon name="pin" :size="16" stroke="#0D9488"/></span>
                    <div>
                        <div style="font-weight: 800; font-size: 13px;">{{ $business->address }}</div>
                        <div class="label-meta">{{ $business->lat }}, {{ $business->lng }}</div>
                    </div>
                </div>
                <div class="biz-info-sep"></div>
                <div class="biz-info-row">
                    <span class="biz-info-ico"><x-icon name="clock" :size="16" stroke="#0D9488"/></span>
                    <div style="flex: 1;">
                        @php $today = $business->hours[(int) now()->format('w')] ?? null; @endphp
                        @if($today && empty($today['closed']))
                            <div style="font-weight: 800; font-size: 13px;">يفتح اليوم @time12($today['open']) – @time12($today['close'])</div>
                        @else
                            <div style="font-weight: 800; font-size: 13px;">مغلق اليوم</div>
                        @endif
                        <div class="label-meta">المواعيد قابلة للتغيير في الأعياد</div>
                    </div>
                    @if($business->isOpenNow())
                        <span class="chip open" style="padding: 2px 7px; font-size: 10px;">مفتوح</span>
                    @endif
                </div>
                @if($showPriceRange)
                    <div class="biz-info-sep"></div>
                    <div class="biz-info-row">
                        <span class="biz-info-ico"><x-icon name="tag" :size="16" stroke="#0D9488"/></span>
                        <div>
                            <div style="font-weight: 800; font-size: 13px;">
                                {{ ['low' => 'أسعار اقتصادية', 'medium' => 'نطاق سعري متوسط', 'high' => 'نطاق سعري مرتفع'][$business->price_range] }}
                            </div>
                            <div class="label-meta">
                                @if($business->delivery) توصيل متاح @else استلام من المحل @endif
                            </div>
                        </div>
                    </div>
                @endif
                @if($walkinOnly)
                    <div class="biz-info-sep"></div>
                    <div class="biz-info-row">
                        <span class="biz-info-ico"><x-icon name="pin" :size="16" stroke="#0D9488"/></span>
                        <div>
                            <div style="font-weight: 800; font-size: 13px;">بدون حجز — تواجد مباشر</div>
                            <div class="label-meta">تعالى في مواعيد العمل بدون موعد مسبق.</div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Mini map preview (Leaflet + OSM — real streets) --}}
            <div class="card biz-mini-map">
                <div id="biz-mini-map"
                     data-lat="{{ $business->lat }}"
                     data-lng="{{ $business->lng }}"
                     data-name="{{ $business->name }}"
                     style="position: absolute; inset: 0; background: #EEF1F4;"></div>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}" target="_blank" class="biz-mini-map-cta">
                    <x-icon name="directions" :size="14"/> الاتجاهات
                </a>
            </div>
        </aside>
    </div>

    {{-- ── STICKY CTA (mobile only) ────────────────────────── --}}
    @if($canOrder || $canBook || $walkinOnly)
        <div class="biz-sticky-cta">
            @if($canOrder)
                <a href="{{ route('business.menu', $business) }}" class="btn btn-wa" style="flex: 2; padding: 12px; font-size: 13px;">
                    <x-icon name="cart" :size="14" stroke="white"/> {{ $orderLabel }}
                </a>
            @endif
            @if($canBook)
                <a href="{{ $bookUrl }}" class="btn btn-navy" style="flex: {{ $canOrder ? '1.2' : '1' }}; padding: 12px; font-size: 13px;">{{ $bookLabel }}</a>
            @endif
            @if($walkinOnly && ! $canOrder)
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $business->lat }},{{ $business->lng }}" target="_blank" class="btn btn-navy" style="flex: 1; padding: 12px; font-size: 13px;">
                    <x-icon name="directions" :size="14" stroke="white"/> اعرف الطريق
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
.biz { display: flex; flex-direction: column; flex: 1; }

#biz-mini-map .leaflet-container { background: #EEF1F4; font-family: 'Cairo', sans-serif; }
#biz-mini-map .leaflet-tile-pane  { filter: saturate(.85); }
.banhawy-pin { background: transparent !important; border: none !important; }
.banhawy-pin svg { filter: drop-shadow(0 4px 6px rgba(0,27,42,.25)); }

.biz-cover {
    position: relative;
    height: 170px;
    flex-shrink: 0;
}
.biz-cover-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,27,42,0) 30%, rgba(0,27,42,.55) 100%);
}
.biz-cover-actions {
    position: absolute; top: 14px; right: 14px; left: 14px;
    display: flex; justify-content: space-between;
}
.biz-icon-btn {
    width: 36px; height: 36px; border-radius: 12px;
    background: rgba(255,255,255,.18); backdrop-filter: blur(8px);
    display: grid; place-items: center; color: white; border: none;
}

.biz-head {
    padding: 12px 14px 0;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    flex-wrap: wrap;
}
.biz-logo {
    width: 70px; height: 70px;
    border-radius: 18px;
    background: white;
    padding: 4px;
    box-shadow: var(--shadow);
    flex-shrink: 0;
    margin-top: -48px; /* only logo overlaps the cover */
    position: relative;
}
.biz-id { flex: 1; min-width: 0; padding-top: 4px; }
.biz-name { font-weight: 900; font-size: 18px; }
.biz-meta { display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; }
.biz-head-cta { display: none; }

.biz-tabs {
    margin-top: 14px;
    border-bottom: 1px solid var(--line);
    padding: 0 14px;
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scrollbar-width: none;
    flex-shrink: 0;
}
.biz-tabs::-webkit-scrollbar { display: none; }
.biz-tab {
    padding: 10px 0;
    font-size: 12px;
    font-weight: 700;
    color: var(--ink-3);
    white-space: nowrap;
}
.biz-tab.is-active {
    color: var(--teal);
    border-bottom: 2px solid var(--teal);
    margin-bottom: -1px;
    font-weight: 800;
}

.biz-body {
    padding: 14px;
    padding-bottom: 90px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    flex: 1;
}

.biz-quick { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.biz-quick-btn { flex-direction: column; padding: 12px 6px; gap: 4px; font-size: 11px; }

.biz-info { padding: 12px; }
.biz-info-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
.biz-info-ico { color: var(--teal); flex-shrink: 0; }
.biz-info-sep { height: 1px; background: var(--line); }

.biz-mini-map {
    position: relative;
    height: 180px;
    overflow: hidden;
}
.biz-mini-map-cta {
    position: absolute;
    bottom: 12px; right: 12px; left: 12px;
    background: white;
    border-radius: 10px;
    padding: 8px;
    text-align: center;
    font-size: 12px; font-weight: 800;
    box-shadow: var(--shadow);
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}

.biz-sticky-cta {
    position: sticky;
    bottom: 0;
    padding: 10px 14px 14px;
    background: white;
    border-top: 1px solid var(--line);
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

/* ─── DESKTOP ≥ 1024px ─────────────────────────────────────── */
@media (min-width: 1024px) {
    .biz-cover {
        height: 240px;
        border-radius: 0 0 24px 24px;
        margin-bottom: 0;
    }
    .biz-cover-actions { display: none; } /* topbar handles nav on desktop */

    /* Name moves OUT of cover overlap - sits cleanly below */
    .biz-head {
        max-width: var(--content-max);
        margin: 24px auto 0;
        padding: 0 32px;
        align-items: center;
        gap: 20px;
    }
    .biz-logo {
        width: 100px; height: 100px;
        border-radius: 24px;
        margin-top: -70px; /* lift over cover edge cleanly, but only 30px overlap */
        position: relative;
        z-index: 2;
    }
    .biz-id { padding-bottom: 0; }
    .biz-name { font-size: 28px; letter-spacing: -.5px; }
    .biz-id .label-meta { font-size: 13px; }
    .biz-meta { margin-top: 10px; }

    .biz-head-cta {
        display: flex;
        gap: 10px;
        margin-right: auto; /* RTL: push to far end */
    }

    .biz-tabs {
        max-width: var(--content-max);
        margin: 24px auto 0;
        padding: 0 32px;
        gap: 28px;
    }
    .biz-tab { padding: 14px 0; font-size: 14px; }

    .biz-body {
        max-width: var(--content-max);
        margin: 0 auto;
        padding: 28px 32px 48px;
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 28px;
        align-items: start;
    }

    .biz-main { display: flex; flex-direction: column; gap: 16px; }
    .biz-side { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 88px; }

    .biz-quick { grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .biz-quick-btn { padding: 16px; font-size: 13px; }

    /* Hide mobile sticky CTA on desktop */
    .biz-sticky-cta { display: none; }

    .biz-mini-map { height: 220px; }
}

@media (min-width: 1440px) {
    .biz-cover { height: 280px; }
    .biz-name { font-size: 32px; }
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var el = document.getElementById('biz-mini-map');
    if (!el || typeof L === 'undefined') return;

    var lat  = parseFloat(el.dataset.lat);
    var lng  = parseFloat(el.dataset.lng);
    var name = el.dataset.name || '';
    if (isNaN(lat) || isNaN(lng)) return;

    // Compact, non-interactive map: drag/scroll are fine, but no zoom UI clutter
    var map = L.map(el, {
        zoomControl: false,
        attributionControl: false,
        scrollWheelZoom: false,
        dragging: true,
        doubleClickZoom: false,
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    var icon = L.divIcon({
        className: 'banhawy-pin',
        html: '<svg viewBox="0 0 28 32" width="34" height="38">' +
              '<path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#0D9488"/>' +
              '<circle cx="14" cy="14" r="5" fill="white"/></svg>',
        iconSize:   [34, 38],
        iconAnchor: [17, 38],
    });
    L.marker([lat, lng], { icon: icon, title: name }).addTo(map);
})();
</script>
@endpush
