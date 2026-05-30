@extends('layouts.mobile')

@section('title', $item->title . ' · بنهاوي')
@section('page-title', 'تفاصيل البلاغ')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('lost.index') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تفاصيل البلاغ</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<div style="padding: 14px;">

    @if($item->image)
        <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 12px;">
            <img src="{{ $item->image }}" alt="" style="width: 100%; max-height: 320px; object-fit: cover; display: block;" referrerpolicy="no-referrer">
        </div>
    @endif

    <div class="card" style="padding: 16px;">
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
            @if($item->kind === 'lost')
                <span class="kind-pill is-lost">ضايع · بحث عنه</span>
            @else
                <span class="kind-pill is-found">موجود · بحث عن صاحبه</span>
            @endif
            <span class="lost-cat">{{ \App\Models\LostItem::CATEGORIES[$item->category] ?? $item->category }}</span>
            @if($item->status === 'resolved')
                <span class="lost-cat" style="background: rgba(0,27,42,.06); color: var(--ink-3);">انتهى ✓</span>
            @endif
        </div>

        <h2 style="margin: 0 0 12px; font-size: 18px; font-weight: 900; line-height: 1.4;">{{ $item->title }}</h2>
        <p style="font-size: 14px; line-height: 1.8; color: var(--ink-2); margin: 0 0 14px;">{{ $item->description }}</p>

        <div style="display: grid; gap: 10px; padding: 12px; background: #FAFBFC; border-radius: 11px; font-size: 13px;">
            @if($item->location)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <x-icon name="pin" :size="14" stroke="#0D9488"/>
                    <span style="font-weight: 700;">{{ $item->location }}</span>
                </div>
            @endif
            @if($item->happened_on)
                <div style="display: flex; align-items: center; gap: 8px;">
                    <x-icon name="calendar" :size="14" stroke="#0D9488"/>
                    <span style="font-weight: 700;">{{ $item->happened_on?->translatedFormat('d M Y') }}</span>
                </div>
            @endif
            @if($item->reward && $item->kind === 'lost')
                <div style="display: flex; align-items: center; gap: 8px;">
                    <x-icon name="gift" :size="14" stroke="#B45309"/>
                    <span style="font-weight: 900; color: #B45309;">مكافأة {{ number_format($item->reward) }} ج</span>
                </div>
            @endif
        </div>
    </div>

    @if($item->status === 'open')
        <div class="card" style="padding: 14px; margin-top: 10px;">
            <div class="label-strong" style="margin-bottom: 10px;">
                @if($item->kind === 'lost')
                    تواصل مع {{ $item->contact_name }} لو لقيته
                @else
                    تواصل مع {{ $item->contact_name }} لو ده بتاعك
                @endif
            </div>

            <div style="display: grid; gap: 8px;">
                <a href="tel:{{ $item->contact_phone }}" class="btn btn-line" style="padding: 12px; font-size: 13px; justify-content: center;">
                    <x-icon name="phone" :size="14" stroke="#0D9488"/>
                    اتصل · {{ $item->contact_phone }}
                </a>
                @php
                    $waNum = \App\Support\Phone::forWhatsapp($item->contact_phone);
                    $waMsg = rawurlencode("السلام عليكم، شفت بلاغ \"" . $item->title . "\" على بنهاوي");
                @endphp
                <a href="https://wa.me/{{ $waNum }}?text={{ $waMsg }}" target="_blank" class="btn btn-wa" style="padding: 12px; font-size: 13px; justify-content: center;">
                    <x-icon name="whatsapp" :size="14" stroke="white"/>
                    تواصل واتساب
                </a>
            </div>
        </div>
    @endif

    @auth
        @if((auth()->id() === $item->user_id || auth()->user()->isAdmin()) && $item->status === 'open')
            <form method="post" action="{{ route('lost.resolve', $item) }}" style="margin-top: 10px;" onsubmit="return confirm('تأكيد إقفال البلاغ؟')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-line btn-full" style="padding: 11px; font-size: 13px;">
                    إقفال البلاغ {{ $item->kind === 'lost' ? '(لقيته)' : '(رجع لصاحبه)' }}
                </button>
            </form>
        @endif
    @endauth
</div>

<style>
.lost-cat {
    background: rgba(13,148,136,.10);
    color: var(--teal);
    padding: 3px 9px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 800;
}
.kind-pill {
    padding: 3px 9px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 800;
}
.kind-pill.is-lost  { background: rgba(220,38,38,.10); color: #B91C1C; }
.kind-pill.is-found { background: rgba(16,185,129,.14); color: #047857; }
</style>
@endsection
