@extends('layouts.mobile')

@section('title', 'شركات الشحن · بنهاوي')
@section('page-title', 'شركات الشحن')

@section('content')
<div class="app-head" style="background: transparent;">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">شركات الشحن</div>
</div>

{{-- ── HERO ───────────────────────────────────────────────── --}}
<div style="padding: 4px 14px 0;">
    <div class="cat-hero">
        <div class="cat-hero-text">
            <div class="cat-hero-eyebrow">{{ $businesses->count() }} شركة شحن في بنها</div>
            <h1 class="cat-hero-title">ابعت طلباتك في أمان</h1>
            <p class="cat-hero-sub">اختار من بين شركات الشحن الموثوقة — تواصل واتساب مباشر، مفيش وسيط.</p>
        </div>
        <span class="cat-hero-ico">
            <x-icon name="truck" :size="42" stroke="white"/>
        </span>
    </div>
</div>

{{-- ── SEARCH + FILTERS ───────────────────────────────────── --}}
<form id="cat-form" autocomplete="off" style="padding: 14px 14px 8px;">
    <label class="field" style="margin-bottom: 8px;">
        <span style="color: var(--ink-4);"><x-icon name="search" :size="16"/></span>
        <input type="search" id="cat-search" placeholder="ابحث عن شركة شحن..." inputmode="search">
        <span class="tiny" id="cat-count" style="color: var(--ink-3);"></span>
    </label>

    @if($categories->count() > 0)
        <div class="chip-scroll-wrap" style="margin: 8px -14px 0;">
            <div class="chip-scroll" style="padding: 0 14px;">
                <button type="button" class="chip active" data-cat="all">الكل</button>
                @foreach($categories as $c)
                    <button type="button" class="chip" data-cat="{{ Str::lower($c) }}">{{ $c }}</button>
                @endforeach
            </div>
        </div>
    @endif
</form>

<div class="scroll" style="padding: 4px 14px 24px;">
    @forelse($businesses as $b)
        @php $hay = mb_strtolower($b->name . ' ' . ($b->category ?? '') . ' ' . ($b->description ?? '')); @endphp
        <a href="{{ route('business.show', $b) }}"
           class="card cat-card cat-item"
           data-haystack="{{ $hay }}"
           data-cat="{{ Str::lower($b->category ?? '') }}">

            <div class="cat-card-thumb ph ph-{{ $b->type?->slug ?? 'shipping' }}">
                @if($b->logo)
                    <img src="{{ $b->logo }}" alt="" onerror="this.style.display='none'">
                @else
                    <span>{{ mb_substr($b->name, 0, 2) }}</span>
                @endif
            </div>

            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span class="cat-card-name">{{ $b->name }}</span>
                    @if($b->isPlanVerified())
                        <span class="cat-verified" title="موثّق">✓</span>
                    @endif
                </div>
                <div class="cat-card-cat">{{ $b->category }}</div>
                <div class="cat-card-meta">
                    <x-icon name="star-f" :size="11"/>
                    <span>{{ number_format($b->rating, 1) }}</span>
                    <span class="dot"></span>
                    <span>{{ $b->reviews_count }} تقييم</span>
                    @if($b->isOnBusinessPlan())
                        <span class="cat-badge cat-badge-business">★ مميّز</span>
                    @elseif($b->isOnProPlan())
                        <span class="cat-badge cat-badge-pro">PRO</span>
                    @endif
                </div>
            </div>

            <span class="cat-arrow"><x-icon name="chev-l" :size="14" stroke="#94A1AE"/></span>
        </a>
    @empty
        <div style="text-align: center; padding: 40px 16px;">
            <div style="width: 80px; height: 80px; border-radius: 24px; background: rgba(13,148,136,.08); display: grid; place-items: center; margin: 0 auto 12px;">
                <x-icon name="truck" :size="38" stroke="#0D9488"/>
            </div>
            <div class="label-strong" style="font-size: 16px;">لسه مفيش شركات شحن</div>
            <p class="muted" style="margin-top: 6px;">كن أول شركة تسجّل في بنهاوي.</p>
        </div>
    @endforelse

    {{-- Realtime-search empty state --}}
    <div id="cat-empty" hidden style="text-align: center; padding: 30px 16px;">
        <div style="width: 70px; height: 70px; border-radius: 20px; background: rgba(0,27,42,.06); display: grid; place-items: center; margin: 0 auto 10px; color: var(--ink-4);">
            <x-icon name="search" :size="28"/>
        </div>
        <div class="label-strong">مفيش نتائج تطابق بحثك</div>
        <p class="muted" style="margin-top: 4px; font-size: 12px;">جرّب كلمة تانية أو صنف مختلف.</p>
    </div>

    {{-- Register CTA --}}
    <div style="margin-top: 22px; padding: 16px; background: linear-gradient(135deg, rgba(13,148,136,.06), transparent); border: 1px dashed rgba(13,148,136,.30); border-radius: 14px; text-align: center;">
        <div style="font-weight: 800; font-size: 13.5px;">عندك شركة شحن؟</div>
        <p style="font-size: 12px; color: var(--ink-3); margin: 4px 0 10px; line-height: 1.7;">سجّل شركتك دلوقتي وابقى ظاهر لآلاف العملاء.</p>
        <a href="{{ route('register.step1') }}" class="btn btn-teal" style="padding: 10px 20px; font-size: 13px;">سجّل شركتك</a>
    </div>
</div>

@include('public.partials.category-styles')
@include('public.partials.category-script')

@include('partials.visitor-nav')
@endsection
