@extends('layouts.mobile')

@section('title', 'الإعدادات · ' . $business->name)
@section('page-title', 'إعدادات النشاط')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">الإعدادات</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<form method="post" action="{{ route('merchant.settings.update') }}" class="scroll" style="padding: 12px 14px 28px;">
    @csrf @method('PATCH')

    {{-- ── ORDERS CHANNEL ─────────────────────────────────── --}}
    <div class="card" style="padding: 16px; margin-bottom: 12px;">
        <div class="label-strong" style="margin-bottom: 4px;">استقبال الطلبات</div>
        <p class="muted" style="margin-bottom: 14px;">اختر إزاي عايز الطلبات الجديدة توصلك.</p>

        <div class="setting-options">
            <label class="setting-opt">
                <input type="radio" name="orders_via" value="whatsapp" @if($business->orders_via === 'whatsapp') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-wa"><x-icon name="whatsapp" :size="18" stroke="#1FB855" w="2"/></span>
                        <span class="setting-name">واتساب فقط</span>
                    </div>
                    <p class="setting-desc">العميل بيتحول مباشرة لواتساب وبيوصلك الطلب كرسالة.</p>
                </div>
            </label>

            <label class="setting-opt">
                <input type="radio" name="orders_via" value="web" @if($business->orders_via === 'web') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-navy"><x-icon name="cart" :size="18" stroke="#001B2A"/></span>
                        <span class="setting-name">الموقع فقط</span>
                    </div>
                    <p class="setting-desc">الطلب بيتسجل في لوحة التحكم بس، وانت بتتواصل مع العميل من عندك.</p>
                </div>
            </label>

            <label class="setting-opt">
                <input type="radio" name="orders_via" value="both" @if($business->orders_via === 'both') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-teal"><x-icon name="check" :size="18" stroke="#0D9488" w="2.4"/></span>
                        <span class="setting-name">الاتنين</span>
                        <span class="chip teal" style="padding: 1px 7px; font-size: 9px; margin-right: auto;">يُنصح به</span>
                    </div>
                    <p class="setting-desc">العميل بيختار: واتساب أو الموقع. الطلب يتسجل في الحالتين.</p>
                </div>
            </label>
        </div>
    </div>

    {{-- ── BOOKINGS CHANNEL ───────────────────────────────── --}}
    <div class="card" style="padding: 16px; margin-bottom: 12px;">
        <div class="label-strong" style="margin-bottom: 4px;">استقبال الحجوزات</div>
        <p class="muted" style="margin-bottom: 14px;">للأنشطة اللي بتاخد مواعيد (صالونات/عيادات).</p>

        <div class="setting-options">
            <label class="setting-opt">
                <input type="radio" name="bookings_via" value="whatsapp" @if($business->bookings_via === 'whatsapp') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-wa"><x-icon name="whatsapp" :size="18" stroke="#1FB855" w="2"/></span>
                        <span class="setting-name">واتساب فقط</span>
                    </div>
                </div>
            </label>

            <label class="setting-opt">
                <input type="radio" name="bookings_via" value="web" @if($business->bookings_via === 'web') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-navy"><x-icon name="calendar" :size="18" stroke="#001B2A"/></span>
                        <span class="setting-name">الموقع فقط</span>
                    </div>
                </div>
            </label>

            <label class="setting-opt">
                <input type="radio" name="bookings_via" value="both" @if($business->bookings_via === 'both') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-teal"><x-icon name="check" :size="18" stroke="#0D9488" w="2.4"/></span>
                        <span class="setting-name">الاتنين</span>
                    </div>
                </div>
            </label>

            <label class="setting-opt">
                <input type="radio" name="bookings_via" value="walkin" @if($business->bookings_via === 'walkin') checked @endif>
                <div class="setting-card">
                    <div class="setting-head">
                        <span class="setting-ico setting-ico-navy"><x-icon name="pin" :size="18" stroke="#001B2A"/></span>
                        <span class="setting-name">بدون حجز — تواجد فقط</span>
                    </div>
                    <p class="setting-desc">الزبون يجي على المحل مباشرة في مواعيد العمل.</p>
                </div>
            </label>
        </div>
    </div>

    {{-- ── HOURS PER DAY ──────────────────────────────────── --}}
    @php
        $dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $hours    = $business->hours ?? [];
    @endphp
    <div class="card" style="padding: 16px; margin-bottom: 12px;">
        <div class="label-strong" style="margin-bottom: 4px;">ساعات العمل</div>
        <p class="muted" style="margin-bottom: 14px;">حدد مواعيد كل يوم على حدة.</p>

        <div class="hours-list">
            @foreach($dayNames as $d => $name)
                @php $row = $hours[$d] ?? ['open' => '10:00', 'close' => '22:00', 'closed' => false]; @endphp
                <div class="hours-row" data-day="{{ $d }}" @if($row['closed']) data-closed @endif>
                    <div class="hours-day">{{ $name }}</div>

                    <label class="hours-toggle">
                        <input type="checkbox" name="hours[{{ $d }}][closed]" value="1" @if($row['closed']) checked @endif>
                        <span class="hours-toggle-slider"></span>
                        <span class="hours-toggle-label">مغلق</span>
                    </label>

                    <div class="hours-times">
                        <input type="time" name="hours[{{ $d }}][open]"  value="{{ $row['open']  ?? '10:00' }}" step="900" class="hours-time" @if($row['closed']) disabled @endif>
                        <span class="hours-sep">–</span>
                        <input type="time" name="hours[{{ $d }}][close]" value="{{ $row['close'] ?? '22:00' }}" step="900" class="hours-time" @if($row['closed']) disabled @endif>
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" id="apply-all-days" class="btn btn-line btn-full" style="padding: 10px; font-size: 12px; margin-top: 12px;">
            انسخ ميعاد الأحد لباقي الأيام
        </button>
    </div>

    {{-- ── WHATSAPP NUMBER ────────────────────────────────── --}}
    <div class="card" style="padding: 16px; margin-bottom: 16px;">
        <div class="label-strong" style="margin-bottom: 4px;">رقم واتساب الأعمال</div>
        <p class="muted" style="margin-bottom: 12px;">الرقم اللي العملاء هيكلموك عليه.</p>
        <div class="field focused">
            <span style="color: var(--wa-600);"><x-icon name="whatsapp" :size="16" stroke="#1FB855"/></span>
            <input type="tel" name="whatsapp" value="{{ $business->whatsapp }}" required dir="ltr" style="text-align: right;">
        </div>
        @error('whatsapp') <div class="tiny" style="color: #B91C1C; margin-top: 6px;">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">
        حفظ الإعدادات
    </button>
