@extends('layouts.mobile')

@section('title', 'المفقودات · بنهاوي')
@section('page-title', 'المفقودات')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">المفقودات</div>
    <a href="{{ route('lost.create') }}" class="ico-btn" aria-label="نشر بلاغ">
        <x-icon name="plus" :size="18"/>
    </a>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<div style="padding: 12px 14px 0;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
        <a href="{{ route('lost.create', ['kind' => 'lost']) }}" class="lost-cta lost-cta-lost">
            <span class="lost-cta-ico">
                <x-icon name="search-loc" :size="22" stroke="#B91C1C"/>
            </span>
            <div>
                <div class="lost-cta-title">حاجة ضاعت مني</div>
                <div class="lost-cta-sub">انشر بلاغ بحث</div>
            </div>
        </a>
        <a href="{{ route('lost.create', ['kind' => 'found']) }}" class="lost-cta lost-cta-found">
            <span class="lost-cta-ico">
                <x-icon name="check" :size="22" stroke="#047857"/>
            </span>
            <div>
                <div class="lost-cta-title">لقيت حاجة</div>
                <div class="lost-cta-sub">ساعد صاحبها يلاقيها</div>
            </div>
        </a>
    </div>

    <form method="get" id="lost-form" autocomplete="off">
        <label class="field" style="margin-bottom: 8px;">
            <span style="color: var(--ink-4);"><x-icon name="search" :size="16"/></span>
            <input type="search" name="q" id="lost-search" value="{{ $q }}" placeholder="ابحث عن شيء مفقود..." inputmode="search">
            <span class="tiny" id="lost-count" style="color: var(--ink-3);"></span>
        </label>

        <div class="chip-scroll-wrap" style="margin: 6px -14px 4px;">
            <div class="chip-scroll" style="padding: 0 14px;">
                <a href="{{ route('lost.index') }}" class="chip @if(! $kind) active @endif">الكل</a>
                <a href="{{ route('lost.index', ['kind' => 'lost']) }}"  class="chip @if($kind === 'lost') active @endif" style="@if($kind === 'lost') background: rgba(220,38,38,.10); color: #B91C1C; @endif">ضائع</a>
                <a href="{{ route('lost.index', ['kind' => 'found']) }}" class="chip @if($kind === 'found') active @endif" style="@if($kind === 'found') background: rgba(16,185,129,.14); color: #047857; @endif">موجود</a>
                @foreach(\App\Models\LostItem::CATEGORIES as $key => $label)
                    <a href="{{ route('lost.index', ['category' => $key, 'kind' => $kind]) }}" class="chip @if($category === $key) active @endif">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </form>
</div>

<div class="scroll" style="padding: 4px 14px 24px;">
    @forelse($items as $item)
        @php
            $isOpen = $item->status === 'open';
            $hay = mb_strtolower($item->title . ' ' . $item->description . ' ' . ($item->location ?? '') . ' ' . (\App\Models\LostItem::CATEGORIES[$item->category] ?? ''));
        @endphp
        @if($isOpen)
            <a href="{{ route('lost.show', $item) }}" class="card lost-card lost-item" data-haystack="{{ $hay }}">
        @else
            <div class="card lost-card is-closed lost-item" data-haystack="{{ $hay }}" aria-disabled="true">
        @endif
            @if($item->image)
                <div class="lost-card-img">
                    <img src="{{ $item->image }}" alt="" loading="lazy" onerror="this.parentNode.style.display='none'">
                </div>
            @endif
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: flex-start; gap: 8px;">
                    <div style="flex: 1; min-width: 0;">
                        <div class="lost-title">{{ $item->title }}</div>
                        <div class="lost-meta">
                            <span class="lost-cat">{{ \App\Models\LostItem::CATEGORIES[$item->category] ?? $item->category }}</span>
                            @if($item->location)
                                <span class="dot"></span>
                                <span><x-icon name="pin" :size="10" stroke="#5E6A77"/> {{ $item->location }}</span>
                            @endif
                        </div>
                    </div>
                    @if(! $isOpen)
                        <span class="kind-pill is-done">انتهى ✓</span>
                    @elseif($item->kind === 'lost')
                        <span class="kind-pill is-lost">ضايع</span>
                    @else
                        <span class="kind-pill is-found">موجود</span>
                    @endif
                </div>
                <p class="lost-desc">{{ Str::limit($item->description, 100) }}</p>
                <div class="lost-foot">
                    @if($item->reward && $item->kind === 'lost' && $isOpen)
                        <span class="lost-reward">مكافأة {{ number_format($item->reward) }} ج</span>
                    @endif
                    <span class="lost-time">{{ $item->created_at?->diffForHumans() }}</span>
                </div>
            </div>
        @if($isOpen)
            </a>
        @else
            </div>
        @endif
    @empty
        <div style="text-align: center; padding: 40px 16px;">
            <div style="width: 80px; height: 80px; border-radius: 24px; background: rgba(13,148,136,.08); display: grid; place-items: center; margin: 0 auto 12px;">
                <x-icon name="search-loc" :size="38" stroke="#0D9488"/>
            </div>
            <div class="label-strong" style="font-size: 16px;">مفيش بلاغات حالياً</div>
            <p class="muted" style="margin-top: 6px;">كن أول من ينشر بلاغ مفقود أو موجود.</p>
        </div>
    @endforelse

    {{-- Realtime-search empty state (hidden until JS filter wipes all matches) --}}
    <div id="lost-empty" hidden style="text-align: center; padding: 30px 16px;">
        <div style="width: 70px; height: 70px; border-radius: 20px; background: rgba(0,27,42,.06); display: grid; place-items: center; margin: 0 auto 10px; color: var(--ink-4);">
            <x-icon name="search" :size="28"/>
        </div>
        <div class="label-strong">مفيش نتائج تطابق بحثك</div>
        <p class="muted" style="margin-top: 4px; font-size: 12px;">جرّب كلمة تانية.</p>
    </div>

    @if($items->hasPages())
        <div style="margin-top: 14px;">{{ $items->links() }}</div>
    @endif
