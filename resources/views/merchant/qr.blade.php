@extends('layouts.mobile')

@section('title', 'رمز QR · ' . $business->name)
@section('page-title', 'رمز QR للمتجر')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">رمز المتجر · QR</div>
</div>

<div class="scroll" style="padding: 8px 14px 14px;">

    <div style="background: white; border-radius: 22px; padding: 18px; box-shadow: var(--shadow); border: 1px solid var(--line);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
            <div class="ph ph-{{ $business->type->slug }}" style="width: 34px; height: 34px; border-radius: 10px; font-size: 10px;">
                {{ mb_substr($business->name, 0, 2) }}
            </div>
            <div>
                <div style="font-weight: 900; font-size: 13px;">{{ $business->name }}</div>
                <div class="tiny" style="color: var(--ink-3);">امسح الرمز لفتح المتجر</div>
            </div>
        </div>

        <div style="background: white; border: 1px solid var(--line); border-radius: 16px; padding: 16px; display: grid; place-items: center;">
            @include('partials.qr-svg', ['size' => 220, 'center' => mb_substr($business->name, 0, 2)])
        </div>

        <div style="text-align: center; margin-top: 14px;">
            <div class="tiny" style="color: var(--ink-3);">رابط متجرك</div>
            <div style="font-weight: 800; font-size: 12px; direction: ltr; color: var(--navy); margin-top: 3px; word-break: break-all;">{{ $url }}</div>
        </div>
    </div>

    <p class="tiny" style="text-align: center; color: var(--ink-3); margin: 14px 16px 16px; line-height: 1.6;">
        اطبع الرمز وحطه في المحل أو على المنيو — العميل يمسحه ويفتح متجرك فورًا.
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <button onclick="downloadQr('png')" class="btn btn-navy" style="padding: 11px; font-size: 12px;">
            <x-icon name="download" :size="14" stroke="white" w="2.2"/> PNG
        </button>
        <button onclick="downloadQr('pdf')" class="btn btn-line" style="padding: 11px; font-size: 12px;">
            <x-icon name="download" :size="14"/> PDF
        </button>
        <button onclick="navigator.clipboard.writeText('{{ $url }}'); this.textContent='تم النسخ ✓'" class="btn btn-line" style="padding: 11px; font-size: 12px;">
            <x-icon name="copy" :size="14"/> نسخ الرابط
        </button>
        <a href="https://wa.me/?text={{ rawurlencode('شوف متجرنا على بنهاوي: ' . $url) }}" class="btn btn-wa" style="padding: 11px; font-size: 12px;">
            <x-icon name="whatsapp" :size="14" stroke="white"/> مشاركة
        </a>
    </div>
</div>

@include('partials.merchant-nav')

<script>
function downloadQr(format) {
    alert('تحميل ' + format.toUpperCase() + ' — في إصدار الإنتاج هيتولّد ملف فعلي');
}
</script>
@endsection
