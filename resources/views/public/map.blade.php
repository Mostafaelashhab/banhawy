@extends('layouts.mobile')

@section('title', ($type ? $type->name_ar . ' · ' : '') . 'الخريطة · بنهاوي')
@section('page-title', $type?->name_ar ?? 'الخريطة')

@section('content')
{{-- Search bar — submits back to the map so results stay on the map --}}
<div style="padding: 6px 14px 8px;">
    <form action="{{ route('map') }}" method="get" id="map-form" autocomplete="off">
        @if($type)<input type="hidden" name="type" value="{{ $type->slug }}">@endif
        @if($openOnly)<input type="hidden" name="open" value="1">@endif
        <label class="field" style="cursor: text;">
            <span style="color: var(--ink-4);"><x-icon name="search" :size="18"/></span>
            <input type="search" name="q" id="map-search" value="{{ $q }}" placeholder="بتدور على إيه في بنها؟" inputmode="search">
            <span class="tiny" id="map-count" style="color: var(--ink-3);"></span>
        </label>
    </form>
</div>

{{-- Filter chips — link back to /map with ?type, stays on the map --}}
@php
    // Carry q + type + open across chip links so filters stack
    $baseParams = array_filter([
        'q'    => $q ?: null,
        'type' => $type?->slug,
        'open' => $openOnly ? 1 : null,
    ]);
@endphp
<div class="chip-scroll-wrap" style="padding: 0 0 8px;">
    <div class="chip-scroll" style="padding: 0 14px;">
        @if($q !== '')
            {{-- Active search-query chip — clearing it removes ?q from the URL --}}
            <a href="{{ route('map', collect($baseParams)->except('q')->all()) }}"
               class="chip active" style="gap: 6px;">
                <x-icon name="search" :size="12" stroke="white"/>
                {{ $q }}
                <span aria-label="إزالة البحث" style="opacity: .7; font-weight: 900;">×</span>
            </a>
        @endif

        <a href="{{ route('map', array_filter(['q' => $q ?: null, 'type' => $type?->slug, 'open' => $openOnly ? null : 1])) }}"
           class="chip @if($openOnly) active @endif">
            <x-icon name="flag" :size="12" :stroke="$openOnly ? 'white' : 'currentColor'"/>
            مفتوح الآن
        </a>

        @if($type)
            <a href="{{ route('map', collect($baseParams)->except('type')->all()) }}"
               class="chip active" style="gap: 6px;">
                <x-icon :name="$type->icon" :size="12" stroke="white"/>
                {{ $type->name_ar }}
                <span aria-label="إزالة الفلتر" style="opacity: .7; font-weight: 900;">×</span>
            </a>
        @endif

        @foreach($types as $t)
            @continue($type && $type->id === $t->id)
            <a href="{{ route('map', array_filter(['q' => $q ?: null, 'type' => $t->slug, 'open' => $openOnly ? 1 : null])) }}" class="chip">
                <x-icon :name="$t->icon" :size="12"/> {{ $t->name_ar }}
            </a>
        @endforeach
    </div>
</div>

