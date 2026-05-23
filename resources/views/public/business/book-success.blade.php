@extends('layouts.mobile')

@section('title', 'تم استلام حجزك · ' . $business->name)
@section('page-title', 'تم استلام الحجز')
@section('shell-class', 'no-bnav')
@section('screen-class', 'bg-gray')

@section('content')
<div style="flex: 1; display: flex; flex-direction: column;">
    <div class="app-head">
        <a href="{{ route('home') }}" class="back" aria-label="رجوع للرئيسية"><x-icon name="chev-r" :size="18"/></a>
        <div class="title">تم استلام الحجز</div>
    </div>

    <div class="scroll" style="padding: 24px 16px;">
        <div class="card card-pad" style="padding: 28px 20px; text-align: center;">
            <div style="position: relative; width: 84px; height: 84px; margin: 0 auto 20px;">
                <div style="position: absolute; inset: -16px; border-radius: 50%; background: rgba(13,148,136,.10);"></div>
                <div style="position: absolute; inset: -8px;  border-radius: 50%; background: rgba(13,148,136,.18);"></div>
                <div style="position: relative; width: 84px; height: 84px; border-radius: 50%; background: var(--teal); display: grid; place-items: center;">
                    <x-icon name="check" :size="42" stroke="white" w="3"/>
                </div>
            </div>

            <h2 style="font-size: 22px; font-weight: 900; color: var(--navy); margin-bottom: 8px;">تم استلام حجزك ✓</h2>
            <p class="muted" style="line-height: 1.7;">
                الحجز وصل لـ <strong style="color: var(--navy);">{{ $business->name }}</strong>.
                هيتواصلوا معاك على
                <span style="direction: ltr; display: inline-block; font-weight: 800; color: var(--navy);">{{ $booking->customer_phone }}</span>
                لتأكيد الموعد.
            </p>
        </div>

        {{-- Booking summary --}}
        <div class="card" style="padding: 14px; margin-top: 12px;">
            <div class="label-strong" style="margin-bottom: 10px;">تفاصيل الحجز</div>
            <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px;">
                <span class="label-meta">الموعد</span>
                <span style="font-weight: 800;">{{ $booking->booked_at->locale('ar')->isoFormat('dddd D MMMM · h:mm a') }}</span>
            </div>
            @if($booking->service)
                <div class="sep" style="margin: 4px 0;"></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px;">
                    <span class="label-meta">الخدمة</span>
                    <span style="font-weight: 800;">{{ $booking->service }}</span>
                </div>
            @endif
            @if($booking->party_size > 1)
                <div class="sep" style="margin: 4px 0;"></div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px;">
                    <span class="label-meta">العدد</span>
                    <span style="font-weight: 800;">{{ $booking->party_size }}</span>
                </div>
            @endif
            @if($booking->notes)
                <div class="sep" style="margin: 4px 0;"></div>
                <div style="padding: 6px 0; font-size: 12px;">
                    <div class="label-meta" style="margin-bottom: 4px;">ملاحظات</div>
                    <div style="font-weight: 700;">{{ $booking->notes }}</div>
                </div>
            @endif
            <div class="sep" style="margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between;">
                <span class="label-meta">رقم الحجز</span>
                <span style="font-weight: 900; direction: ltr;">#{{ $booking->id }}</span>
            </div>
        </div>

        {{-- Quick actions --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px;">
            <a href="tel:{{ $business->phone ?? $business->whatsapp }}" class="btn btn-line" style="padding: 11px;">
                <x-icon name="phone" :size="14"/> اتصل
            </a>
            <a href="{{ route('business.whatsapp', $business) }}" class="btn btn-wa" style="padding: 11px;">
                <x-icon name="whatsapp" :size="14" stroke="white" w="2"/> واتساب
            </a>
        </div>

        <a href="{{ route('home') }}" class="btn btn-ghost btn-full" style="padding: 13px; margin-top: 10px;">
            استكشف أنشطة تانية في بنها
        </a>
    </div>
</div>
@endsection
