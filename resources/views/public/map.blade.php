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

{{-- ── Road alert filter chips ─────────────────────────────── --}}
<div class="chip-scroll-wrap" style="padding: 0 0 6px;">
    <div class="chip-scroll" style="padding: 0 14px;">
        <button type="button" class="chip alert-chip active" data-alert-type="all">
            <x-icon name="pin" :size="12"/> الكل
        </button>
        @foreach($alertTypes as $at)
            <button type="button" class="chip alert-chip" data-alert-type="{{ $at['slug'] }}"
                    style="border-color: {{ $at['color'] }}40;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $at['color'] }};"></span>
                {{ $at['label'] }}
            </button>
        @endforeach
    </div>
</div>

{{-- Business filter chips — link back to /map with ?type --}}
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

    {{-- ── Top-right action stack: report alert + navigate ── --}}
    <div style="position: absolute; top: 12px; right: 12px; z-index: 600;
                display: flex; flex-direction: column; gap: 8px; align-items: flex-end;">

        <button id="alert-report-btn" type="button" class="map-fab map-fab-navy">
            <span class="map-fab-ico" style="background: var(--teal);">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="white" stroke-width="2.6" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </span>
            بلّغ عن تنبيه
        </button>

        <button id="nav-start-btn" type="button" class="map-fab map-fab-teal">
            <span class="map-fab-ico" style="background: rgba(255,255,255,.18);">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                </svg>
            </span>
            حدد وجهتك
        </button>

        <button id="nav-saved-btn" type="button" class="map-fab" style="background: white; color: var(--navy);">
            <span class="map-fab-ico" style="background: rgba(245,158,11,.16); color: #B45309;">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15 9 22 9 17 14 19 21 12 17 5 21 7 14 2 9 9 9 12 2"/>
                </svg>
            </span>
            وجهاتي
        </button>
    </div>

    {{-- ── Saved destinations sheet ───────────────────────────── --}}
    <div id="nav-saved-sheet" hidden
         style="position: fixed; inset: 0; z-index: 1100;
                background: rgba(0,27,42,.55);
                display: flex; align-items: flex-end; justify-content: center;
                backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
        <div style="background: white; width: 100%; max-width: 480px; border-radius: 22px 22px 0 0; padding: 18px 18px 24px;
                    max-height: 75vh; overflow-y: auto;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                <div style="font-weight: 900; font-size: 16px; color: var(--navy);">وجهاتي</div>
                <button type="button" id="nav-saved-close" style="border: none; background: var(--gray-100); width: 32px; height: 32px; border-radius: 10px; font-weight: 900; color: var(--ink-2);">×</button>
            </div>

            <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 800; margin-bottom: 8px;">المفضّلة</div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 18px;">
                <button type="button" class="nav-saved-tile" data-kind="home">
                    <span class="nav-saved-ico" style="background: rgba(13,148,136,.14); color: var(--teal);">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12L12 3l9 9"/><path d="M5 10v11h14V10"/></svg>
                    </span>
                    <span class="nav-saved-name">البيت</span>
                    <span class="nav-saved-addr" id="nav-saved-home-addr">— مش محفوظ</span>
                </button>
                <button type="button" class="nav-saved-tile" data-kind="work">
                    <span class="nav-saved-ico" style="background: rgba(14,165,233,.14); color: #0369A1;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V4h8v3"/></svg>
                    </span>
                    <span class="nav-saved-name">الشغل</span>
                    <span class="nav-saved-addr" id="nav-saved-work-addr">— مش محفوظ</span>
                </button>
            </div>

            <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 800; margin-bottom: 8px;">آخر الوجهات</div>
            <div id="nav-saved-recent" style="display: flex; flex-direction: column; gap: 6px;"></div>
            <div id="nav-saved-empty" hidden style="text-align: center; padding: 24px 8px; color: var(--ink-3); font-size: 12.5px;">
                مفيش وجهات لسه — حدد أي وجهة وهتظهر هنا
            </div>

            <button type="button" id="nav-saved-pick" class="btn btn-teal btn-full" style="padding: 12px; font-size: 13px; margin-top: 16px;">
                + حدد وجهة جديدة
            </button>
        </div>
    </div>

    {{-- ── Destination crosshair (shown while picking the destination) ── --}}
    <div id="nav-crosshair" hidden style="position: absolute; inset: 0; z-index: 550; pointer-events: none;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -100%); pointer-events: none;">
            <svg viewBox="0 0 28 32" width="44" height="48" style="filter: drop-shadow(0 6px 12px rgba(0,0,0,.35));">
                <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#001B2A"/>
                <circle cx="14" cy="14" r="5" fill="#0D9488"/>
            </svg>
        </div>
        <div style="position: absolute; bottom: 24px; right: 16px; left: 16px;
                    background: white; border-radius: 14px; padding: 12px 14px;
                    box-shadow: var(--shadow-lg); text-align: center; pointer-events: auto;">
            <div style="font-weight: 800; font-size: 13px; margin-bottom: 4px;">حدد وجهتك</div>
            <div style="font-size: 11.5px; color: var(--ink-3); margin-bottom: 10px;">حرّك الخريطة لمكان الوصول</div>
            <div style="display: flex; gap: 8px;">
                <button type="button" id="nav-cancel-pick" class="btn btn-line" style="flex: 1; padding: 9px; font-size: 12px;">إلغاء</button>
                <button type="button" id="nav-confirm-pick" class="btn btn-teal" style="flex: 1.5; padding: 9px; font-size: 12px;">تأكيد الوجهة</button>
            </div>
        </div>
    </div>

    {{-- ── Route preview card (between picking and navigating) ── --}}
    <div id="nav-preview-card" hidden
         style="position: absolute; bottom: 16px; right: 12px; left: 12px; z-index: 580;
                background: white; border-radius: 16px; padding: 14px;
                box-shadow: 0 16px 40px -10px rgba(0,27,42,.28);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <span style="width: 38px; height: 38px; border-radius: 12px; background: rgba(13,148,136,.12); color: var(--teal); display: grid; place-items: center; flex-shrink: 0;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="3 11 22 2 13 21 11 13 3 11"/>
                </svg>
            </span>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 900; font-size: 14px;">المسار جاهز</div>
                <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 700; margin-top: 2px;">
                    <span id="nav-preview-distance">— كم</span>
                    <span style="color: var(--ink-4); margin: 0 4px;">·</span>
                    <span id="nav-preview-duration">— دقيقة</span>
                </div>
            </div>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 9px 12px; background: rgba(245,158,11,.10); border-radius: 10px; margin-bottom: 12px;">
            <span style="font-weight: 800; font-size: 12.5px; color: #92400E;">
                على طريقك <span id="nav-preview-count">0</span> تنبيهات
            </span>
            <span id="nav-preview-types" style="font-size: 14px;"></span>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" id="nav-cancel-preview" class="btn btn-line" style="flex: 1; padding: 11px; font-size: 13px;">إلغاء</button>
            <button type="button" id="nav-start-trip" class="btn btn-teal" style="flex: 2; padding: 11px; font-size: 13px; font-weight: 900;">
                ابدأ الطريق
            </button>
        </div>
    </div>

    {{-- ── Arrival celebration screen ─────────────────────────── --}}
    <div id="nav-arrived" hidden
         style="position: fixed; inset: 0; z-index: 1200;
                background: rgba(0,27,42,.92);
                display: flex; align-items: center; justify-content: center; padding: 24px;
                backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
        <div style="background: white; border-radius: 22px; padding: 26px 22px; width: 100%; max-width: 360px; text-align: center;
                    box-shadow: 0 32px 64px -16px rgba(0,0,0,.5);">
            <div style="font-size: 56px; line-height: 1; margin-bottom: 8px;">🎉</div>
            <div style="font-weight: 900; font-size: 22px; color: var(--navy); margin-bottom: 4px;">وصلت!</div>
            <div style="color: var(--ink-3); font-size: 13px; margin-bottom: 22px;">رحلة سعيدة — تم الوصول بأمان</div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 20px;">
                <div style="background: var(--gray-100); border-radius: 12px; padding: 12px 6px;">
                    <div id="nav-arrived-dist" style="font-weight: 900; font-size: 16px; color: var(--teal);">—</div>
                    <div style="font-size: 10.5px; color: var(--ink-3); font-weight: 800; margin-top: 4px;">المسافة</div>
                </div>
                <div style="background: var(--gray-100); border-radius: 12px; padding: 12px 6px;">
                    <div id="nav-arrived-time" style="font-weight: 900; font-size: 16px; color: var(--navy);">—</div>
                    <div style="font-size: 10.5px; color: var(--ink-3); font-weight: 800; margin-top: 4px;">الوقت</div>
                </div>
                <div style="background: var(--gray-100); border-radius: 12px; padding: 12px 6px;">
                    <div id="nav-arrived-alerts" style="font-weight: 900; font-size: 16px; color: #F59E0B;">—</div>
                    <div style="font-size: 10.5px; color: var(--ink-3); font-weight: 800; margin-top: 4px;">تنبيهات</div>
                </div>
            </div>

            <button type="button" id="nav-arrived-close" class="btn btn-teal" style="width: 100%; padding: 13px; font-size: 14px; font-weight: 900;">
                تمام
            </button>
        </div>
    </div>

    {{-- ── Top maneuver banner (next turn, prominent for driver) ── --}}
    <div id="nav-maneuver-bar" hidden
         style="position: absolute; top: 12px; right: 12px; left: 12px; z-index: 590;
                background: linear-gradient(135deg, #001B2A 0%, #0E2E3F 100%);
                color: white; border-radius: 16px;
                padding: 14px 16px;
                box-shadow: 0 16px 36px -8px rgba(0,27,42,.5);
                display: flex; align-items: center; gap: 12px;">
        <span id="nav-maneuver-icon"
              style="width: 52px; height: 52px; border-radius: 14px;
                     background: var(--teal); color: white;
                     display: grid; place-items: center;
                     font-size: 28px; flex-shrink: 0;">⬆</span>
        <div style="flex: 1; min-width: 0;">
            <div id="nav-maneuver-distance"
                 style="font-size: 11px; font-weight: 800; color: rgba(255,255,255,.65); letter-spacing: .3px;">بعد —</div>
            <div id="nav-maneuver-text"
                 style="font-weight: 900; font-size: 14.5px; line-height: 1.35; margin-top: 2px;
                        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">جاري الحساب...</div>
        </div>
    </div>

    {{-- ── Bottom navigation card (live status + actions) ── --}}
    <div id="nav-active-bar" hidden
         style="position: absolute; bottom: 16px; right: 12px; left: 12px; z-index: 590;
                background: white; border-radius: 16px;
                padding: 12px 14px;
                box-shadow: 0 16px 36px -8px rgba(0,27,42,.28);">
        {{-- 3-stat row: ETA · Remaining · Speed --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px; margin-bottom: 10px; text-align: center;">
            <div>
                <div id="nav-eta" style="font-weight: 900; font-size: 16px; color: var(--navy); line-height: 1;">—</div>
                <div style="font-size: 10px; font-weight: 800; color: var(--ink-3); margin-top: 3px;">الوصول</div>
            </div>
            <div style="border-left: 1px solid var(--line); border-right: 1px solid var(--line);">
                <div id="nav-remaining" style="font-weight: 900; font-size: 16px; color: var(--teal); line-height: 1;">—</div>
                <div style="font-size: 10px; font-weight: 800; color: var(--ink-3); margin-top: 3px;">المتبقي</div>
            </div>
            <div>
                <div id="nav-speed" style="font-weight: 900; font-size: 16px; color: var(--navy); line-height: 1;">0<span style="font-size: 10px; font-weight: 700; color: var(--ink-3);"> كم/س</span></div>
                <div style="font-size: 10px; font-weight: 800; color: var(--ink-3); margin-top: 3px;">السرعة</div>
            </div>
        </div>

        {{-- Alerts on route --}}
        <div style="display: flex; align-items: center; justify-content: flex-start; gap: 8px; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 5px; padding: 4px 9px; background: rgba(245,158,11,.12); border-radius: 8px;">
                <span style="font-size: 13px;">⚠</span>
                <span style="font-size: 11.5px; font-weight: 800; color: #92400E;">
                    <span id="nav-alerts-count">0</span> تنبيهات
                </span>
            </div>
        </div>

        <div id="nav-nearest-alert" hidden
             style="display: flex; align-items: center; gap: 8px; padding: 8px 10px; background: #FAFBFC; border-radius: 10px; margin-bottom: 10px;">
            <span id="nav-nearest-emoji" style="font-size: 16px;">📷</span>
            <div style="flex: 1; min-width: 0; font-size: 12px;">
                <span style="font-weight: 800;" id="nav-nearest-label">—</span>
                <span style="color: var(--ink-3); font-weight: 700;" id="nav-nearest-dist"></span>
            </div>
        </div>
        <div style="display: flex; gap: 6px;">
            <button type="button" id="nav-recalc-btn" class="btn"
                    style="flex: 1; padding: 9px; font-size: 12px;
                           background: white; color: var(--ink-1); border: 1px solid var(--line); font-weight: 800;">
                ↻ إعادة حساب
            </button>
            <button type="button" id="nav-stop-btn" class="btn"
                    style="flex: 1; padding: 9px; font-size: 12px; background: #DC2626; color: white; font-weight: 800;">
                ✕ إيقاف التتبع
            </button>
        </div>
    </div>

    {{-- ── Off-route notice toast ── --}}
    <div id="nav-offroute-toast" hidden
         style="position: absolute; top: 90px; left: 50%; transform: translateX(-50%); z-index: 720;
                background: #DC2626; color: white;
                padding: 10px 18px; border-radius: 12px;
                font-weight: 800; font-size: 13px;
                box-shadow: 0 16px 36px -8px rgba(220,38,38,.5);
                animation: navToastIn .3s cubic-bezier(.2,.9,.3,1.3);">
        خرجت عن المسار · جاري إعادة الحساب...
    </div>

    {{-- ── Proximity warning toast (large, transient) ── --}}
    <div id="nav-proximity-toast" hidden
         style="position: absolute; top: 90px; left: 50%; transform: translateX(-50%); z-index: 700;
                background: white; padding: 12px 18px; border-radius: 14px;
                display: flex; align-items: center; gap: 10px;
                box-shadow: 0 16px 40px -8px rgba(220,38,38,.45);
                border: 2px solid #DC2626;
                min-width: 240px;
                animation: navToastIn .3s cubic-bezier(.2,.9,.3,1.3);">
        <span id="nav-proximity-emoji" style="font-size: 28px;">⚠</span>
        <div>
            <div id="nav-proximity-title" style="font-weight: 900; font-size: 13.5px; color: #B91C1C;">تنبيه قريب</div>
            <div id="nav-proximity-dist" style="font-size: 12px; font-weight: 700; color: var(--ink-2); margin-top: 2px;">على بُعد — متر</div>
        </div>
    </div>

    {{-- ── Crosshair overlay (shown when picking a location) ── --}}
    <div id="alert-crosshair" hidden style="position: absolute; inset: 0; z-index: 550; pointer-events: none;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -100%); pointer-events: none;">
            <svg viewBox="0 0 28 32" width="40" height="44" style="filter: drop-shadow(0 6px 12px rgba(0,0,0,.35));">
                <path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" id="alert-pin-shape" fill="#0D9488"/>
                <circle cx="14" cy="14" r="5" fill="white"/>
            </svg>
        </div>
        <div style="position: absolute; bottom: 24px; right: 16px; left: 16px;
                    background: white; border-radius: 14px; padding: 12px 14px;
                    box-shadow: var(--shadow-lg); text-align: center; pointer-events: auto;">
            <div style="font-weight: 800; font-size: 13px; margin-bottom: 8px;">حرّك الخريطة لتحديد المكان</div>
            <div style="display: flex; gap: 8px;">
                <button type="button" id="alert-cancel-loc" class="btn btn-line" style="flex: 1; padding: 9px; font-size: 12px;">إلغاء</button>
                <button type="button" id="alert-confirm-loc" class="btn btn-teal" style="flex: 1.5; padding: 9px; font-size: 12px;">تأكيد المكان</button>
            </div>
        </div>
    </div>

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
           style="position: fixed; right: 12px; left: 12px; bottom: calc(88px + max(14px, env(safe-area-inset-bottom))); background: white; border-radius: 16px; padding: 10px; box-shadow: 0 12px 32px -8px rgba(0,27,42,.25), 0 4px 12px -4px rgba(0,27,42,.12); display: flex; gap: 10px; align-items: center; z-index: 500;">
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

{{-- ────────────────────────────────────────────────────────────
     Alert reporting dialog · two steps:
     Step 1: pick type (8 cards)
     Step 2: optional description + submit (location picked from map)
─────────────────────────────────────────────────────────────── --}}
<dialog id="alert-dialog" class="alert-dialog" dir="rtl">
    <button type="button" class="alert-dialog-close" id="alert-dialog-close" aria-label="إغلاق">✕</button>

    {{-- Step 1: pick type --}}
    <div id="alert-step-type">
        <div class="alert-dialog-head">
            <div class="alert-dialog-title">إيه نوع التنبيه؟</div>
            <div class="alert-dialog-sub">اختر النوع · بعدها حدّد المكان على الخريطة</div>
        </div>
        <div class="alert-type-grid">
            @foreach($alertTypes as $at)
                <button type="button" class="alert-type-card" data-type="{{ $at['slug'] }}" data-color="{{ $at['color'] }}">
                    <span class="alert-type-emoji" style="background: {{ $at['color'] }}1A; color: {{ $at['color'] }};">{{ $at['icon'] }}</span>
                    <span class="alert-type-name">{{ \App\Models\RoadAlert::TYPES[$at['slug']]['label_ar'] }}</span>
                </button>
            @endforeach
        </div>
        <div class="alert-dialog-foot">
            استخدم تنبيهات عامة ومحترمة · ممنوع نشر أسماء أو صور أشخاص.
        </div>
    </div>

    {{-- Step 2: details + submit --}}
    <div id="alert-step-details" hidden>
        <div class="alert-dialog-head">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span id="alert-step2-emoji" class="alert-type-emoji" style="width: 38px; height: 38px;">📍</span>
                <div>
                    <div class="alert-dialog-title" id="alert-step2-title">تفاصيل التنبيه</div>
                    <div class="alert-dialog-sub" id="alert-step2-loc">الموقع: —</div>
                </div>
            </div>
        </div>

        <label class="alert-label" for="alert-desc">وصف اختياري</label>
        <textarea id="alert-desc" maxlength="500" rows="3" placeholder="مثال: رادار قبل الكوبري ٥٠م · اتجاه القاهرة" class="alert-input"></textarea>
        <div class="alert-counter"><span id="alert-desc-count">0</span> / 500</div>

        <div style="display: flex; gap: 8px; margin-top: 14px;">
            <button type="button" id="alert-back-type" class="btn btn-line" style="flex: 1; padding: 11px; font-size: 13px;">رجوع</button>
            <button type="button" id="alert-submit" class="btn btn-teal" style="flex: 2; padding: 11px; font-size: 13px;">إرسال البلاغ</button>
        </div>
    </div>
</dialog>

{{-- ── Floating toast for confirm/reject feedback ── --}}
<div id="alert-toast" hidden style="position: fixed; bottom: 86px; left: 50%; transform: translateX(-50%); z-index: 9999;
            background: var(--navy); color: white; padding: 10px 16px; border-radius: 12px;
            font-size: 13px; font-weight: 700;
            box-shadow: 0 12px 30px -10px rgba(0,27,42,.4);"></div>

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

/* ── Road alert markers ─────────────────────────────────────── */
.alert-marker {
    background: transparent !important;
    border: none !important;
}
.alert-marker-pin {
    position: relative;
    width: 34px; height: 34px;
    border-radius: 50%;
    display: grid; place-items: center;
    color: white;
    font-size: 18px;
    box-shadow: 0 4px 10px rgba(0,27,42,.30);
    border: 2.5px solid white;
}
.alert-marker-pin.is-confirmed::after {
    content: "✓";
    position: absolute;
    top: -6px; right: -6px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: var(--teal);
    color: white;
    font-size: 10px;
    font-weight: 900;
    display: grid; place-items: center;
    border: 2px solid white;
}
.alert-marker-pin.pulse {
    animation: alertPulse 1.8s ease-in-out infinite;
}
@keyframes alertPulse {
    0%, 100% { box-shadow: 0 4px 10px rgba(0,27,42,.30), 0 0 0 0 currentColor; }
    50%      { box-shadow: 0 4px 10px rgba(0,27,42,.30), 0 0 0 12px transparent; }
}

.alert-popup .label-strong { font-size: 13.5px; font-weight: 900; }
.alert-popup .desc { font-size: 12.5px; color: var(--ink-2); line-height: 1.6; margin: 6px 0; }
.alert-popup .meta { font-size: 11px; color: var(--ink-4); font-weight: 700; }
.alert-popup .actions { display: flex; gap: 6px; margin-top: 10px; }
.alert-popup .actions button {
    flex: 1;
    padding: 7px 8px;
    border-radius: 8px;
    border: 1px solid var(--line);
    background: white;
    font-family: inherit;
    font-size: 11.5px;
    font-weight: 800;
    cursor: pointer;
    color: var(--ink-1);
}
.alert-popup .actions .confirm-btn:hover, .alert-popup .actions .confirm-btn.is-voted {
    background: rgba(16,185,129,.12); border-color: #10B981; color: #047857;
}
.alert-popup .actions .reject-btn:hover, .alert-popup .actions .reject-btn.is-voted {
    background: rgba(220,38,38,.10); border-color: #DC2626; color: #B91C1C;
}
.alert-popup .badge-confirmed {
    display: inline-block;
    background: rgba(13,148,136,.14);
    color: var(--teal);
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 800;
    margin-top: 6px;
}

/* Alert chips colored to match the type */
.alert-chip { background: white; }
.alert-chip.active { background: var(--navy); color: white; border-color: var(--navy); }

/* ── Top-right map FABs (report + navigate) ─────────────────── */
.map-fab {
    background: var(--navy);
    color: white;
    padding: 8px 12px;
    border-radius: 12px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 8px 22px -6px rgba(0,27,42,.45);
    white-space: nowrap;
    transition: transform .12s ease;
}
.map-fab:active { transform: scale(.96); }
.map-fab-teal { background: var(--teal); }
.map-fab-ico {
    display: inline-grid;
    place-items: center;
    width: 20px; height: 20px;
    border-radius: 50%;
}

/* ── Route polyline glow ───────────────────────────────────── */
.nav-route-line {
    filter: drop-shadow(0 2px 6px rgba(13,148,136,.45));
}

/* ── Saved destination tiles ───────────────────────────────── */
.nav-saved-tile {
    background: var(--gray-100);
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 12px 10px;
    text-align: right;
    cursor: pointer;
    display: flex; flex-direction: column; gap: 4px;
    transition: transform .12s ease, border-color .12s ease;
}
.nav-saved-tile:active { transform: scale(.97); }
.nav-saved-tile.has-coords { border-color: var(--teal); }
.nav-saved-ico {
    width: 36px; height: 36px;
    border-radius: 11px;
    display: grid; place-items: center;
    margin-bottom: 4px;
}
.nav-saved-name { font-weight: 900; font-size: 13px; color: var(--navy); }
.nav-saved-addr { font-size: 11px; color: var(--ink-3); font-weight: 700; line-height: 1.4;
                  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.nav-recent-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    background: var(--gray-100);
    border-radius: 12px;
    cursor: pointer;
    transition: transform .12s ease;
}
.nav-recent-row:active { transform: scale(.98); }
.nav-recent-row .ph { font-size: 11px; }
.nav-recent-row .recent-ico {
    width: 32px; height: 32px;
    border-radius: 10px;
    background: rgba(13,148,136,.10); color: var(--teal);
    display: grid; place-items: center;
    flex-shrink: 0;
}
.nav-recent-row .recent-name { flex: 1; font-weight: 800; font-size: 12.5px;
                               overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.nav-recent-row .recent-del {
    border: none; background: transparent; color: var(--ink-4);
    width: 26px; height: 26px; border-radius: 8px;
    font-weight: 900; font-size: 14px; cursor: pointer;
}
.nav-recent-row .recent-del:hover { background: rgba(220,38,38,.10); color: #DC2626; }

/* ── Toast animations ──────────────────────────────────────── */
@keyframes navToastIn {
    from { opacity: 0; transform: translate(-50%, -8px) scale(.94); }
    to   { opacity: 1; transform: translate(-50%, 0)    scale(1); }
}

/* ── Alerts on route get full opacity, off-route are dimmed ── */
.alert-marker.is-off-route { opacity: 0.35; }

/* ── Driving mode: hide everything except alerts + route + user ── */
body.is-navigating #map-form,
body.is-navigating .chip-scroll-wrap,
body.is-navigating #alert-report-btn,
body.is-navigating #nav-start-btn,
body.is-navigating #map-card,
body.is-navigating #locate-btn {
    display: none !important;
}
/* In driving mode, dimmed alerts are hidden completely — only on-route ones show */
body.is-navigating .alert-marker.is-off-route {
    display: none !important;
}

/* Route direction arrows */
.nav-arrow { background: transparent !important; border: none !important; }
.nav-arrow svg { filter: drop-shadow(0 2px 3px rgba(0,27,42,.35)); }

/* ── Alert dialog ─────────────────────────────────────────── */
.alert-dialog {
    border: none;
    border-radius: 20px;
    padding: 18px;
    box-sizing: border-box;
    width: min(94vw, 460px);
    max-width: 94vw;
    max-height: 90dvh;
    background: white;
    box-shadow: 0 30px 80px -10px rgba(0,27,42,.35);
    overflow-y: auto;
    color: var(--ink-1);
    /* Reliable centering across browsers + RTL — let `margin: auto` do the work */
    position: fixed;
    inset: 0;
    margin: auto;
}
.alert-dialog::backdrop {
    background: rgba(0,27,42,.55);
    -webkit-backdrop-filter: blur(6px);
    backdrop-filter: blur(6px);
}
.alert-dialog-close {
    position: absolute;
    top: 10px;
    inset-inline-start: 14px;     /* LEFT in LTR, automatically flips for RTL */
    background: rgba(0,27,42,.06);
    border: none;
    width: 30px; height: 30px;
    border-radius: 50%;
    font-size: 15px;
    font-weight: 700;
    color: var(--ink-2);
    cursor: pointer;
    z-index: 2;
    display: grid;
    place-items: center;
    line-height: 1;
}
.alert-dialog-close:hover { background: rgba(0,27,42,.12); }
.alert-dialog-head {
    margin: 4px 0 14px;
    padding-inline-start: 36px;   /* clear room for close button */
}
.alert-dialog-title { font-size: 16px; font-weight: 900; color: var(--ink-1); }
.alert-dialog-sub { font-size: 12px; color: var(--ink-3); font-weight: 600; margin-top: 4px; line-height: 1.6; }

.alert-type-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 6px;
    max-width: 100%;
}
.alert-type-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #FAFBFC;
    border: 1.5px solid var(--line);
    border-radius: 11px;
    text-align: right;
    cursor: pointer;
    font-family: inherit;
    width: 100%;
    transition: border-color .12s ease, background .12s ease, transform .12s ease;
}
.alert-type-card:hover { border-color: var(--teal); }
.alert-type-card:active { transform: scale(.97); }
.alert-type-emoji {
    width: 32px; height: 32px;
    border-radius: 10px;
    display: grid; place-items: center;
    font-size: 17px;
    flex-shrink: 0;
}
.alert-type-name { font-weight: 800; font-size: 13px; }

.alert-dialog-foot {
    margin-top: 14px;
    padding: 10px 12px;
    background: rgba(245,158,11,.08);
    border-radius: 10px;
    font-size: 11.5px;
    color: #92400E;
    font-weight: 700;
    line-height: 1.7;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.alert-label { display: block; font-size: 12px; font-weight: 800; color: var(--ink-2); margin: 14px 0 6px; }
.alert-input {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid var(--line);
    border-radius: 11px;
    font-family: inherit;
    font-size: 13.5px;
    background: #FAFBFC;
    color: var(--ink-1);
    resize: vertical;
}
.alert-input:focus { outline: none; background: white; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(13,148,136,.14); }
.alert-counter { text-align: left; font-size: 10.5px; color: var(--ink-4); font-weight: 700; margin-top: 4px; }

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

    function buildBusinessMarker(b) {
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
    }

    // Defer all markers to next frame so the map tiles paint first
    requestAnimationFrame(function () {
        businesses.forEach(buildBusinessMarker);

        // Seed the card with the first business (featured if any, else first)
        if (businesses.length) {
            var seed = businesses.find(function (b) { return b.featured; }) || businesses[0];
            showInCard(seed);
        }
        fitToVisible();
    });

    // Fit the map to the visible markers
    function fitToVisible() {
        var visible = markerEntries.filter(function (e) { return e.visible; }).map(function (e) { return e.marker; });
        if (visible.length === 0) return;
        var group = L.featureGroup(visible);
        map.fitBounds(group.getBounds().pad(0.25), { maxZoom: 16 });
    }

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

    // ════════════════════════════════════════════════════════════════
    //  أمان وطريق بنها — Road & safety alerts layer
    // ════════════════════════════════════════════════════════════════
    var alerts        = {!! $alertsJson ?? '[]' !!};
    var alertMarkers  = {};        // id → { marker, alert }
    var activeAlertFilter = 'all';
    var csrfToken     = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function alertEmoji(type) {
        return ({
            'radar':'📷','traffic':'🚗','accident':'⚠','pothole':'🕳',
            'blocked':'🚧','caution':'❗','signal':'🚦','safety':'🛡',
        })[type] || '📍';
    }

    function buildAlertMarker(a) {
        var pulseClass = a.is_confirmed ? '' : (a.age_minutes < 30 ? 'pulse' : '');
        var confirmedClass = a.is_confirmed ? 'is-confirmed' : '';
        var icon = L.divIcon({
            className: 'alert-marker',
            html: '<div class="alert-marker-pin ' + pulseClass + ' ' + confirmedClass + '"'
                + ' style="background: ' + a.type_color + '; color: ' + a.type_color + ';">'
                + '<span style="line-height:1;">' + alertEmoji(a.type) + '</span>'
                + '</div>',
            iconSize:    [38, 38],
            iconAnchor:  [19, 19],
            popupAnchor: [0, -20],
        });
        var m = L.marker([a.lat, a.lng], { icon: icon, zIndexOffset: 1000 }).addTo(map);
        m.bindPopup(alertPopupHtml(a), { offset: [0, -10] });
        m.on('popupopen', function () { wirePopupButtons(a.id); });
        return m;
    }

    function alertPopupHtml(a) {
        var ageMin = Math.max(1, Math.round(a.age_minutes || 0));
        var ageText = ageMin < 60 ? (ageMin + ' دقيقة') : (Math.round(ageMin/60) + ' ساعة');
        var confirmedBadge = a.is_confirmed
            ? '<span class="badge-confirmed">✓ مؤكد من المجتمع</span>'
            : '';
        var descBlock = a.description
            ? '<div class="desc">' + escapeHtml(a.description) + '</div>'
            : '';
        var confirmCls = a.voter_choice === 'confirm' ? 'is-voted' : '';
        var rejectCls  = a.voter_choice === 'reject'  ? 'is-voted' : '';

        return '<div class="alert-popup" data-alert-id="' + a.id + '">'
            +   '<div class="label-strong" style="color:' + a.type_color + '">'
            +     alertEmoji(a.type) + ' ' + escapeHtml(a.type_label)
            +   '</div>'
            +   descBlock
            +   '<div class="meta">من ' + ageText + ' · ' + a.confirmations + ' تأكيد · ' + a.rejections + ' نفي</div>'
            +   confirmedBadge
            +   '<div class="actions">'
            +     '<button type="button" class="confirm-btn ' + confirmCls + '">✓ مازال موجود</button>'
            +     '<button type="button" class="reject-btn ' + rejectCls + '">✕ غير موجود</button>'
            +   '</div>'
            + '</div>';
    }

    function escapeHtml(s) {
        return (s || '').toString()
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function wirePopupButtons(alertId) {
        var popupEl = document.querySelector('.alert-popup[data-alert-id="' + alertId + '"]');
        if (!popupEl) return;
        popupEl.querySelector('.confirm-btn')?.addEventListener('click', function () {
            voteOnAlert(alertId, 'confirm');
        });
        popupEl.querySelector('.reject-btn')?.addEventListener('click', function () {
            voteOnAlert(alertId, 'reject');
        });
    }

    function voteOnAlert(alertId, kind) {
        var url = '/alerts/' + alertId + '/' + kind;
        fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (!res.ok) {
                showAlertToast(res.body?.error || 'حصلت مشكلة، حاول تاني.');
                return;
            }
            var a = res.body.alert;
            var entry = alertMarkers[a.id];
            if (!entry) return;

            // If the alert was auto-hidden (rejected), remove it
            if (a.status !== 'active') {
                map.removeLayer(entry.marker);
                delete alertMarkers[a.id];
                showAlertToast('شكراً — تم إخفاء التنبيه.');
                return;
            }

            entry.alert = a;
            entry.marker.setPopupContent(alertPopupHtml(a));
            // refresh marker icon (in case is_confirmed changed)
            entry.marker.setIcon(buildAlertMarker(a).options.icon);
            showAlertToast(kind === 'confirm' ? 'شكراً للتأكيد ✓' : 'شكراً، تم تسجيل رأيك');
        })
        .catch(function () {
            showAlertToast('مفيش اتصال. حاول تاني.');
        });
    }

    var toastTimer = null;
    function showAlertToast(msg) {
        var t = document.getElementById('alert-toast');
        if (!t) return;
        t.textContent = msg;
        t.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { t.hidden = true; }, 2400);
    }

    // Defer alert markers to next frame (after business markers + map tiles)
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            alerts.forEach(function (a) {
                alertMarkers[a.id] = { marker: buildAlertMarker(a), alert: a };
            });
        });
    });

    // ── Alert filter chips ──
    document.querySelectorAll('.alert-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('.alert-chip').forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            activeAlertFilter = chip.dataset.alertType;
            Object.keys(alertMarkers).forEach(function (id) {
                var entry = alertMarkers[id];
                var show = activeAlertFilter === 'all' || entry.alert.type === activeAlertFilter;
                if (show) entry.marker.addTo(map);
                else map.removeLayer(entry.marker);
            });
        });
    });

    // ── Report alert flow ──
    var dialog       = document.getElementById('alert-dialog');
    var stepType     = document.getElementById('alert-step-type');
    var stepDetails  = document.getElementById('alert-step-details');
    var crosshair    = document.getElementById('alert-crosshair');
    var reportBtn    = document.getElementById('alert-report-btn');
    var cancelLocBtn = document.getElementById('alert-cancel-loc');
    var confirmLocBtn= document.getElementById('alert-confirm-loc');
    var desc         = document.getElementById('alert-desc');
    var descCount    = document.getElementById('alert-desc-count');
    var submitBtn    = document.getElementById('alert-submit');
    var backTypeBtn  = document.getElementById('alert-back-type');

    var pickedType = null;
    var pickedLoc  = null;

    function openDialog() {
        if (dialog.showModal) dialog.showModal();
        else dialog.setAttribute('open', '');
    }
    function closeDialog() {
        if (dialog.close) dialog.close();
        else dialog.removeAttribute('open');
    }
    function resetToStep1() {
        stepType.hidden = false;
        stepDetails.hidden = true;
    }

    // Report button: always start from Step 1 (pick type)
    reportBtn?.addEventListener('click', function () {
        pickedType = null;
        pickedLoc  = null;
        crosshair.hidden = true;
        resetToStep1();
        openDialog();
    });
    document.getElementById('alert-dialog-close')?.addEventListener('click', closeDialog);

    // Auto-open dialog if arrived via ?report=1 (from discover safety card)
    if (new URLSearchParams(window.location.search).get('report') === '1') {
        setTimeout(function () { reportBtn?.click(); }, 300);
    }

    // Step 1 → Step 2: pick a type, then close dialog to let user pan the map
    document.querySelectorAll('.alert-type-card').forEach(function (card) {
        card.addEventListener('click', function () {
            pickedType = card.dataset.type;
            closeDialog();
            // Enter location-picking mode
            crosshair.hidden = false;
            // Tint the crosshair pin to the chosen type color
            var pinShape = document.getElementById('alert-pin-shape');
            if (pinShape) pinShape.setAttribute('fill', card.dataset.color);
            // Re-fit so the user has room to pan
            map.invalidateSize();
        });
    });

    cancelLocBtn?.addEventListener('click', function () {
        crosshair.hidden = true;
        pickedType = null;
        pickedLoc  = null;
    });

    confirmLocBtn?.addEventListener('click', function () {
        var c = map.getCenter();
        pickedLoc = { lat: c.lat, lng: c.lng };
        crosshair.hidden = true;

        // Update Step 2 header preview
        document.getElementById('alert-step2-emoji').textContent = alertEmoji(pickedType);
        document.getElementById('alert-step2-emoji').style.background = (
            { radar:'#DC2626', traffic:'#F97316', accident:'#DC2626', pothole:'#EAB308',
              blocked:'#3B82F6', caution:'#F59E0B', signal:'#94A1AE', safety:'#10B981' }[pickedType] || '#5E6A77'
        ) + '1A';
        document.getElementById('alert-step2-title').textContent = ({
            radar:'تنبيه رادار', traffic:'تنبيه زحمة', accident:'تنبيه حادثة',
            pothole:'تنبيه حفرة', blocked:'طريق مقفول', caution:'انتباه',
            signal:'عطل إشارة', safety:'تنبيه أمان',
        })[pickedType] || 'تنبيه';
        document.getElementById('alert-step2-loc').textContent =
            'الموقع: ' + pickedLoc.lat.toFixed(4) + ', ' + pickedLoc.lng.toFixed(4);

        stepType.hidden = true;
        stepDetails.hidden = false;
        openDialog();
    });

    backTypeBtn?.addEventListener('click', function () {
        resetToStep1();
    });

    desc?.addEventListener('input', function () {
        descCount.textContent = desc.value.length;
    });

    // ════════════════════════════════════════════════════════════════
    //  Navigation — destination → route → live driving with proximity
    // ════════════════════════════════════════════════════════════════
    var NAV_STATE          = 'idle'; // idle | picking | preview | active
    var NAV_PROXIMITY_M    = 400;    // warn within this distance (meters)
    var NAV_ON_ROUTE_M     = 250;    // alert "on route" if within this distance
    var NAV_OFF_ROUTE_M    = 50;     // user is "off route" if farther than this
    var NAV_POLL_INTERVAL  = 12000;  // refresh alerts every 12s while navigating
    var NAV_ARROW_SPACING  = 250;    // meters between direction arrows on the route

    var navDest         = null;     // { lat, lng }
    var navUserPos      = null;     // { lat, lng }
    var navRouteCoords  = null;     // [[lat, lng], ...]
    var navRouteSteps   = [];       // [{ location, type, modifier, distance, name }]
    var navRouteLayer   = null;     // L.LayerGroup of polylines
    var navArrowLayer   = null;     // L.LayerGroup of arrow markers
    var navDestMarker   = null;
    var navUserMarker   = null;
    var navWatchId      = null;
    var navPollTimer    = null;
    var navWarnedAlerts = {};       // id → timestamp of last warning
    var navOnRouteIds   = [];       // alert ids currently considered "on route"
    var navCurrentStepIdx = 0;      // active step index in navRouteSteps
    var navOffRouteSince  = 0;      // timestamp when user went off-route
    var navIsRecalculating = false;
    var navAutoFollow      = true;  // map auto-pans to user position
    var navTripStartTs    = 0;      // ms timestamp when navigation started
    var navTripStartDist  = 0;      // total route distance at start (m)
    var navMeters         = 0;      // total meters travelled
    var navLastPos        = null;   // last user pos for incremental distance
    var navCurrentSpeed   = 0;      // current speed in km/h (from GPS or computed)
    var navHasArrived     = false;

    /* ── Wake Lock: keep the screen ON during driving ───────────
     * Requires HTTPS. Re-acquired on visibility change.
     */
    var navWakeLock = null;
    async function navAcquireWakeLock() {
        if (!('wakeLock' in navigator)) return;
        try {
            navWakeLock = await navigator.wakeLock.request('screen');
            navWakeLock.addEventListener?.('release', function () { navWakeLock = null; });
        } catch (e) { /* user denied or unsupported */ }
    }
    function navReleaseWakeLock() {
        try { navWakeLock?.release(); } catch (e) {}
        navWakeLock = null;
    }
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible' && NAV_STATE === 'active' && !navWakeLock) {
            navAcquireWakeLock();
        }
    });

    var navStartBtn      = document.getElementById('nav-start-btn');
    var navCrosshair     = document.getElementById('nav-crosshair');
    var navCancelPick    = document.getElementById('nav-cancel-pick');
    var navConfirmPick   = document.getElementById('nav-confirm-pick');
    var navPreviewCard   = document.getElementById('nav-preview-card');
    var navCancelPrev    = document.getElementById('nav-cancel-preview');
    var navStartTrip     = document.getElementById('nav-start-trip');
    var navActiveBar     = document.getElementById('nav-active-bar');
    var navManeuverBar   = document.getElementById('nav-maneuver-bar');
    var navStopBtn       = document.getElementById('nav-stop-btn');
    var navRecalcBtn     = document.getElementById('nav-recalc-btn');
    var navProximityToast= document.getElementById('nav-proximity-toast');
    var navOffRouteToast = document.getElementById('nav-offroute-toast');

    /* ── State setters ─────────────────────────────────────────── */
    function navSetState(s) {
        NAV_STATE = s;
        navCrosshair.hidden    = s !== 'picking';
        navPreviewCard.hidden  = s !== 'preview';
        navActiveBar.hidden    = s !== 'active';
        if (navManeuverBar) navManeuverBar.hidden = s !== 'active';
        // Hide the start button while we're already in a flow
        if (navStartBtn) navStartBtn.style.display = (s === 'idle') ? '' : 'none';

        // Driving mode: enter focus mode — hide everything except alerts
        document.body.classList.toggle('is-navigating', s === 'active');

        if (s === 'active') {
            navHideBusinessMarkers();
        } else if (s === 'idle') {
            navShowBusinessMarkers();
        }
    }

    /* ── Toggle business markers (hidden during driving) ───────── */
    function navHideBusinessMarkers() {
        if (typeof markerEntries === 'undefined') return;
        markerEntries.forEach(function (e) {
            if (e.visible) { map.removeLayer(e.marker); e._wasVisible = true; e.visible = false; }
        });
    }
    function navShowBusinessMarkers() {
        if (typeof markerEntries === 'undefined') return;
        markerEntries.forEach(function (e) {
            if (e._wasVisible) {
                e.marker.addTo(map);
                e.visible = true;
                e._wasVisible = false;
            }
        });
    }

    /* ── Geometry helpers ──────────────────────────────────────── */
    function navDistanceMeters(lat1, lng1, lat2, lng2) {
        var R = 6371000;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat/2)*Math.sin(dLat/2) +
                Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180) *
                Math.sin(dLng/2)*Math.sin(dLng/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }
    function navDistanceToRoute(lat, lng) {
        if (!navRouteCoords) return Infinity;
        var min = Infinity;
        for (var i = 0; i < navRouteCoords.length; i++) {
            var d = navDistanceMeters(lat, lng, navRouteCoords[i][0], navRouteCoords[i][1]);
            if (d < min) min = d;
        }
        return min;
    }
    function navFormatDistance(m) {
        if (m < 1000) return Math.round(m) + ' متر';
        return (m/1000).toFixed(m < 10000 ? 1 : 0) + ' كم';
    }
    function navFormatDuration(s) {
        var mins = Math.round(s/60);
        if (mins < 60) return mins + ' دقيقة';
        var h = Math.floor(mins/60), m = mins % 60;
        return h + ' س' + (m > 0 ? ' و ' + m + ' د' : '');
    }

    /* ── Get current GPS position (one-shot) ──────────────────── */
    function navGetCurrentPosition() {
        return new Promise(function (resolve, reject) {
            if (!navigator.geolocation) return reject(new Error('no-geo'));
            navigator.geolocation.getCurrentPosition(
                function (pos) { resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }); },
                function (err) { reject(err); },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        });
    }

    /* ── Routing via OSRM public demo (with turn-by-turn steps) ─ */
    function navFetchRoute(from, to) {
        var url = 'https://router.project-osrm.org/route/v1/driving/'
                + from.lng + ',' + from.lat + ';' + to.lng + ',' + to.lat
                + '?overview=full&geometries=geojson&alternatives=false&steps=true&annotations=false';
        return fetch(url)
            .then(function (r) { if (!r.ok) throw new Error('osrm ' + r.status); return r.json(); })
            .then(function (data) {
                if (!data.routes || !data.routes[0]) throw new Error('no-route');
                var route = data.routes[0];
                var steps = [];
                (route.legs || []).forEach(function (leg) {
                    (leg.steps || []).forEach(function (s) {
                        steps.push({
                            location: [s.maneuver.location[1], s.maneuver.location[0]],
                            type:     s.maneuver.type,
                            modifier: s.maneuver.modifier || '',
                            distance: s.distance || 0,   // m for THIS step
                            name:     s.name || '',
                        });
                    });
                });
                return {
                    coords:   route.geometry.coordinates.map(function (c) { return [c[1], c[0]]; }),
                    distance: route.distance,    // meters
                    duration: route.duration,    // seconds
                    steps:    steps,
                };
            });
    }

    /* ── Translate OSRM maneuver to a friendly Arabic line ────── */
    function navManeuverText(step) {
        if (!step) return '';
        var m = step.modifier || '';
        var n = step.name ? ' عند ' + step.name : '';
        switch (step.type) {
            case 'depart':              return 'ابدأ السير' + n;
            case 'arrive':              return 'وصلت لوجهتك';
            case 'turn':
                if (m === 'left')         return 'ادخل شمال' + n;
                if (m === 'right')        return 'ادخل يمين' + n;
                if (m === 'slight left')  return 'خد شمال خفيف' + n;
                if (m === 'slight right') return 'خد يمين خفيف' + n;
                if (m === 'sharp left')   return 'ادخل شمال حاد' + n;
                if (m === 'sharp right')  return 'ادخل يمين حاد' + n;
                if (m === 'uturn')        return 'ارجع للخلف';
                return 'انعطف' + n;
            case 'continue':            return 'كمل مستقيم' + n;
            case 'merge':               return 'اندمج' + (m === 'left' ? ' شمال' : (m === 'right' ? ' يمين' : '')) + n;
            case 'on ramp':             return 'ادخل المدخل' + (m === 'left' ? ' الشمال' : (m === 'right' ? ' اليمين' : '')) + n;
            case 'off ramp':            return 'اخرج من المخرج' + (m === 'left' ? ' الشمال' : (m === 'right' ? ' اليمين' : '')) + n;
            case 'fork':                return 'خد التفرّع' + (m === 'left' ? ' الشمال' : (m === 'right' ? ' اليمين' : '')) + n;
            case 'end of road':         return 'في آخر الشارع' + (m === 'left' ? ' خد شمال' : (m === 'right' ? ' خد يمين' : '')) + n;
            case 'roundabout':
            case 'rotary':              return 'ادخل الدوران' + n;
            case 'exit roundabout':
            case 'exit rotary':         return 'اخرج من الدوران' + n;
            case 'new name':            return 'كمل في' + (step.name ? ' ' + step.name : ' نفس الاتجاه');
            default:                    return 'كمل في طريقك' + n;
        }
    }

    function navManeuverIcon(step) {
        // Kept for back-compat; the visual rendering uses navRenderManeuverIcon (SVG).
        return '⬆';
    }

    /* ── SVG-based maneuver icon: rotates a single arrow path per direction ─ */
    function navManeuverSvgKey(step) {
        if (!step) return { key: 'straight', tint: 'teal' };
        var m = step.modifier || '';
        switch (step.type) {
            case 'arrive':           return { key: 'flag',       tint: 'green' };
            case 'depart':           return { key: 'straight',   tint: 'teal'  };
            case 'roundabout':
            case 'rotary':           return { key: 'roundabout', tint: 'teal'  };
            case 'continue':
            case 'new name':         return { key: 'straight',   tint: 'teal'  };
            case 'turn':
                if (m === 'uturn')                       return { key: 'uturn',         tint: 'amber' };
                if (m === 'sharp right')                 return { key: 'sharp-right',   tint: 'teal'  };
                if (m === 'sharp left')                  return { key: 'sharp-left',    tint: 'teal'  };
                if (m === 'slight right')                return { key: 'slight-right',  tint: 'teal'  };
                if (m === 'slight left')                 return { key: 'slight-left',   tint: 'teal'  };
                if (m.includes('right'))                 return { key: 'right',         tint: 'teal'  };
                if (m.includes('left'))                  return { key: 'left',          tint: 'teal'  };
                return { key: 'straight', tint: 'teal' };
            case 'merge':
            case 'fork':
            case 'on ramp':
            case 'off ramp':
                if (m.includes('right')) return { key: 'slight-right', tint: 'teal' };
                if (m.includes('left'))  return { key: 'slight-left',  tint: 'teal' };
                return { key: 'straight', tint: 'teal' };
            default:                  return { key: 'straight', tint: 'teal' };
        }
    }

    function navRenderManeuverIcon(el, step) {
        var info = navManeuverSvgKey(step);
        var paths = {
            // Up arrow — pointing up
            'straight':     '<path d="M16 4v22M8 12l8-8 8 8" />',
            'right':        '<path d="M4 16h22M18 8l8 8-8 8" />',
            'left':         '<path d="M28 16H6M14 8l-8 8 8 8" />',
            'sharp-right':  '<path d="M6 24c0-10 8-18 18-18M16 10l8-4 4 8" />',
            'sharp-left':   '<path d="M26 24c0-10-8-18-18-18M16 10l-8-4-4 8" />',
            'slight-right': '<path d="M8 26l16-16M14 10h10v10" />',
            'slight-left':  '<path d="M24 26L8 10M18 10H8v10" />',
            'uturn':        '<path d="M8 26V12a6 6 0 0 1 12 0v8M14 20l-6 6-6-6" transform="translate(8 0)"/>',
            'roundabout':   '<circle cx="16" cy="16" r="8" fill="none"/><path d="M16 4v8M28 16h-8M16 28v-8M4 16h8"/>',
            'flag':         '<path d="M6 28V4M6 4h18l-4 6 4 6H6"/>',
        };
        var tint = info.tint === 'green' ? '#10B981'
                 : info.tint === 'amber' ? '#F59E0B'
                                         : '#0D9488';
        el.style.background = tint;
        el.innerHTML =
            '<svg viewBox="0 0 32 32" width="32" height="32" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">'
            + paths[info.key] + '</svg>';
    }

    /* ── Draw / clear route polyline + arrows ────────────────── */
    function navDrawRoute(coords) {
        navClearRoute();
        var outline = L.polyline(coords, { color: '#ffffff', weight: 9, opacity: .95, lineCap: 'round', lineJoin: 'round' });
        var line    = L.polyline(coords, { color: '#0D9488', weight: 6, opacity: .95, lineCap: 'round', lineJoin: 'round', className: 'nav-route-line' });
        navRouteLayer = L.layerGroup([outline, line]).addTo(map);
        navDrawArrows(coords);
    }
    function navClearRoute() {
        if (navRouteLayer) { map.removeLayer(navRouteLayer); navRouteLayer = null; }
        if (navArrowLayer) { map.removeLayer(navArrowLayer); navArrowLayer = null; }
    }

    /* ── Direction arrows along the route ────────────────────── */
    function navBearing(lat1, lng1, lat2, lng2) {
        var toRad = Math.PI / 180, toDeg = 180 / Math.PI;
        var dLng = (lng2 - lng1) * toRad;
        var y = Math.sin(dLng) * Math.cos(lat2 * toRad);
        var x = Math.cos(lat1 * toRad) * Math.sin(lat2 * toRad)
              - Math.sin(lat1 * toRad) * Math.cos(lat2 * toRad) * Math.cos(dLng);
        return ((Math.atan2(y, x) * toDeg) + 360) % 360;
    }

    function navArrowIcon(bearing) {
        return L.divIcon({
            className: 'nav-arrow',
            html: '<svg viewBox="0 0 16 16" width="16" height="16" '
                + 'style="transform: rotate(' + (bearing - 90) + 'deg);">'
                + '<path d="M2 8h10 M9 5l4 3-4 3" '
                + 'fill="none" stroke="#0D9488" stroke-width="2.5" '
                + 'stroke-linecap="round" stroke-linejoin="round"/>'
                + '</svg>',
            iconSize:   [16, 16],
            iconAnchor: [8, 8],
        });
    }

    function navDrawArrows(coords) {
        if (!coords || coords.length < 2) return;
        var markers = [];
        var accumulated = 0;
        for (var i = 0; i < coords.length - 1; i++) {
            var a = coords[i], b = coords[i + 1];
            var d = navDistanceMeters(a[0], a[1], b[0], b[1]);
            accumulated += d;
            if (accumulated >= NAV_ARROW_SPACING) {
                accumulated = 0;
                var br = navBearing(a[0], a[1], b[0], b[1]);
                // Place arrow at midpoint of this segment, rotated to direction
                var mid = [(a[0] + b[0]) / 2, (a[1] + b[1]) / 2];
                markers.push(L.marker(mid, { icon: navArrowIcon(br), zIndexOffset: 600, interactive: false }));
            }
        }
        if (markers.length) {
            navArrowLayer = L.layerGroup(markers).addTo(map);
        }
    }

    /* ── User location marker (live dot) ──────────────────────── */
    function navSetUserMarker(latlng) {
        if (!navUserMarker) {
            var icon = L.divIcon({
                className: 'banhawy-user-pin',
                html: '<svg viewBox="0 0 24 24" width="24" height="24">'
                    + '<circle cx="12" cy="12" r="11" fill="#fff" opacity=".85"/>'
                    + '<circle cx="12" cy="12" r="7"  fill="#0D6EFD"/>'
                    + '<circle cx="12" cy="12" r="3"  fill="#fff"/></svg>',
                iconSize:   [24, 24],
                iconAnchor: [12, 12],
            });
            navUserMarker = L.marker(latlng, { icon: icon, zIndexOffset: 900 }).addTo(map);
        } else {
            navUserMarker.setLatLng(latlng);
        }
    }
    function navClearUserMarker() {
        if (navUserMarker) { map.removeLayer(navUserMarker); navUserMarker = null; }
    }

    /* ── Destination marker ───────────────────────────────────── */
    function navSetDestMarker(latlng) {
        if (navDestMarker) map.removeLayer(navDestMarker);
        var icon = L.divIcon({
            className: 'banhawy-pin',
            html: '<svg viewBox="0 0 28 32" width="34" height="38">'
                + '<path d="M14 0a14 14 0 0 0-14 14c0 10 14 18 14 18s14-8 14-18A14 14 0 0 0 14 0z" fill="#001B2A"/>'
                + '<circle cx="14" cy="14" r="5" fill="#0D9488"/></svg>',
            iconSize:   [34, 38],
            iconAnchor: [17, 38],
        });
        navDestMarker = L.marker(latlng, { icon: icon, zIndexOffset: 800 }).addTo(map);
    }
    function navClearDestMarker() {
        if (navDestMarker) { map.removeLayer(navDestMarker); navDestMarker = null; }
    }

    /* ── Filter alert markers to only those near the route ───── */
    function navHighlightAlertsOnRoute() {
        navOnRouteIds = [];
        Object.keys(alertMarkers).forEach(function (id) {
            var entry = alertMarkers[id];
            var d = navDistanceToRoute(entry.alert.lat, entry.alert.lng);
            var el = entry.marker.getElement();
            if (d <= NAV_ON_ROUTE_M) {
                navOnRouteIds.push(parseInt(id, 10));
                if (el) el.classList.remove('is-off-route');
            } else {
                if (el) el.classList.add('is-off-route');
            }
        });
    }
    function navResetAlertHighlight() {
        Object.keys(alertMarkers).forEach(function (id) {
            var el = alertMarkers[id].marker.getElement();
            if (el) el.classList.remove('is-off-route');
        });
    }

    /* ── Preview card population ──────────────────────────────── */
    function navFillPreview(distance, duration) {
        document.getElementById('nav-preview-distance').textContent = navFormatDistance(distance);
        document.getElementById('nav-preview-duration').textContent = navFormatDuration(duration);
        document.getElementById('nav-preview-count').textContent    = navOnRouteIds.length;
        var typesEl = document.getElementById('nav-preview-types');
        var emojis = navOnRouteIds.map(function (id) { return alertMarkers[id]?.alert?.type_icon || ''; }).filter(Boolean);
        typesEl.textContent = emojis.slice(0, 6).join(' ');
    }

    /* ── Start picking destination ────────────────────────────── */
    navStartBtn?.addEventListener('click', function () {
        navSetState('picking');
    });
    navCancelPick?.addEventListener('click', function () {
        navSetState('idle');
    });
    navConfirmPick?.addEventListener('click', function () {
        var c = map.getCenter();
        navDest = { lat: c.lat, lng: c.lng, name: 'وجهة مخصصة' };
        navSetDestMarker(navDest);
        navBuildRoute();
        // Save to recent destinations
        navPushRecent('وجهة ' + new Date().toLocaleDateString('ar-EG'), c.lat, c.lng);
    });

    /* ── Build route from current GPS → destination ───────────── */
    function navBuildRoute() {
        navSetState('preview');
        document.getElementById('nav-preview-distance').textContent = '...';
        document.getElementById('nav-preview-duration').textContent = '...';

        navGetCurrentPosition()
            .then(function (pos) {
                navUserPos = pos;
                navSetUserMarker([pos.lat, pos.lng]);
                return navFetchRoute(pos, navDest);
            })
            .then(function (route) {
                navRouteCoords    = route.coords;
                navRouteSteps     = route.steps || [];
                navCurrentStepIdx = 0;
                navDrawRoute(route.coords);
                navHighlightAlertsOnRoute();
                navFillPreview(route.distance, route.duration);

                // Fit map to route
                var bounds = L.latLngBounds(route.coords);
                map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
            })
            .catch(function (err) {
                showAlertToast('تعذّر حساب الطريق · تأكد من تفعيل الموقع.');
                navSetState('idle');
                navClearRoute();
                navClearDestMarker();
            });
    }

    navCancelPrev?.addEventListener('click', function () {
        navClearRoute();
        navClearDestMarker();
        navResetAlertHighlight();
        navUserPos = null;
        navDest = null;
        navSetState('idle');
    });

    /* ── Start live navigation ────────────────────────────────── */
    navStartTrip?.addEventListener('click', function () {
        navSetState('active');
        navCurrentStepIdx   = 0;
        navOffRouteSince    = 0;
        navAutoFollow       = true;
        navTripStartTs      = Date.now();
        navMeters           = 0;
        navHasArrived       = false;
        navWarnedAlerts     = {};

        // Keep the screen awake during the trip (needs a user gesture)
        navAcquireWakeLock();

        navUpdateManeuverBar();
        navUpdateActiveBar();
        navStartWatch();
        navStartPolling();
        // Zoom in for driving view
        if (navUserPos) {
            map.setView([navUserPos.lat, navUserPos.lng], 16, { animate: true });
        }
    });

    /* ── Saved destinations (localStorage) ─────────────────────── */
    var NAV_STORE_KEY = 'banhawy_nav_v1';
    function navLoadStore() {
        try {
            var raw = localStorage.getItem(NAV_STORE_KEY);
            if (!raw) return { home: null, work: null, recent: [] };
            var s = JSON.parse(raw) || {};
            return { home: s.home || null, work: s.work || null, recent: Array.isArray(s.recent) ? s.recent : [] };
        } catch (e) { return { home: null, work: null, recent: [] }; }
    }
    function navSaveStore(s) {
        try { localStorage.setItem(NAV_STORE_KEY, JSON.stringify(s)); } catch (e) {}
    }
    function navPushRecent(name, lat, lng) {
        var s = navLoadStore();
        // De-dupe by ~5m proximity
        s.recent = s.recent.filter(function (r) {
            return navDistanceMeters(r.lat, r.lng, lat, lng) > 25;
        });
        s.recent.unshift({ name: name || 'وجهة', lat: lat, lng: lng, ts: Date.now() });
        s.recent = s.recent.slice(0, 6);
        navSaveStore(s);
    }
    function navSetFavorite(kind, name, lat, lng) {
        var s = navLoadStore();
        s[kind] = { name: name, lat: lat, lng: lng, ts: Date.now() };
        navSaveStore(s);
    }
    function navRenderSavedSheet() {
        var s = navLoadStore();
        var homeAddr = document.getElementById('nav-saved-home-addr');
        var workAddr = document.getElementById('nav-saved-work-addr');
        var homeTile = document.querySelector('.nav-saved-tile[data-kind="home"]');
        var workTile = document.querySelector('.nav-saved-tile[data-kind="work"]');

        if (s.home) {
            homeAddr.textContent = s.home.name || 'محفوظ';
            homeTile.classList.add('has-coords');
        } else {
            homeAddr.textContent = '— اضغط للحفظ';
            homeTile.classList.remove('has-coords');
        }
        if (s.work) {
            workAddr.textContent = s.work.name || 'محفوظ';
            workTile.classList.add('has-coords');
        } else {
            workAddr.textContent = '— اضغط للحفظ';
            workTile.classList.remove('has-coords');
        }

        var recentEl = document.getElementById('nav-saved-recent');
        var emptyEl  = document.getElementById('nav-saved-empty');
        recentEl.innerHTML = '';
        if (!s.recent.length) {
            emptyEl.hidden = false;
            recentEl.hidden = true;
            return;
        }
        emptyEl.hidden = true;
        recentEl.hidden = false;
        s.recent.forEach(function (r, idx) {
            var row = document.createElement('div');
            row.className = 'nav-recent-row';
            row.innerHTML =
                '<span class="recent-ico"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="11" r="3"/><path d="M12 22s8-7 8-12a8 8 0 1 0-16 0c0 5 8 12 8 12z"/></svg></span>' +
                '<span class="recent-name">' + (r.name || 'وجهة') + '</span>' +
                '<button class="recent-del" data-del="' + idx + '" aria-label="حذف">×</button>';
            row.addEventListener('click', function (e) {
                if (e.target.closest('.recent-del')) return;
                navTriggerFromSaved(r);
            });
            recentEl.appendChild(row);
        });
        recentEl.querySelectorAll('.recent-del').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var i = +btn.dataset.del;
                var st = navLoadStore();
                st.recent.splice(i, 1);
                navSaveStore(st);
                navRenderSavedSheet();
            });
        });

        // Wire favorite tile clicks
        [homeTile, workTile].forEach(function (tile) {
            tile.onclick = function () {
                var kind = tile.dataset.kind;
                var saved = navLoadStore()[kind];
                if (saved) {
                    navTriggerFromSaved(saved);
                } else {
                    // Prompt to save current map center as this kind
                    if (NAV_STATE === 'idle') {
                        var name = prompt('اكتب اسم ' + (kind === 'home' ? 'البيت' : 'الشغل') + ' (اختياري)') || (kind === 'home' ? 'البيت' : 'الشغل');
                        var c = map.getCenter();
                        navSetFavorite(kind, name, c.lat, c.lng);
                        navRenderSavedSheet();
                    }
                }
            };
        });
    }
    function navTriggerFromSaved(saved) {
        document.getElementById('nav-saved-sheet').hidden = true;
        // Jump straight to preview
        if (!navUserPos) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    navUserPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                    navStartPreviewTo(saved);
                }, function () { alert('احنا محتاجين موقعك عشان نحسبلك الطريق'); }, { enableHighAccuracy: true });
            }
        } else {
            navStartPreviewTo(saved);
        }
    }
    function navStartPreviewTo(saved) {
        navDest = { lat: saved.lat, lng: saved.lng, name: saved.name || 'وجهة' };
        navSetDestMarker(navDest);
        navBuildRoute();   // reuses existing pipeline: preview state + fetch + fit
    }

    // Open/close sheet
    document.getElementById('nav-saved-btn')?.addEventListener('click', function () {
        navRenderSavedSheet();
        document.getElementById('nav-saved-sheet').hidden = false;
    });
    document.getElementById('nav-saved-close')?.addEventListener('click', function () {
        document.getElementById('nav-saved-sheet').hidden = true;
    });
    document.getElementById('nav-saved-pick')?.addEventListener('click', function () {
        document.getElementById('nav-saved-sheet').hidden = true;
        navStartBtn?.click();
    });

    /* ── Arrival screen dismiss ────────────────────────────────── */
    document.getElementById('nav-arrived-close')?.addEventListener('click', function () {
        document.getElementById('nav-arrived').hidden = true;
        // Auto-end the trip on dismiss
        navStopBtn?.click();
    });

    function navStartWatch() {
        if (!navigator.geolocation) return;
        navStopWatch();
        navWatchId = navigator.geolocation.watchPosition(
            function (pos) {
                var newPos = { lat: pos.coords.latitude, lng: pos.coords.longitude };

                // Distance travelled increment (Haversine on each tick)
                if (navUserPos) {
                    var step = navDistanceMeters(navUserPos.lat, navUserPos.lng, newPos.lat, newPos.lng);
                    // Filter out GPS jitter < 5m
                    if (step >= 5) navMeters += step;
                }

                // Speed: prefer GPS-provided (m/s → km/h), else compute from deltas
                if (typeof pos.coords.speed === 'number' && pos.coords.speed >= 0) {
                    navCurrentSpeed = pos.coords.speed * 3.6;
                } else if (navUserPos && navLastPos) {
                    var dt = (Date.now() - (navLastPos.ts || 0)) / 1000;
                    var dm = navDistanceMeters(navLastPos.lat, navLastPos.lng, newPos.lat, newPos.lng);
                    if (dt > 0.5 && dt < 30) navCurrentSpeed = (dm / dt) * 3.6;
                }
                navLastPos = { lat: newPos.lat, lng: newPos.lng, ts: Date.now() };

                navUserPos = newPos;
                navSetUserMarker([navUserPos.lat, navUserPos.lng]);
                navAutoFollowPan();
                navAdvanceCurrentStep();
                navCheckArrived();
                navCheckProximity();
                navCheckOffRoute();
                navUpdateManeuverBar();
                navUpdateActiveBar();
            },
            function () { /* silent fail — keep last known */ },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
        );
    }

    /* ── Arrival detection ──────────────────────────────────── */
    function navCheckArrived() {
        if (navHasArrived || !navDest || !navUserPos) return;
        var d = navDistanceMeters(navUserPos.lat, navUserPos.lng, navDest.lat, navDest.lng);
        if (d <= 40) {
            navHasArrived = true;
            navShowArrivalScreen();
        }
    }

    function navShowArrivalScreen() {
        var screen = document.getElementById('nav-arrived');
        if (!screen) return;
        var elapsedMs   = Date.now() - (navTripStartTs || Date.now());
        var elapsedMin  = Math.max(1, Math.round(elapsedMs / 60000));
        var alertsHit   = Object.keys(navWarnedAlerts).length;
        document.getElementById('nav-arrived-dist').textContent   = (navMeters >= 1000 ? (navMeters/1000).toFixed(1) + ' كم' : Math.round(navMeters) + ' متر');
        document.getElementById('nav-arrived-time').textContent   = elapsedMin + ' دقيقة';
        document.getElementById('nav-arrived-alerts').textContent = alertsHit;
        screen.hidden = false;
    }

    /* ── Auto-follow: pan map to user position smoothly ───────── */
    function navAutoFollowPan() {
        if (!navAutoFollow || !navUserPos) return;
        // Use animated pan; keep current zoom
        map.panTo([navUserPos.lat, navUserPos.lng], { animate: true, duration: 0.6 });
    }

    // User-initiated map drag temporarily disables auto-follow,
    // then re-enables after 8s of inactivity
    var navFollowResumeTimer = null;
    map.on('dragstart', function () {
        if (NAV_STATE !== 'active') return;
        navAutoFollow = false;
        clearTimeout(navFollowResumeTimer);
        navFollowResumeTimer = setTimeout(function () {
            navAutoFollow = true;
            navAutoFollowPan();
        }, 8000);
    });

    /* ── Step advancement: find which step the user is at ─────── */
    function navAdvanceCurrentStep() {
        if (!navUserPos || !navRouteSteps.length) return;
        // Move forward through steps as the user passes their maneuver points
        // A step is "passed" when user is within 30m of the NEXT step's location
        // OR has moved significantly past the current step.
        while (navCurrentStepIdx < navRouteSteps.length - 1) {
            var next = navRouteSteps[navCurrentStepIdx + 1];
            var dToNext = navDistanceMeters(
                navUserPos.lat, navUserPos.lng,
                next.location[0], next.location[1]
            );
            // Past the next-step location → advance
            if (dToNext < 25) {
                navCurrentStepIdx++;
                continue;
            }
            break;
        }
    }

    /* ── Update the top maneuver banner ───────────────────────── */
    function navUpdateManeuverBar() {
        if (!navRouteSteps.length) return;
        var step = navRouteSteps[navCurrentStepIdx];
        if (!step) return;

        var iconEl = document.getElementById('nav-maneuver-icon');
        var textEl = document.getElementById('nav-maneuver-text');
        var distEl = document.getElementById('nav-maneuver-distance');

        // Use the NEXT step's instruction (driver wants to know what's ahead)
        var nextStep = navRouteSteps[navCurrentStepIdx + 1] || step;

        navRenderManeuverIcon(iconEl, nextStep);
        textEl.textContent = navManeuverText(nextStep);

        // Distance to that next maneuver
        var d = null;
        if (navUserPos && nextStep) {
            d = navDistanceMeters(
                navUserPos.lat, navUserPos.lng,
                nextStep.location[0], nextStep.location[1]
            );
            distEl.textContent = 'بعد ' + navFormatDistance(d);
        } else {
            distEl.textContent = '';
        }

        // ── Voice guidance ──
        // Speak at 3 distance buckets: ≤500m, ≤200m, ≤50m (immediate)
        // Use stepIdx + bucket as a dedupe key so we don't repeat.
        if (d !== null && nextStep) {
            var bucket = null;
            if (d <= 50)       bucket = 'now';
            else if (d <= 200) bucket = '200';
            else if (d <= 500) bucket = '500';

            if (bucket && (navVoice.lastTurn.stepIdx !== navCurrentStepIdx || navVoice.lastTurn.bucket !== bucket)) {
                navVoice.lastTurn = { stepIdx: navCurrentStepIdx, bucket: bucket };
                var text = navVoiceTextForStep(nextStep, d, bucket);
                if (text) navVoice.speak(text, { interrupt: bucket === 'now' });
            }
        }
    }

    /* ── Translate a step into spoken Arabic text ───────────────── */
    function navVoiceTextForStep(step, distMeters, bucket) {
        if (!step) return '';
        if (step.type === 'arrive') {
            return bucket === 'now' ? 'وصلت إلى وجهتك' : 'اقتربت من الوجهة، على بعد ' + Math.round(distMeters) + ' متر';
        }
        var m = step.modifier || '';
        var dir = '';
        if (m.includes('right'))      dir = m === 'slight right' ? 'مايل يمين' : (m === 'sharp right' ? 'يمين قوي' : 'يمين');
        else if (m.includes('left'))  dir = m === 'slight left'  ? 'مايل شمال' : (m === 'sharp left'  ? 'شمال قوي' : 'شمال');
        else if (m === 'uturn')       dir = 'دوران لف كامل';
        else if (m === 'straight')    dir = 'استمر';
        else                          dir = 'استمر';

        var prefix = (bucket === 'now')   ? 'دلوقتي '
                   : (bucket === '200')   ? 'بعد مئتين متر '
                                          : 'بعد ' + (distMeters > 1000 ? (distMeters/1000).toFixed(1) + ' كيلو ' : Math.round(distMeters / 100) * 100 + ' متر ');
        if (step.type === 'roundabout' || step.type === 'rotary') return prefix + 'ادخل الدوران';
        if (step.type === 'continue' || step.type === 'new name') return prefix + 'استمر دوغري';
        if (step.type === 'merge')                                return prefix + 'اندمج ' + dir;
        if (step.type === 'fork')                                 return prefix + 'خد التحويلة ' + dir;
        if (step.type === 'on ramp' || step.type === 'off ramp')  return prefix + 'خد المخرج ' + dir;
        return prefix + 'خد ' + dir;
    }

    /* ── Off-route detection → auto-recalculate ───────────────── */
    function navCheckOffRoute() {
        if (!navUserPos || !navRouteCoords || navIsRecalculating) return;
        var d = navDistanceToRoute(navUserPos.lat, navUserPos.lng);
        if (d <= NAV_OFF_ROUTE_M) {
            navOffRouteSince = 0;
            if (navOffRouteToast) navOffRouteToast.hidden = true;
            return;
        }
        // Mark when we first went off-route
        if (navOffRouteSince === 0) {
            navOffRouteSince = Date.now();
            return;
        }
        // After 4s of being off-route → recalculate
        if (Date.now() - navOffRouteSince > 4000) {
            navOffRouteSince = 0;
            navAutoRecalculate();
        }
    }

    function navAutoRecalculate() {
        if (!navDest || !navUserPos || navIsRecalculating) return;
        navIsRecalculating = true;
        if (navOffRouteToast) {
            navOffRouteToast.hidden = false;
            clearTimeout(navOffRouteToast._t);
            navOffRouteToast._t = setTimeout(function () { navOffRouteToast.hidden = true; }, 3000);
        }
        navVoice.speak('خرجت عن المسار. جاري إعادة الحساب.', { interrupt: true });
        navVoice.lastTurn = { stepIdx: -1, bucket: '' }; // reset so new route announces

        navFetchRoute(navUserPos, navDest)
            .then(function (route) {
                navRouteCoords    = route.coords;
                navRouteSteps     = route.steps || [];
                navCurrentStepIdx = 0;
                navDrawRoute(route.coords);
                navHighlightAlertsOnRoute();
                navUpdateManeuverBar();
                navUpdateActiveBar();
            })
            .catch(function () { /* keep old route — best-effort */ })
            .finally(function () { navIsRecalculating = false; });
    }
    function navStopWatch() {
        if (navWatchId !== null) {
            navigator.geolocation.clearWatch(navWatchId);
            navWatchId = null;
        }
    }

    /* ── Proximity warnings (≤ NAV_PROXIMITY_M) ───────────────── */
    function navCheckProximity() {
        if (!navUserPos) return;
        var nearest = null, nearestDist = Infinity;
        navOnRouteIds.forEach(function (id) {
            var a = alertMarkers[id]?.alert;
            if (!a) return;
            var d = navDistanceMeters(navUserPos.lat, navUserPos.lng, a.lat, a.lng);
            if (d < nearestDist) { nearestDist = d; nearest = a; }
        });
        if (nearest && nearestDist <= NAV_PROXIMITY_M) {
            var last = navWarnedAlerts[nearest.id] || 0;
            if (Date.now() - last > 60000) {       // throttle: same alert max once per minute
                navWarnedAlerts[nearest.id] = Date.now();
                navShowProximityToast(nearest, nearestDist);
            }
        }
    }
    function navShowProximityToast(alert, dist) {
        document.getElementById('nav-proximity-emoji').textContent = alert.type_icon || '⚠';
        document.getElementById('nav-proximity-title').textContent = 'تنبيه قريب: ' + alert.type_label;
        document.getElementById('nav-proximity-dist').textContent  = 'على بُعد ' + Math.round(dist) + ' متر';
        navProximityToast.hidden = false;
        clearTimeout(navProximityToast._t);
        navProximityToast._t = setTimeout(function () { navProximityToast.hidden = true; }, 4500);

        // Voice warning — rounded to nearest 50m for natural speech
        var dRounded = Math.max(50, Math.round(dist / 50) * 50);
        navVoice.speak('تنبيه! ' + (alert.type_label || 'خطر') + ' على بعد ' + dRounded + ' متر', { interrupt: false });
    }

    /* ── Update the bottom navigation card ────────────────────── */
    function navUpdateActiveBar() {
        var countEl     = document.getElementById('nav-alerts-count');
        var remainingEl = document.getElementById('nav-remaining');
        var etaEl       = document.getElementById('nav-eta');
        var speedEl     = document.getElementById('nav-speed');
        var nearestRow  = document.getElementById('nav-nearest-alert');

        // Alerts on route count
        var count = navOnRouteIds.length;
        if (countEl) countEl.textContent = count;

        // Remaining distance + ETA
        var remaining = navComputeRemainingDistance();
        if (remainingEl) remainingEl.textContent = navFormatDistance(remaining);

        if (etaEl) {
            // ETA = now + (remaining / avg_speed)
            // Use current speed when > 5 km/h, else fall back to 30 km/h urban avg
            var speedKmh = navCurrentSpeed > 5 ? navCurrentSpeed : 30;
            var minutes  = (remaining / 1000) / speedKmh * 60;
            if (remaining > 0 && minutes < 240) {
                var arriveAt = new Date(Date.now() + minutes * 60000);
                var h = arriveAt.getHours(), m = arriveAt.getMinutes();
                var ampm = h >= 12 ? 'م' : 'ص';
                var h12  = ((h % 12) || 12);
                etaEl.innerHTML = h12 + ':' + (m < 10 ? '0' : '') + m + '<span style="font-size: 10px; font-weight: 700; color: var(--ink-3);"> ' + ampm + '</span>';
            } else {
                etaEl.textContent = '—';
            }
        }

        if (speedEl) {
            var sp = Math.max(0, Math.round(navCurrentSpeed));
            speedEl.innerHTML = sp + '<span style="font-size: 10px; font-weight: 700; color: var(--ink-3);"> كم/س</span>';
        }

        // Nearest alert info
        if (!navUserPos || count === 0) {
            if (nearestRow) nearestRow.hidden = true;
            return;
        }
        var nearest = null, nearestDist = Infinity;
        navOnRouteIds.forEach(function (id) {
            var a = alertMarkers[id]?.alert;
            if (!a) return;
            var d = navDistanceMeters(navUserPos.lat, navUserPos.lng, a.lat, a.lng);
            if (d < nearestDist) { nearestDist = d; nearest = a; }
        });
        if (nearest && nearestRow) {
            nearestRow.hidden = false;
            document.getElementById('nav-nearest-emoji').textContent = nearest.type_icon;
            document.getElementById('nav-nearest-label').textContent = nearest.type_label;
            document.getElementById('nav-nearest-dist').textContent  = ' · بعد ' + navFormatDistance(nearestDist);
        }
    }

    /* ── Distance from user to end of route ──────────────────── */
    function navComputeRemainingDistance() {
        if (!navUserPos || !navRouteCoords || !navRouteCoords.length) return 0;
        // Find closest route point to user
        var closestIdx = 0, closestDist = Infinity;
        for (var i = 0; i < navRouteCoords.length; i++) {
            var d = navDistanceMeters(
                navUserPos.lat, navUserPos.lng,
                navRouteCoords[i][0], navRouteCoords[i][1]
            );
            if (d < closestDist) { closestDist = d; closestIdx = i; }
        }
        // Sum distances from there to the end
        var total = 0;
        for (var j = closestIdx; j < navRouteCoords.length - 1; j++) {
            total += navDistanceMeters(
                navRouteCoords[j][0], navRouteCoords[j][1],
                navRouteCoords[j+1][0], navRouteCoords[j+1][1]
            );
        }
        return total;
    }

    /* ── Realtime polling for alerts — production-grade ─────────
     *   - Bounding-box query so the server only returns visible markers
     *   - Delta polling via `since` so we only get rows that changed
     *   - Exponential backoff on errors (12s → 30s → 60s → 120s → 120s …)
     *   - Auto-restart on visibility change (page wake)
     *   - Full refresh every ~5 minutes to recover from any drift
     * ─────────────────────────────────────────────────────────── */
    var navLastServerTime = null;
    var navPollFails      = 0;
    var navFullResyncEvery= 5 * 60 * 1000;  // 5 minutes
    var navLastFullSync   = 0;

    function navStartPolling() {
        navStopPolling();
        navPollFails = 0;
        navLastServerTime = null;
        navScheduleNextPoll(0);
    }
    function navStopPolling() {
        if (navPollTimer) { clearTimeout(navPollTimer); navPollTimer = null; }
    }
    function navScheduleNextPoll(delayOverride) {
        navStopPolling();
        // Exponential backoff (caps at 120s)
        var backoff = Math.min(NAV_POLL_INTERVAL * Math.pow(2, navPollFails), 120000);
        var delay   = (typeof delayOverride === 'number') ? delayOverride : backoff;
        navPollTimer = setTimeout(navPollAlerts, delay);
    }

    function navCurrentBoundsParam() {
        // Use the map's visible viewport, slightly inflated so markers just
        // outside the edge are pre-loaded for smoother panning.
        var b = map.getBounds().pad(0.2);
        return [
            b.getSouth().toFixed(5),
            b.getWest().toFixed(5),
            b.getNorth().toFixed(5),
            b.getEast().toFixed(5),
        ].join(',');
    }

    function navPollAlerts() {
        var now = Date.now();
        var forceFull = (now - navLastFullSync) > navFullResyncEvery;

        var params = new URLSearchParams();
        params.set('bounds', navCurrentBoundsParam());
        if (navLastServerTime && !forceFull) params.set('since', navLastServerTime);

        var url = '/alerts/active?' + params.toString();
        fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (r) {
                if (!r.ok) throw new Error('http ' + r.status);
                return r.json();
            })
            .then(function (data) {
                navPollFails = 0; // reset backoff on success
                navLastServerTime = data.server_time || null;

                var fresh   = data.alerts || [];
                var isDelta = !!navLastServerTime && !forceFull;

                fresh.forEach(function (a) {
                    if (alertMarkers[a.id]) {
                        // Update in place — keeps the marker DOM node stable
                        alertMarkers[a.id].alert = a;
                        alertMarkers[a.id].marker.setPopupContent(alertPopupHtml(a));
                        var el = alertMarkers[a.id].marker.getElement();
                        if (el) {
                            // Refresh confirmed-badge state without recreating the icon
                            var pin = el.querySelector('.alert-marker-pin');
                            if (pin) pin.classList.toggle('is-confirmed', !!a.is_confirmed);
                        }
                        // If status changed to inactive, remove
                        if (a.status !== 'active') {
                            map.removeLayer(alertMarkers[a.id].marker);
                            delete alertMarkers[a.id];
                        }
                    } else if (a.status === 'active') {
                        alertMarkers[a.id] = { marker: buildAlertMarker(a), alert: a };
                    }
                });

                // On a FULL refresh, prune markers the server no longer returned
                if (forceFull) {
                    var freshIds = {};
                    fresh.forEach(function (a) { freshIds[a.id] = true; });
                    Object.keys(alertMarkers).forEach(function (id) {
                        if (!freshIds[id]) {
                            map.removeLayer(alertMarkers[id].marker);
                            delete alertMarkers[id];
                        }
                    });
                    navLastFullSync = now;
                }

                // Re-evaluate route context
                if (NAV_STATE === 'active' && navRouteCoords) {
                    navHighlightAlertsOnRoute();
                    navCheckProximity();
                    navUpdateActiveBar();
                }
            })
            .catch(function (err) {
                navPollFails = Math.min(navPollFails + 1, 4);
            })
            .finally(function () {
                // Schedule the next tick only while we're still in active mode
                if (NAV_STATE === 'active') navScheduleNextPoll();
            });
    }

    // Pause polling when the page is hidden (saves battery + bandwidth),
    // resume immediately when it comes back.
    document.addEventListener('visibilitychange', function () {
        if (NAV_STATE !== 'active') return;
        if (document.hidden) {
            navStopPolling();
        } else {
            navScheduleNextPoll(0);
        }
    });

    // Also re-poll when the user finishes panning the map — they probably want
    // to see what alerts are in the new viewport.
    var navMapMoveTimer = null;
    map.on('moveend', function () {
        if (NAV_STATE !== 'active') return;
        clearTimeout(navMapMoveTimer);
        navMapMoveTimer = setTimeout(function () { navScheduleNextPoll(0); }, 400);
    });

    /* ── Stop / recalc ────────────────────────────────────────── */
    navStopBtn?.addEventListener('click', function () {
        navStopWatch();
        navStopPolling();
        navClearRoute();
        navClearDestMarker();
        navClearUserMarker();
        navResetAlertHighlight();
        navReleaseWakeLock();
        if ('speechSynthesis' in window) speechSynthesis.cancel();
        navUserPos          = null;
        navDest             = null;
        navRouteCoords      = null;
        navRouteSteps       = [];
        navCurrentStepIdx   = 0;
        navOnRouteIds       = [];
        navWarnedAlerts     = {};
        navOffRouteSince    = 0;
        navIsRecalculating  = false;
        navAutoFollow       = true;
        if (navOffRouteToast) navOffRouteToast.hidden = true;
        clearTimeout(navFollowResumeTimer);
        navSetState('idle');
    });

    navRecalcBtn?.addEventListener('click', function () {
        if (!navDest) return;
        navAutoFollow = true;
        navGetCurrentPosition()
            .then(function (pos) {
                navUserPos = pos;
                navSetUserMarker([pos.lat, pos.lng]);
                return navFetchRoute(pos, navDest);
            })
            .then(function (route) {
                navRouteCoords    = route.coords;
                navRouteSteps     = route.steps || [];
                navCurrentStepIdx = 0;
                navDrawRoute(route.coords);
                navHighlightAlertsOnRoute();
                navUpdateManeuverBar();
                navUpdateActiveBar();
                showAlertToast('تم إعادة حساب الطريق ✓');
            })
            .catch(function () {
                showAlertToast('تعذّر إعادة حساب الطريق.');
            });
    });

    // ── Original alert submit handler (left intact) ──
    submitBtn?.addEventListener('click', function () {
        if (!pickedType || !pickedLoc) return;
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الإرسال...';

        fetch('/alerts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                type: pickedType,
                lat:  pickedLoc.lat,
                lng:  pickedLoc.lng,
                description: desc.value || null,
            }),
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
        .then(function (res) {
            if (!res.ok) {
                showAlertToast(res.body?.error || 'حصلت مشكلة، حاول تاني.');
                return;
            }
            var a = res.body.alert;
            alertMarkers[a.id] = { marker: buildAlertMarker(a), alert: a };
            closeDialog();
            desc.value = '';
            descCount.textContent = '0';
            pickedType = null;
            pickedLoc  = null;
            map.setView([a.lat, a.lng], 16);
            showAlertToast('شكراً — تم نشر التنبيه ✓');
        })
        .catch(function () {
            showAlertToast('مفيش اتصال. حاول تاني.');
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = 'إرسال البلاغ';
        });
    });
})();
</script>
@endpush