</form>

@include('partials.merchant-nav')
@endsection

@push('head')
<style>
.setting-options { display: flex; flex-direction: column; gap: 10px; }
.setting-opt { display: block; cursor: pointer; }
.setting-opt input { position: absolute; opacity: 0; pointer-events: none; }
.setting-card {
    border: 1.5px solid var(--line-2);
    border-radius: 14px;
    padding: 12px 14px;
    transition: all .15s;
    background: white;
}
.setting-head { display: flex; align-items: center; gap: 10px; }
.setting-ico {
    width: 36px; height: 36px; border-radius: 10px;
    display: grid; place-items: center;
    flex-shrink: 0;
}
.setting-ico-wa   { background: rgba(37,211,102,.12); }
.setting-ico-navy { background: var(--gray-100); }
.setting-ico-teal { background: var(--teal-50); }
.setting-name { font-weight: 800; font-size: 13px; }
.setting-desc { font-size: 11px; color: var(--ink-3); font-weight: 600; line-height: 1.6; margin-top: 8px; padding-right: 46px; }
.setting-opt input:checked + .setting-card {
    border-color: var(--teal);
    background: var(--teal-50);
    box-shadow: 0 0 0 4px rgba(13,148,136,.08);
}

@media (min-width: 1024px) {
    .setting-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .setting-desc { padding-right: 0; }
}

/* ── Hours editor ──────────────────────────────────────────── */
.hours-list { display: flex; flex-direction: column; gap: 8px; }
.hours-row {
    display: grid;
    grid-template-columns: 60px 76px 1fr;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: var(--gray-50);
    border-radius: 10px;
    border: 1px solid var(--line);
}
.hours-row[data-closed] { background: #FEF2F2; border-color: #FECACA; }
.hours-day { font-weight: 800; font-size: 12px; color: var(--navy); }

.hours-toggle { display: inline-flex; align-items: center; gap: 6px; cursor: pointer; font-size: 11px; font-weight: 700; color: var(--ink-3); }
.hours-toggle input { position: absolute; opacity: 0; pointer-events: none; }
.hours-toggle-slider {
    width: 28px; height: 16px; border-radius: 999px;
    background: var(--gray-300); position: relative;
    transition: background .15s;
}
.hours-toggle-slider::after {
    content: ''; position: absolute; top: 2px; right: 2px;
    width: 12px; height: 12px; border-radius: 50%; background: white;
    transition: transform .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.15);
}
.hours-toggle input:checked + .hours-toggle-slider { background: #B91C1C; }
.hours-toggle input:checked + .hours-toggle-slider::after { transform: translateX(-12px); }
.hours-toggle-label { display: none; }
.hours-toggle input:checked ~ .hours-toggle-label { display: inline; color: #B91C1C; font-weight: 800; }

.hours-times { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
.hours-time {
    border: 1px solid var(--line-2); border-radius: 8px;
    padding: 6px 8px; font-size: 12px; font-weight: 700;
    background: white; color: var(--ink); font-family: inherit;
    direction: ltr; text-align: center;
    outline: none;
}
.hours-time:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.hours-time:disabled { opacity: .35; cursor: not-allowed; }
.hours-sep { color: var(--ink-4); font-weight: 700; }

@media (min-width: 1024px) {
    .hours-row { grid-template-columns: 100px 100px 1fr; padding: 12px 14px; }
    .hours-day { font-size: 13px; }
    .hours-time { padding: 8px 10px; font-size: 13px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.hours-row').forEach(function (row) {
        var toggle = row.querySelector('.hours-toggle input');
        if (!toggle) return;
        function sync() {
            var closed = toggle.checked;
            row.toggleAttribute('data-closed', closed);
            row.querySelectorAll('.hours-time').forEach(function (t) { t.disabled = closed; });
        }
        toggle.addEventListener('change', sync);
        sync();
    });

    var btn = document.getElementById('apply-all-days');
    if (btn) {
        btn.addEventListener('click', function () {
            var rows = document.querySelectorAll('.hours-row');
            if (rows.length < 2) return;
            var src = rows[0];
            var open   = src.querySelector('input[name$="[open]"]').value;
            var close  = src.querySelector('input[name$="[close]"]').value;
            var closed = src.querySelector('input[name$="[closed]"]').checked;
            rows.forEach(function (row, i) {
                if (i === 0) return;
                row.querySelector('input[name$="[open]"]').value  = open;
                row.querySelector('input[name$="[close]"]').value = close;
                var t = row.querySelector('input[name$="[closed]"]');
                t.checked = closed;
                t.dispatchEvent(new Event('change'));
            });
        });
    }
})();
</script>
@endpush
