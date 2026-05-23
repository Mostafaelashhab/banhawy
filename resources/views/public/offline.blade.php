@extends('layouts.mobile')

@section('title', 'بلا اتصال · بنهاوي')
@section('page-title', 'بلا اتصال')
@section('shell-class', 'is-landing')

@section('content')
<div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 24px; text-align: center; background: var(--navy); color: white;">
    <div style="position: relative; margin-bottom: 24px;">
        <div style="position: absolute; inset: -16px; border-radius: 50%; background: rgba(255,255,255,.06);"></div>
        <div style="position: relative; width: 84px; height: 84px; border-radius: 26px; background: var(--teal); display: grid; place-items: center;">
            <svg viewBox="0 0 24 24" width="42" height="42" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="2" y1="2" x2="22" y2="22"/>
                <path d="M8.5 16.5a5 5 0 0 1 7 0"/>
                <path d="M2 8.82a15 15 0 0 1 4.17-2.65"/>
                <path d="M10.66 5c4.01-.36 8.14.9 11.34 3.76"/>
                <path d="M16.85 11.25a10 10 0 0 1 2.22 1.68"/>
                <line x1="12" y1="20" x2="12.01" y2="20"/>
            </svg>
        </div>
    </div>
    <h2 style="font-size: 22px; font-weight: 900; margin-bottom: 10px;">مفيش نت دلوقتي</h2>
    <p style="font-size: 14px; color: rgba(255,255,255,.7); line-height: 1.7; max-width: 320px;">
        اتفصلت من الإنترنت. الصفحات اللي زرتها قبل كده هتفضل شغّالة من الـ cache.
        جرّب ترجع لما يرجع النت.
    </p>
    <button onclick="window.location.reload()" class="btn btn-teal" style="padding: 12px 28px; margin-top: 24px;">
        إعادة المحاولة
    </button>
</div>
@endsection
