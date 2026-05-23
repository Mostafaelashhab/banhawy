@extends('layouts.mobile')

@section('title', 'لوحة التحكم · ' . $business->name)
@section('page-title', 'لوحة التحكم')
@section('screen-class', 'bg-gray')

@section('content')
{{-- Header --}}
<div style="padding: 8px 14px 14px;">
    <div style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="ph ph-{{ $business->type->slug }}" style="width: 42px; height: 42px; border-radius: 12px; font-size: 12px;">
                {{ mb_substr($business->name, 0, 2) }}
            </div>
            <div>
                <div class="tiny" style="color: var(--ink-3);">أهلًا بك في</div>
                <div style="font-weight: 900; font-size: 14px; color: var(--navy);">{{ $business->name }}</div>
            </div>
        </div>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button class="ico-btn" style="width: 36px; height: 36px; border-radius: 12px; background: white; border: 1px solid var(--line); display: grid; place-items: center;">
                <x-icon name="logout" :size="16"/>
            </button>
        </form>
    </div>

    {{-- Setup progress --}}
    <div style="margin-top: 14px; background: var(--navy); border-radius: 14px; padding: 12px 14px; color: white; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -30px; left: -30px; width: 120px; height: 120px; border-radius: 50%; background: rgba(13,148,136,.15);"></div>
        <div style="position: relative;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="tiny" style="color: rgba(255,255,255,.7);">إعداد الملف</span>
                <span class="tiny" style="font-weight: 900; color: var(--teal);">{{ $business->setup_progress }}%</span>
            </div>
            <div style="font-size: 13px; font-weight: 800; margin-top: 4px;">
                @if($business->setup_progress < 100)
                    أكمل خطوات وافتح كل المزايا
                @else
                    موقعك مكتمل ✓
                @endif
            </div>
            <div style="margin-top: 8px; height: 6px; border-radius: 3px; background: rgba(255,255,255,.15); overflow: hidden;">
                <div style="height: 100%; width: {{ $business->setup_progress }}%; background: var(--teal); border-radius: 3px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="scroll" style="padding: 0 14px 14px;">
    {{-- Notifications opt-in for the merchant — gets order alerts --}}
    @include('partials.notifications-card')

    {{-- Stats grid --}}
    <div class="stat-grid" style="margin-bottom: 16px;">
        <div class="card" style="padding: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="color: var(--teal); background: var(--teal-50); width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center;"><x-icon name="eye" :size="14" stroke="#0D9488"/></span>
            </div>
            <div style="font-weight: 900; font-size: 18px; color: var(--navy); margin-top: 6px;">{{ $stats['views_week'] }}</div>
            <div class="label-meta">زيارات الأسبوع</div>
        </div>
        <div class="card" style="padding: 10px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="color: var(--wa-600); background: rgba(37,211,102,.12); width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center;"><x-icon name="whatsapp" :size="14" stroke="#1FB855"/></span>
            </div>
            <div style="font-weight: 900; font-size: 18px; color: var(--navy); margin-top: 6px;">{{ $stats['wa_clicks_week'] }}</div>
            <div class="label-meta">ضغطات واتساب</div>
        </div>
        <a href="{{ route('merchant.orders.index') }}" class="card" style="padding: 10px; display: block;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="color: var(--deep); background: #E8EBFA; width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center;"><x-icon name="cart" :size="14" stroke="#1E3A8A"/></span>
                @if($stats['orders_new'] > 0)
                    <span class="chip wa-tint" style="padding: 1px 7px; font-size: 9px;">+{{ $stats['orders_new'] }}</span>
                @endif
            </div>
            <div style="font-weight: 900; font-size: 18px; color: var(--navy); margin-top: 6px;">{{ $stats['orders_new'] }}</div>
            <div class="label-meta">طلبات جديدة</div>
        </a>
        <a href="{{ route('merchant.bookings.index') }}" class="card" style="padding: 10px; display: block;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <span style="color: var(--amber); background: #FEF3C7; width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center;"><x-icon name="calendar" :size="14" stroke="#F59E0B"/></span>
                <span class="tiny" style="color: var(--amber);">اليوم</span>
            </div>
            <div style="font-weight: 900; font-size: 18px; color: var(--navy); margin-top: 6px;">{{ $stats['bookings_today'] }}</div>
            <div class="label-meta">حجوزات</div>
        </a>
    </div>

    {{-- Upgrade card (if not on top plan) --}}
    @if($business->plan?->slug !== 'business')
        <div style="background: white; border: 1px solid var(--teal-100); border-radius: 16px; padding: 12px; margin-bottom: 10px; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -20px; left: -20px; width: 90px; height: 90px; border-radius: 50%; background: var(--teal-50);"></div>
            <div style="position: relative;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: var(--teal); background: var(--teal-50); width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center;"><x-icon name="star" :size="16" stroke="#0D9488"/></span>
                    <div>
                        <div style="font-weight: 900; font-size: 13px; color: var(--navy);">
                            @if($business->plan?->slug === 'pro') ارتقِ إلى Business @else فعّل خطة Pro @endif
                        </div>
                        <div class="label-meta">ظهور مميز · كوبونات · تحليلات</div>
                    </div>
                </div>
                <button class="btn btn-teal btn-full" style="padding: 10px; font-size: 12px; margin-top: 10px;">ترقية الآن</button>
            </div>
        </div>
    @endif

    {{-- Quick links --}}
    <div class="label-meta" style="margin: 6px 4px;">أدوات سريعة</div>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        <a href="{{ route('merchant.qr') }}" class="card" style="padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--navy); background: var(--gray-100); width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center;"><x-icon name="share" :size="14"/></span>
            <div style="flex: 1; font-size: 12px; font-weight: 700;">رمز QR للمتجر</div>
            <span style="color: var(--ink-4);"><x-icon name="chev-l" :size="14"/></span>
        </a>
        <a href="{{ route('business.show', $business) }}" target="_blank" class="card" style="padding: 10px 12px; display: flex; align-items: center; gap: 10px;">
            <span style="color: var(--navy); background: var(--gray-100); width: 30px; height: 30px; border-radius: 9px; display: grid; place-items: center;"><x-icon name="eye" :size="14"/></span>
            <div style="flex: 1; font-size: 12px; font-weight: 700;">عرض صفحة المتجر</div>
            <span style="color: var(--ink-4);"><x-icon name="chev-l" :size="14"/></span>
        </a>
    </div>
</div>

@include('partials.merchant-nav')
@endsection
