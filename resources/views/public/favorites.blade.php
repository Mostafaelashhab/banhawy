@extends('layouts.mobile')

@section('title', 'المفضلة · بنهاوي')
@section('page-title', 'المفضلة')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back" aria-label="رجوع"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">المفضلة</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<div class="scroll" style="padding: 12px 14px 14px;">
    @if($favorites->isEmpty())
        <div class="card card-pad" style="text-align: center; padding: 48px 16px; color: var(--ink-3);">
            <div style="width: 64px; height: 64px; border-radius: 20px; background: var(--gray-100); display: grid; place-items: center; margin: 0 auto 14px; color: var(--ink-4);">
                <x-icon name="heart" :size="28"/>
            </div>
            <div class="label-strong">مفيش حاجة في المفضلة لسه</div>
            <div class="label-meta" style="margin-top: 4px; line-height: 1.6;">دوس على القلب في أي نشاط عشان تحفظه هنا.</div>
            <a href="{{ route('map') }}" class="btn btn-navy" style="padding: 11px 18px; font-size: 12px; margin-top: 16px;">
                <x-icon name="pin" :size="14" stroke="white"/> استكشف الأنشطة
            </a>
        </div>
    @else
        <div class="result-grid">
            @foreach($favorites as $b)
                <div class="card card-pad" style="display: flex; gap: 10px; align-items: center;">
                    <a href="{{ route('business.show', $b) }}" class="ph ph-{{ $b->type->slug }}" style="width: 64px; height: 64px; flex-shrink: 0; font-size: 14px;">
                        {{ mb_substr($b->name, 0, 2) }}
                    </a>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a href="{{ route('business.show', $b) }}" class="label-strong">{{ $b->name }}</a>
                            <span class="stars"><x-icon name="star-f" :size="10"/> {{ number_format($b->rating, 1) }}</span>
                        </div>
                        <div class="label-meta">{{ $b->category }}</div>
                        <div style="display: flex; gap: 5px; margin-top: 8px;">
                            <a href="{{ route('business.show', $b) }}" class="btn btn-line" style="flex: 1; padding: 6px; font-size: 11px;">عرض</a>
                            <form method="post" action="{{ route('favorites.toggle', $b) }}" style="display: contents;">
                                @csrf
                                <button class="btn btn-line" style="padding: 6px 10px; font-size: 11px; color: #B91C1C;" aria-label="إزالة">
                                    <svg viewBox="0 0 24 24" width="12" height="12" fill="#B91C1C" stroke="#B91C1C" stroke-width="1.5">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@include('partials.visitor-nav')
@endsection