</div>

<style>
.lost-cta {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px;
    border-radius: 14px;
    background: white;
    border: 1px solid var(--line);
    text-decoration: none;
    color: var(--ink-1);
    transition: transform .12s ease, box-shadow .15s ease;
}
.lost-cta:active { transform: scale(.97); }
.lost-cta-lost  { background: linear-gradient(135deg, rgba(220,38,38,.06), transparent); }
.lost-cta-found { background: linear-gradient(135deg, rgba(16,185,129,.07), transparent); }
.lost-cta-ico {
    width: 38px; height: 38px;
    border-radius: 11px;
    background: white;
    display: grid; place-items: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px -4px rgba(0,27,42,.18);
}
.lost-cta-title { font-weight: 900; font-size: 13px; }
.lost-cta-sub { font-size: 11px; color: var(--ink-3); font-weight: 700; margin-top: 2px; }

.lost-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    margin-bottom: 10px;
    text-decoration: none;
    color: var(--ink-1);
}
.lost-card-img {
    width: 78px; height: 78px;
    flex-shrink: 0;
    border-radius: 11px;
    overflow: hidden;
    background: #F1F4F7;
}
.lost-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.lost-title { font-weight: 900; font-size: 14px; line-height: 1.4; }
.lost-meta {
    display: flex; align-items: center; gap: 6px;
    margin-top: 4px;
    font-size: 11px;
    color: var(--ink-3);
    font-weight: 700;
    flex-wrap: wrap;
}
.lost-cat {
    background: rgba(13,148,136,.08);
    color: var(--teal);
    padding: 2px 7px;
    border-radius: 6px;
    font-size: 10.5px;
    font-weight: 800;
}
.lost-meta .dot { width: 3px; height: 3px; background: var(--ink-4); border-radius: 50%; }
.lost-desc { margin: 6px 0; font-size: 12px; line-height: 1.6; color: var(--ink-2); }
.lost-foot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    font-weight: 700;
    color: var(--ink-4);
}
.lost-reward { color: #B45309; font-weight: 900; font-size: 12px; }
.kind-pill {
    padding: 3px 8px;
    border-radius: 7px;
    font-size: 10.5px;
    font-weight: 800;
    flex-shrink: 0;
}
.kind-pill.is-lost  { background: rgba(220,38,38,.10); color: #B91C1C; }
.kind-pill.is-found { background: rgba(16,185,129,.14); color: #047857; }
.kind-pill.is-done  { background: rgba(0,27,42,.08); color: var(--ink-3); }

/* Closed/resolved items: visible but inert */
.lost-card.is-closed {
    opacity: 0.7;
    background: #FAFBFC;
    cursor: default;
    pointer-events: none;
}
.lost-card.is-closed .lost-title { color: var(--ink-3); }
.lost-card.is-closed .lost-card-img,
.lost-card.is-closed .lost-cat {
    filter: grayscale(.4);
}
</style>

<script>
(function () {
    var input    = document.getElementById('lost-search');
    var form     = document.getElementById('lost-form');
    var items    = Array.from(document.querySelectorAll('.lost-item'));
    var emptyEl  = document.getElementById('lost-empty');
    var count    = document.getElementById('lost-count');
    if (!input) return;

    form.addEventListener('submit', function (e) { e.preventDefault(); });

    function normalise(s) {
        return (s || '').toString().trim().toLowerCase()
            .replace(/[ً-ْٰ]/g, '')
            .replace(/[إأآا]/g, 'ا')
            .replace(/ى/g, 'ي')
            .replace(/ة/g, 'ه');
    }

    function apply() {
        var q = normalise(input.value);
        var shown = 0;
        if (q === '') {
            items.forEach(function (el) { el.hidden = false; });
            shown = items.length;
        } else {
            items.forEach(function (el) {
                var match = normalise(el.dataset.haystack).indexOf(q) !== -1;
                el.hidden = !match;
                if (match) shown++;
            });
        }
        if (count) count.textContent = q ? (shown + ' نتيجة') : '';
        if (emptyEl) emptyEl.hidden = !(q && shown === 0);
    }

    var t = null;
    input.addEventListener('input', function () {
        apply();
        clearTimeout(t);
        t = setTimeout(function () {
            var url = new URL(window.location.href);
            if (input.value.trim() === '') url.searchParams.delete('q');
            else url.searchParams.set('q', input.value);
            window.history.replaceState(null, '', url);
        }, 250);
    });

    if (input.value) apply();
})();
</script>

@include('partials.visitor-nav')
@endsection