{{-- Map --}}
<div style="flex: 1; position: relative; overflow: hidden; min-height: 320px;">
    <div id="map-root" style="position: absolute; inset: 0; background: #EEF1F4;"></div>

    {{-- Locate button (overlay on map) --}}
    <button id="locate-btn" aria-label="موقعي"
            style="position: absolute; top: 12px; left: 12px; z-index: 500;
                   width: 38px; height: 38px; border-radius: 12px; background: white;
                   border: 1px solid var(--line-2); display: grid; place-items: center;
                   color: var(--navy); box-shadow: var(--shadow);">
        <x-icon name="pin" :size="18" stroke="#001B2A"/>
    </button>

    {{-- Empty-state banner when no businesses match the filter --}}
    @if($businesses->isEmpty())
        <div style="position: absolute; top: 60px; right: 14px; left: 14px; z-index: 500;
                    background: white; border-radius: 14px; padding: 14px;
                    box-shadow: var(--shadow); text-align: center;">
            <div class="label-strong">لا توجد نتائج بالفلتر الحالي</div>
            <div class="label-meta" style="margin-top: 4px;">جرّب فلتر تاني أو شيل الفلتر.</div>
        </div>
    @endif

    {{-- Realtime-search empty-state (shown by JS when text filter wipes all markers) --}}
    <div id="map-empty" style="display: none; position: absolute; top: 60px; right: 14px; left: 14px; z-index: 500;
                background: white; border-radius: 14px; padding: 14px;
                box-shadow: var(--shadow); text-align: center;">
        <div class="label-strong">مفيش نتائج تطابق بحثك</div>
        <div class="label-meta" style="margin-top: 4px;">جرّب كلمة تانية أو شيل الفلتر.</div>
    </div>

    {{-- Floating card — populated dynamically when a marker is tapped --}}
    @if($businesses->isNotEmpty())
        <a id="map-card" href="#"
           style="position: fixed; right: 12px; left: 12px; bottom: calc(76px + env(safe-area-inset-bottom)); background: white; border-radius: 16px; padding: 10px; box-shadow: 0 12px 32px -8px rgba(0,27,42,.25), 0 4px 12px -4px rgba(0,27,42,.12); display: flex; gap: 10px; align-items: center; z-index: 500;">
            <div id="map-card-thumb" class="ph" style="width: 52px; height: 52px; border-radius: 12px; flex-shrink: 0; font-size: 13px;"></div>
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                    <span id="map-card-name" class="label-strong" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></span>
                    <span class="stars" style="flex-shrink: 0;">
                        <x-icon name="star-f" :size="11"/>
                        <span id="map-card-rating"></span>
                    </span>
                </div>
                <div style="display: flex; gap: 6px; margin-top: 4px; align-items: center; flex-wrap: nowrap; overflow: hidden;">
                    <span id="map-card-status" class="chip" style="padding: 2px 7px; font-size: 10px;"></span>
                    <span id="map-card-cat" class="label-meta" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"></span>
                </div>
            </div>
            <span class="btn btn-navy" style="flex-shrink: 0; padding: 8px 12px; font-size: 11px; border-radius: 10px;">
                عرض
                <x-icon name="chev-l" :size="12" stroke="white"/>
            </span>
        </a>
    @endif
</div>

