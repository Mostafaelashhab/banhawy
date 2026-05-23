@extends('layouts.mobile')

@section('title', 'تم إرسال طلبك · ' . $business->name)
@section('page-title', 'تم إرسال الطلب')
@section('screen-class', 'bg-gray')

@section('content')
<div style="flex: 1; display: flex; flex-direction: column;">
    <div class="app-head">
        <a href="{{ route('home') }}" class="back" aria-label="رجوع للرئيسية"><x-icon name="chev-r" :size="18"/></a>
        <div class="title">تم إرسال طلبك</div>
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

            <h2 style="font-size: 22px; font-weight: 900; color: var(--navy); margin-bottom: 8px;">تم استلام طلبك ✓</h2>
            <p class="muted" style="line-height: 1.7;">
                طلبك وصل لـ <strong style="color: var(--navy);">{{ $business->name }}</strong>.
                هيتواصلوا معاك على
                <span style="direction: ltr; display: inline-block; font-weight: 800; color: var(--navy);">{{ $order->customer_phone }}</span>
                لتأكيد الموعد والتوصيل.
            </p>
        </div>

        {{-- Order code — prominent + copyable --}}
        <div class="card" style="padding: 18px; margin-top: 12px; background: var(--navy); color: white; border: none; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -30px; left: -30px; width: 130px; height: 130px; border-radius: 50%; background: rgba(13,148,136,.22); filter: blur(40px);"></div>
            <div style="position: relative; display: flex; align-items: center; gap: 14px;">
                <span style="background: var(--teal); width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; flex-shrink: 0;">
                    <x-icon name="check" :size="20" stroke="white" w="3"/>
                </span>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 10px; color: rgba(255,255,255,.6); font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">رقم الطلب</div>
                    <div id="order-code" style="font-weight: 900; font-size: 22px; direction: ltr; text-align: right; letter-spacing: 1px; margin-top: 2px;">{{ $order->code }}</div>
                </div>
                <button onclick="navigator.clipboard.writeText('{{ $order->code }}'); this.textContent='تم النسخ'"
                        style="background: rgba(255,255,255,.12); color: white; border: none; padding: 8px 12px; border-radius: 10px; font-weight: 800; font-size: 11px;">
                    نسخ
                </button>
            </div>
            <a href="{{ route('track', ['code' => $order->code]) }}"
               style="display: flex; align-items: center; justify-content: center; gap: 6px;
                      margin-top: 12px; padding: 10px;
                      background: rgba(255,255,255,.1); border-radius: 12px;
                      color: white; font-weight: 800; font-size: 12px;
                      position: relative;">
                <x-icon name="search" :size="14" stroke="white"/>
                تتبّع الطلب
                <x-icon name="chev-l" :size="12" stroke="white"/>
            </a>
        </div>

        {{-- Order recap --}}
        <div class="card" style="padding: 14px; margin-top: 12px;">
            <div class="label-strong" style="margin-bottom: 10px;">تفاصيل الطلب</div>
            @foreach($order->items as $item)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 12px;">
                    <span style="color: var(--ink-2); font-weight: 700;">{{ $item['name'] }} <span class="label-meta">×{{ $item['qty'] }}</span></span>
                    <span style="font-weight: 800;">{{ $item['line_total'] }}ج</span>
                </div>
            @endforeach
            <div class="sep" style="margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between;">
                <span class="label-strong">الإجمالي</span>
                <span style="font-weight: 900; font-size: 16px; color: var(--navy);">{{ $order->total }}ج</span>
            </div>
        </div>

        {{-- Quick actions --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px;">
            <a href="tel:{{ $business->phone ?? $business->whatsapp }}" class="btn btn-line" style="padding: 11px;">
                <x-icon name="phone" :size="14"/> اتصل بالمحل
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
