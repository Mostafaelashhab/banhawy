@extends('layouts.mobile')

@section('title', $bookLabel . ' · ' . $business->name)
@section('shell-class', 'no-bnav')
@section('screen-class', 'bg-gray')

@section('content')
@php
    $typeSlug = $business->type->slug;
    $servicePlaceholder = [
        'clinic'    => 'مثلًا: كشف عام',
        'salon'     => 'مثلًا: صبغة + قص',
        'education' => 'مثلًا: كورس رياضيات للصف الثالث الثانوي',
        'service'   => 'مثلًا: صيانة تكييف',
    ][$typeSlug] ?? null;
    $showPartySize = $typeSlug === 'restaurant';
    $minDate = now()->addHours(1)->format('Y-m-d\TH:i');
@endphp

<div class="app-head">
    <a href="{{ route('business.show', $business) }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">{{ $bookLabel }} · {{ $business->name }}</div>
</div>

<form method="post" action="{{ route('business.book.store', $business) }}" class="scroll" style="padding: 4px 14px 110px; display: flex; flex-direction: column; gap: 10px;">
    @csrf

    {{-- Confirmation note --}}
    <div style="background: rgba(13,148,136,.08); border: 1px solid rgba(13,148,136,.25); border-radius: 14px; padding: 10px 12px; display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: var(--teal); flex-shrink: 0; margin-top: 1px;"><x-icon name="clock" :size="18" stroke="#0D9488" w="2"/></span>
        <div style="font-size: 11px; line-height: 1.6; color: var(--gray-700); font-weight: 600;">
            هيوصل الحجز لـ <strong style="color: var(--navy);">{{ $business->name }}</strong> ويتواصلوا معاك لتأكيد الموعد.
        </div>
    </div>

    {{-- Date + time --}}
    <div class="card" style="padding: 12px;">
        <div class="label-strong" style="margin-bottom: 8px;">الموعد</div>
        <div class="field">
            <input type="datetime-local"
                   name="booked_at"
                   value="{{ old('booked_at') }}"
                   min="{{ $minDate }}"
                   required
                   style="width: 100%; border: none; background: transparent; font-weight: 700; font-size: 13px;">
        </div>
        @error('booked_at') <div class="tiny" style="color: #B91C1C; margin-top: 4px;">{{ $message }}</div> @enderror

        @if($showPartySize)
            <div style="margin-top: 10px;">
                <label class="field-label">العدد</label>
                <div class="field">
                    <input type="number" name="party_size" value="{{ old('party_size', 2) }}" min="1" max="50" required>
                </div>
            </div>
        @endif

        @if($servicePlaceholder)
            <div style="margin-top: 10px;">
                <label class="field-label">الخدمة (اختياري)</label>
                <div class="field">
                    <input type="text" name="service" value="{{ old('service') }}" placeholder="{{ $servicePlaceholder }}" maxlength="120">
                </div>
            </div>
        @endif
    </div>

    {{-- Customer info --}}
    <div class="card" style="padding: 12px;">
        <div class="label-strong" style="margin-bottom: 8px;">بيانات التواصل</div>
        <div style="margin-bottom: 8px;">
            <label class="field-label">الاسم</label>
            <div class="field">
                <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" placeholder="اسمك بالكامل" required>
            </div>
            @error('customer_name') <div class="tiny" style="color: #B91C1C; margin-top: 4px;">{{ $message }}</div> @enderror
        </div>
        <div style="margin-bottom: 8px;">
            <label class="field-label">رقم الموبايل</label>
            <div class="field">
                <input type="tel" name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" placeholder="+20 1xx xxx xxxx" required dir="ltr" style="text-align: right;">
            </div>
            @error('customer_phone') <div class="tiny" style="color: #B91C1C; margin-top: 4px;">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="field-label">ملاحظات (اختياري)</label>
            <div class="field">
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="أي تفاصيل تحب تبعتها">
            </div>
        </div>
    </div>

    <div style="position: sticky; bottom: 0; padding: 10px 0 0; background: var(--gray-100); display: flex; flex-direction: column; gap: 8px;">
        @if($business->bookings_via === 'whatsapp')
            <input type="hidden" name="channel" value="whatsapp">
            <button type="submit" class="btn btn-wa btn-full" style="padding: 14px; font-size: 14px;">
                <x-icon name="whatsapp" :size="16" stroke="white" w="2"/>
                إرسال الحجز عبر واتساب
            </button>
        @elseif($business->bookings_via === 'web')
            <input type="hidden" name="channel" value="web">
            <button type="submit" class="btn btn-navy btn-full" style="padding: 14px; font-size: 14px;">
                <x-icon name="check" :size="16" stroke="white"/>
                تأكيد الحجز
            </button>
            <p class="tiny" style="text-align: center; color: var(--ink-3); padding: 4px 8px; line-height: 1.6;">
                الحجز هيتسجل عند صاحب النشاط · هيتواصل معاك على الرقم اللي فوق.
            </p>
        @else
            <button type="submit" name="channel" value="web" class="btn btn-navy btn-full" style="padding: 14px; font-size: 14px;">
                <x-icon name="check" :size="16" stroke="white"/>
                تأكيد الحجز
            </button>
            <button type="submit" name="channel" value="whatsapp" class="btn btn-line btn-full" style="padding: 12px; font-size: 13px;">
                <x-icon name="whatsapp" :size="14" stroke="#1FB855" w="2"/>
                إرسال عبر واتساب بدل الموقع
            </button>
        @endif
    </div>
</form>
@endsection