@include('partials.visitor-nav')
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
#map-root { font-family: 'Cairo', sans-serif; }
.leaflet-container { background: #EEF1F4; }
.leaflet-tile-pane { filter: saturate(.85); }

.banhawy-pin {
    background: transparent !important;
    border: none !important;
}
.banhawy-pin svg {
    filter: drop-shadow(0 4px 6px rgba(0,27,42,.25));
}

.leaflet-popup-content-wrapper {
    border-radius: 14px;
    box-shadow: 0 12px 32px rgba(0,27,42,.15);
    padding: 4px;
}
.leaflet-popup-content {
    margin: 8px 12px;
    font-family: 'Cairo', sans-serif;
    direction: rtl;
}
.leaflet-popup-tip { box-shadow: 0 3px 8px rgba(0,27,42,.1); }
.leaflet-control-attribution {
    font-size: 9px !important;
    background: rgba(255,255,255,.7) !important;
}

.banhawy-popup .label-strong { font-weight: 800; font-size: 13px; color: var(--navy); }
.banhawy-popup .meta { font-size: 11px; color: var(--ink-3); font-weight: 600; margin-top: 2px; }
.banhawy-popup a.cta {
    display: inline-block; margin-top: 8px;
    background: var(--navy); color: white;
    border-radius: 8px; padding: 5px 10px;
    font-size: 11px; font-weight: 800;
    text-decoration: none;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var el = document.getElementById('map-root');
    if (!el || typeof L === 'undefined') return;

    var businesses = {!! $businessesJson !!};

    // Banha town centre — also our fallback when there are no businesses
    var banhaCentre = [30.4582, 31.1797];

    var map = L.map('map-root', {
        zoomControl: false,
        attributionControl: false, // hide the bottom "Leaflet | © OpenStreetMap" credit
        scrollWheelZoom: true,
    }).setView(banhaCentre, 14);

    // OSM tiles bring real street names + roads
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    function pinSvg(color, size) {
        size = size || 32;
        var w = size, h = Math.round(size * 32 / 28);
        return '<svg viewBox="0 0 28 32" width="' + w + '" height="' + h + '">' +
               '<path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="' + color + '"/>' +
               '<circle cx="14" cy="14" r="5" fill="white"/></svg>';
    }

    // ── Bottom card — dynamic ──────────────────────────────
    var card       = document.getElementById('map-card');
    var cardThumb  = document.getElementById('map-card-thumb');
    var cardName   = document.getElementById('map-card-name');
    var cardRating = document.getElementById('map-card-rating');
    var cardStatus = document.getElementById('map-card-status');
    var cardCat    = document.getElementById('map-card-cat');

    function showInCard(b) {
        if (!card) return;
        card.href = b.url;
        cardThumb.className = 'ph ph-' + b.type;
        cardThumb.textContent = b.name.substring(0, 2);
        cardName.textContent = b.name;
        cardRating.textContent = ' ' + b.rating.toFixed(1);
        cardCat.textContent = b.cat;
        cardStatus.textContent = b.open ? 'مفتوح' : 'مغلق';
        cardStatus.className = 'chip ' + (b.open ? 'open' : 'closed');
    }

    var markerEntries = [];   // { marker, biz, hay, visible }
    businesses.forEach(function (b) {
        var color = b.featured ? '#0D9488' : (b.open ? '#1E3A8A' : '#001B2A');
        var size  = b.featured ? 36 : 28;
        var icon  = L.divIcon({
            className: 'banhawy-pin',
            html: pinSvg(color, size),
            iconSize:    [size, Math.round(size * 32 / 28)],
            iconAnchor:  [size / 2, Math.round(size * 32 / 28)],
            popupAnchor: [0, -Math.round(size * 32 / 28)],
        });
        var marker = L.marker([b.lat, b.lng], { icon: icon }).addTo(map);
        marker.bindPopup(
            '<div class="banhawy-popup">' +
                '<div class="label-strong">' + b.name + '</div>' +
                '<div class="meta">' + b.cat + ' · ★ ' + b.rating.toFixed(1) + (b.open ? ' · مفتوح' : ' · مغلق') + '</div>' +
                '<a class="cta" href="' + b.url + '">عرض الصفحة</a>' +
            '</div>',
            { offset: [0, -8] }
        );
        marker.on('click', function () { showInCard(b); });
        markerEntries.push({
            marker: marker,
            biz: b,
            hay: ((b.name || '') + ' ' + (b.cat || '')).toLowerCase(),
            visible: true,
        });
    });

    // Seed the card with the first business (featured if any, else first)
    if (businesses.length) {
        var seed = businesses.find(function (b) { return b.featured; }) || businesses[0];
        showInCard(seed);
    }

    // Fit the map to the visible markers
    function fitToVisible() {
        var visible = markerEntries.filter(function (e) { return e.visible; }).map(function (e) { return e.marker; });
        if (visible.length === 0) return;
        var group = L.featureGroup(visible);
        map.fitBounds(group.getBounds().pad(0.25), { maxZoom: 16 });
    }
    fitToVisible();

    // ── Realtime search: filter markers as the user types ──
    function normalise(s) {
        return (s || '').toString().trim().toLowerCase()
            .replace(/[ً-ْٰ]/g, '')
            .replace(/[إأآا]/g, 'ا')
            .replace(/ى/g, 'ي')
            .replace(/ة/g, 'ه');
    }

    var mapSearch  = document.getElementById('map-search');
    var mapForm    = document.getElementById('map-form');
    var mapCount   = document.getElementById('map-count');
    var mapEmpty   = document.getElementById('map-empty');
    var filterTimer = null;

    function applyFilter() {
        var q = normalise(mapSearch ? mapSearch.value : '');
        var shown = 0;
        markerEntries.forEach(function (e) {
            var match = q === '' || normalise(e.hay).indexOf(q) !== -1;
            if (match) {
                if (!e.visible) { e.marker.addTo(map); e.visible = true; }
                shown++;
            } else if (e.visible) {
                map.removeLayer(e.marker);
                e.visible = false;
            }
        });

        if (mapCount) mapCount.textContent = q ? (shown + ' نتيجة') : '';

        // Show/hide the "no results" panel
        if (mapEmpty) mapEmpty.style.display = (q && shown === 0) ? 'block' : 'none';

        // Hide the bottom card if its business got filtered out
        if (card && q !== '') {
            var seed = markerEntries.find(function (e) { return e.visible; });
            if (seed) showInCard(seed.biz);
        }
    }

    if (mapForm) mapForm.addEventListener('submit', function (e) { e.preventDefault(); });

    if (mapSearch) {
        mapSearch.addEventListener('input', function () {
            applyFilter();
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function () {
                var url = new URL(window.location.href);
                if (mapSearch.value.trim() === '') url.searchParams.delete('q');
                else url.searchParams.set('q', mapSearch.value);
                window.history.replaceState(null, '', url);

                // Re-fit map after the user finishes typing
                fitToVisible();
            }, 400);
        });

        // Apply once on load if there's a pre-filled value
        if (mapSearch.value) applyFilter();
    }

    // Locate button → ask the device, pan there, drop a pulsing dot
    var userMarker = null;
    document.getElementById('locate-btn').addEventListener('click', function () {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(function (pos) {
            var ll = [pos.coords.latitude, pos.coords.longitude];
            if (userMarker) userMarker.remove();
            userMarker = L.circleMarker(ll, {
                radius: 7, color: '#1E3A8A', weight: 3, fillColor: '#1E3A8A', fillOpacity: 1
            }).addTo(map);
            map.setView(ll, 15);
        });
    });
})();
</script>
@endpush
