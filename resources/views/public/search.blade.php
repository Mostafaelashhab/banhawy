@extends('layouts.mobile')

@section('title', ($type ? $type->name_ar . ' · ' : '') . 'بحث · بنهاوي')
@section('page-title', $type?->name_ar ?? 'البحث في بنها')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head" style="background: transparent;">
    <a href="{{ route('home') }}" class="back" aria-label="رجوع للرئيسية"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">{{ $type ? 'كل ' . $type->name_ar : 'نتائج البحث' }}</div>
</div>

<form method="get" action="{{ route('search') }}" style="padding: 0 14px 8px;">
    {{-- preserve active type filter when refining by text --}}
    @if($type)
        <input type="hidden" name="type" value="{{ $type->slug }}">
    @endif
    <label class="field">
        <span style="color: var(--ink-4);"><x-icon name="search" :size="16"/></span>
        <input type="text" name="q" value="{{ $q }}" placeholder="{{ $type ? 'ابحث داخل ' . $type->name_ar : 'ابحث عن مطعم أو خدمة' }}" autofocus>
        <span class="tiny" style="color: var(--ink-3);">{{ $results->count() }} نتيجة</span>
    </label>
</form>

{{-- Active filter chips: clear-button + the rest as filter pills --}}
<div class="chip-scroll-wrap" style="padding: 0 0 8px;">
    <div class="chip-scroll" style="padding: 0 14px;">
        @if($type)
            <a href="{{ route('search', $q ? ['q' => $q] : []) }}" class="chip active" style="gap: 6px;">
                <x-icon :name="$type->icon" :size="12" stroke="white"/>
                {{ $type->name_ar }}
                <span aria-label="إزالة الفلتر" style="opacity: .7; font-weight: 900;">×</span>
            </a>
        @endif
        @foreach($types as $t)
            @continue($type && $type->id === $t->id)
            <a href="{{ route('search', array_filter(['type' => $t->slug, 'q' => $q ?: null])) }}" class="chip">
                <x-icon :name="$t->icon" :size="12"/> {{ $t->name_ar }}
            </a>
        @endforeach
    </div>
</div>

<div class="scroll" style="padding: 6px 14px 14px;">
    <div class="result-grid">
    @forelse($results as $b)
        <div class="card card-pad" style="display: flex; gap: 10px;">
            <a href="{{ route('business.show', $b) }}" class="ph ph-{{ $b->type->slug }}" style="width: 64px; height: 64px; flex-shrink: 0; font-size: 14px;">
                {{ mb_substr($b->name, 0, 2) }}
            </a>
            <div style="flex: 1; min-width: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="{{ route('business.show', $b) }}" class="label-strong">{{ $b->name }}</a>
                    <span class="stars"><x-icon name="star-f" :size="10"/> {{ number_format($b->rating, 1) }}</span>
                </div>
                <div class="label-meta">{{ $b->category }}</div>
                <div style="display: flex; gap: 5px; margin-top: 5px; align-items: center;">
                    @if($b->isOpenNow())
                        <span class="chip open" style="padding: 1px 7px; font-size: 10px;">مفتوح</span>
                    @else
                        <span class="chip closed" style="padding: 1px 7px; font-size: 10px;">مغلق</span>
                    @endif
                    @if($b->delivery)
                        <span class="label-meta">· توصيل</span>
                    @endif
                </div>
                <div style="display: flex; gap: 5px; margin-top: 8px;">
                    <a href="{{ route('business.whatsapp', $b) }}" class="btn btn-wa" style="flex: 1; padding: 6px; font-size: 11px;">
                        <x-icon name="whatsapp" :size="12" stroke="white"/> واتساب
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $b->lat }},{{ $b->lng }}" class="btn btn-line" style="padding: 6px 8px; font-size: 11px;" target="_blank">الاتجاهات</a>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-pad" style="text-align: center; padding: 32px 16px; color: var(--ink-3); grid-column: 1 / -1;">
            <div style="margin-bottom: 8px; color: var(--ink-4);"><x-icon name="search" :size="32"/></div>
            <div class="label-strong">لا توجد نتائج</div>
            <div class="label-meta" style="margin-top: 4px;">جرّب بحث آخر أو تصنيف مختلف.</div>
        </div>
    @endforelse
    </div>
</div>

@include('partials.visitor-nav')
@endsection
