@extends('layouts.mobile')

@section('title', 'مبروك! · بنهاوي')

@section('content')
<div style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 22px; text-align: center;">
    <div style="position: relative; margin-bottom: 24px;">
        <div style="position: absolute; inset: -18px; border-radius: 50%; background: rgba(37,211,102,.10);"></div>
        <div style="position: absolute; inset: -9px; border-radius: 50%; background: rgba(37,211,102,.15);"></div>
        <div style="position: relative; width: 84px; height: 84px; border-radius: 50%; background: var(--wa); display: grid; place-items: center;">
            <x-icon name="check" :size="42" stroke="white" w="3"/>
        </div>
    </div>

    <h2 style="font-size: 22px; font-weight: 900; color: var(--navy); margin-bottom: 8px;">مبروك! موقع نشاطك جاهز</h2>
    <p style="font-size: 13px; color: var(--ink-3); font-weight: 600; line-height: 1.7; margin-bottom: 22px;">
        دلوقتي تقدر تشارك رابط متجرك مع عملائك<br>وتستقبل أول طلب على واتساب.
    </p>

    <div style="width: 100%; background: var(--gray-100); border-radius: 12px; padding: 10px 12px; display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
        <span style="color: var(--teal); flex-shrink: 0;"><x-icon name="pin" :size="14" stroke="#0D9488"/></span>
        <div style="flex: 1; font-size: 12px; font-weight: 800; text-align: left; direction: ltr; color: var(--navy); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ route('business.show', $business) }}
        </div>
        <button onclick="navigator.clipboard.writeText('{{ route('business.show', $business) }}')" style="background: white; border: 1px solid var(--line-2); border-radius: 8px; padding: 4px 6px; color: var(--navy);">
            <x-icon name="copy" :size="13" stroke="#001B2A"/>
        </button>
    </div>

    {{-- Mini QR --}}
    <div style="background: white; border: 1px solid var(--line); border-radius: 14px; padding: 10px; box-shadow: var(--shadow-sm); margin-top: 10px;">
        <a href="{{ route('merchant.qr') }}">
            @include('partials.qr-svg', ['size' => 100])
        </a>
    </div>
</div>

<div style="padding: 14px 14px 20px;">
    <a href="{{ route('business.show', $business) }}" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px; margin-bottom: 8px;">
        عرض الموقع <x-icon name="arrow-l" :size="14" stroke="white" w="2.4"/>
    </a>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('merchant.qr') }}" class="btn btn-line" style="flex: 1; padding: 11px; font-size: 12px;">
            <x-icon name="download" :size="13"/> تحميل QR
        </a>
        <a href="{{ route('merchant.dashboard') }}" class="btn btn-line" style="flex: 1; padding: 11px; font-size: 12px;">
            لوحة التحكم <x-icon name="arrow-l" :size="13"/>
        </a>
    </div>
</div>
@endsection
