@extends('layouts.mobile')

@section('title', 'المنيو · ' . $business->name)
@section('page-title', 'المنيو · ' . $business->name)
@section('shell-class', 'no-bnav')
@section('screen-class', 'bg-gray')

@section('content')
@php
    $totalProducts = $business->categories->sum(fn ($c) => $c->products->count());
    $isEmptyMenu = $totalProducts === 0;
@endphp
<div class="app-head">
    <a href="{{ route('business.show', $business) }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">المنيو · {{ $business->name }}</div>
    @unless($isEmptyMenu)
        <button type="button" id="menu-search-toggle" class="ico-btn" aria-label="بحث"><x-icon name="search" :size="18"/></button>
    @endunless
</div>

@if($isEmptyMenu)
    {{-- ── EMPTY STATE: no products yet ──────────────────────── --}}
    <div class="menu-empty-state">
        <div class="menu-empty-illu">
            <svg viewBox="0 0 96 96" width="96" height="96" fill="none" stroke="#0D9488" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="14" y="22" width="68" height="58" rx="10" fill="rgba(13,148,136,.06)"/>
                <path d="M30 22V14a6 6 0 0 1 6-6h24a6 6 0 0 1 6 6v8"/>
                <line x1="14" y1="40" x2="82" y2="40"/>
                <circle cx="32" cy="56" r="3" fill="#0D9488" stroke="none"/>
                <line x1="42" y1="54" x2="68" y2="54"/>
                <line x1="42" y1="60" x2="60" y2="60"/>
                <circle cx="32" cy="70" r="3" fill="#0D9488" stroke="none"/>
                <line x1="42" y1="68" x2="68" y2="68"/>
                <line x1="42" y1="74" x2="56" y2="74"/>
            </svg>
        </div>
        <div class="menu-empty-title">المنيو لسه فاضي</div>
        <p class="menu-empty-sub">صاحب المتجر لسه ما أضافش منتجاته على بنهاوي. ممكن تتواصل معاه مباشرة لمعرفة المنيو والأسعار.</p>

        <div class="menu-empty-actions">
            @if($business->phone || $business->whatsapp)
                <a href="tel:{{ $business->phone ?? $business->whatsapp }}" class="btn btn-line" style="padding: 11px; font-size: 13px; flex: 1;">
                    <x-icon name="phone" :size="14" stroke="#0D9488"/> اتصل
                </a>
            @endif
            @if($business->whatsapp)
                <a href="{{ route('business.whatsapp', $business) }}" class="btn btn-wa" style="padding: 11px; font-size: 13px; flex: 1;">
                    <x-icon name="whatsapp" :size="14" stroke="white"/> واتساب
                </a>
            @endif
        </div>

        <a href="{{ route('business.show', $business) }}" class="menu-empty-back">رجوع لصفحة المتجر</a>
    </div>

    <style>
    .menu-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 28px 24px 40px;
        gap: 4px;
    }
    .menu-empty-illu {
        margin-bottom: 14px;
        animation: emptyFloat 4s ease-in-out infinite;
    }
    @keyframes emptyFloat {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-6px); }
    }
    .menu-empty-title {
        font-weight: 900;
        font-size: 18px;
        color: var(--ink-1);
    }
    .menu-empty-sub {
        color: var(--ink-3);
        font-size: 13px;
        line-height: 1.75;
        margin: 6px 0 18px;
        max-width: 320px;
        font-weight: 600;
    }
    .menu-empty-actions {
        display: flex;
        gap: 8px;
        width: 100%;
        max-width: 320px;
    }
    .menu-empty-back {
        margin-top: 14px;
        color: var(--ink-3);
        font-size: 12px;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    </style>
@else

{{-- Search input (collapsed by default; toggled by the icon above) --}}
<div id="menu-search-wrap" hidden style="padding: 0 14px 8px;">
    <label class="field">
        <span style="color: var(--ink-4);"><x-icon name="search" :size="16"/></span>
        <input type="search" id="menu-search-input" placeholder="ابحث في المنيو..." autocomplete="off">
        <button type="button" id="menu-search-clear" aria-label="مسح" style="background: transparent; border: none; color: var(--ink-4); padding: 4px 6px; font-size: 16px; line-height: 1;">×</button>
    </label>
</div>

{{-- Category filter chips --}}
<div class="chip-scroll-wrap" style="padding: 0 0 8px;">
    <div class="chip-scroll" style="padding: 0 14px;">
        <button type="button" class="chip active" data-cat-filter="all">الكل</button>
        @foreach($business->categories as $cat)
            <button type="button" class="chip" data-cat-filter="{{ $cat->id }}">{{ $cat->name }}</button>
        @endforeach
    </div>
</div>

<form method="get" action="{{ route('business.order.summary', $business) }}" id="cart-form" class="scroll" style="padding: 4px 14px 12px; display: flex; flex-direction: column; gap: 10px;">
    @foreach($business->categories as $cat)
        @foreach($cat->products as $product)
            <div class="card card-pad menu-item"
                 data-product="{{ $product->id }}"
                 data-price="{{ $product->price }}"
                 data-cat="{{ $cat->id }}"
                 data-name="{{ Str::lower($product->name) }}"
                 style="display: flex; gap: 10px;">
                <div class="ph ph-{{ $business->type->slug }}" style="width: 72px; height: 72px;">
                    <span style="font-size: 11px;">{{ mb_substr($product->name, 0, 2) }}</span>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="label-strong">{{ $product->name }}</span>
                        @if($product->is_featured)
                            <span class="chip teal" style="padding: 1px 7px; font-size: 10px;">شائع</span>
                        @endif
                    </div>
                    @if($product->description)
                        <div class="label-meta" style="margin-top: 2px; line-height: 1.5;">{{ $product->description }}</div>
                    @endif
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 8px;">
                        <span style="font-weight: 900; font-size: 14px; color: var(--navy);">{{ $product->price }} <span class="tiny" style="color: var(--ink-3);">ج</span></span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <button type="button" class="qty-minus" aria-label="ناقص" style="width: 26px; height: 26px; border-radius: 50%; background: var(--gray-100); border: none; display: grid; place-items: center; color: var(--ink);">
                                <x-icon name="minus" :size="12"/>
                            </button>
                            <input type="number" name="items[{{ $product->id }}]" value="0" min="0" max="50" class="qty-input" inputmode="numeric" style="width: 28px; text-align: center; border: none; background: transparent; font-weight: 800; font-size: 13px;">
                            <button type="button" class="qty-plus" aria-label="زيادة" style="width: 26px; height: 26px; border-radius: 50%; background: var(--teal); border: none; color: white; display: grid; place-items: center;">
                                <x-icon name="plus" :size="12" stroke="white" w="2.4"/>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach

    {{-- "No results" message — shown only when filter/search hides everything --}}
    <div id="menu-empty" hidden class="card card-pad" style="text-align: center; padding: 28px 16px; color: var(--ink-3);">
        <div class="label-strong">لا يوجد منتجات في هذا التصنيف</div>
    </div>
</form>

{{-- Sticky cart CTA — fixed above the bnav, only shown when cart has items --}}
<div id="cart-cta-wrap" hidden style="position: fixed; right: 12px; left: 12px; bottom: calc(72px + env(safe-area-inset-bottom)); z-index: 30;">
    <button type="button" id="cart-cta" form="cart-form" style="width: 100%; background: var(--navy); border-radius: 16px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 12px 32px rgba(0,27,42,.2); border: none; color: white;">
        <span style="display: flex; align-items: center; gap: 10px;">
            <span style="background: var(--teal); width: 28px; height: 28px; border-radius: 10px; display: grid; place-items: center;">
                <x-icon name="cart" :size="14" stroke="white" w="2.2"/>
            </span>
            <span style="text-align: right;">
                <span id="cart-count" style="font-weight: 800; font-size: 12px;">0 أصناف</span><br>
                <span style="opacity: .6; font-size: 10px; font-weight: 700;">إجمالي <span id="cart-total">0</span>ج</span>
            </span>
        </span>
        <span style="display: inline-flex; align-items: center; gap: 6px; background: var(--wa); padding: 8px 12px; border-radius: 10px; font-size: 11px; font-weight: 800;">
            <x-icon name="cart" :size="12" stroke="white" w="2"/> إتمام الطلب
        </span>
    </button>
</div>

<script>
(function () {
    const form    = document.getElementById('cart-form');
    const wrap    = document.getElementById('cart-cta-wrap');
    const cta     = document.getElementById('cart-cta');
    const totalEl = document.getElementById('cart-total');
    const countEl = document.getElementById('cart-count');
    const items   = form.querySelectorAll('.menu-item');
    const empty   = document.getElementById('menu-empty');

    /* ── Cart math + sticky CTA visibility ─────────────────── */
    function update() {
        let total = 0, count = 0;
        items.forEach(card => {
            const qty   = parseInt(card.querySelector('.qty-input').value) || 0;
            const price = parseInt(card.dataset.price);
            total += qty * price;
            count += qty;
        });
        totalEl.textContent = total;
        countEl.textContent = count + ' أصناف';
        wrap.hidden = count === 0;
        form.style.paddingBottom = count === 0 ? '12px' : '120px';
    }

    form.addEventListener('click', e => {
        const card = e.target.closest('[data-product]');
        if (!card) return;
        const input = card.querySelector('.qty-input');
        if (e.target.closest('.qty-plus'))  { input.value = (parseInt(input.value) || 0) + 1; update(); }
        if (e.target.closest('.qty-minus')) { input.value = Math.max(0, (parseInt(input.value) || 0) - 1); update(); }
    });
    form.addEventListener('input', update);
    cta.addEventListener('click', () => form.submit());

    /* ── Category filter ──────────────────────────────────── */
    const chips = document.querySelectorAll('[data-cat-filter]');
    let activeCat = 'all';
    let searchTerm = '';

    function applyFilters() {
        let shown = 0;
        items.forEach(card => {
            const matchCat    = activeCat === 'all' || card.dataset.cat === activeCat;
            const matchSearch = searchTerm === '' || card.dataset.name.includes(searchTerm);
            const visible = matchCat && matchSearch;
            card.hidden = !visible;
            if (visible) shown++;
        });
        empty.hidden = shown > 0;
    }

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            activeCat = chip.dataset.catFilter;
            applyFilters();
        });
    });

    /* ── Search ───────────────────────────────────────────── */
    const searchToggle = document.getElementById('menu-search-toggle');
    const searchWrap   = document.getElementById('menu-search-wrap');
    const searchInput  = document.getElementById('menu-search-input');
    const searchClear  = document.getElementById('menu-search-clear');

    searchToggle.addEventListener('click', () => {
        searchWrap.hidden = !searchWrap.hidden;
        if (!searchWrap.hidden) searchInput.focus();
    });
    searchInput.addEventListener('input', () => {
        searchTerm = searchInput.value.trim().toLowerCase();
        applyFilters();
    });
    searchClear.addEventListener('click', () => {
        searchInput.value = '';
        searchTerm = '';
        applyFilters();
        searchInput.focus();
    });

    update();
})();
</script>
@endif
@endsection
