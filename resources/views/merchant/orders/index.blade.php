@extends('layouts.mobile')

@section('title', 'الطلبات · ' . $business->name)
@section('page-title', 'الطلبات')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">الطلبات</div>
    <button class="ico-btn"><x-icon name="search" :size="18"/></button>
</div>

<div style="padding: 0 14px 8px; display: flex; gap: 6px; overflow-x: auto;">
    @foreach(['new' => 'جديد', 'preparing' => 'قيد التنفيذ', 'completed' => 'مكتمل', 'cancelled' => 'ملغي'] as $key => $label)
        <a href="{{ route('merchant.orders.index', ['status' => $key]) }}" class="chip @if($status === $key) active @endif">
            {{ $label }}
            @if($counts[$key] > 0) · {{ $counts[$key] }} @endif
        </a>
    @endforeach
</div>

<div class="scroll" style="padding: 6px 14px 14px;">
    <div class="result-grid">
    @forelse($orders as $order)
        <div class="card" style="padding: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-weight: 800; font-size: 13px; direction: ltr; letter-spacing: .5px;">{{ $order->code ?: '#' . str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</span>
                        @if($order->status === 'new')
                            <span class="chip wa-tint" style="padding: 1px 7px; font-size: 9px;">جديد</span>
                        @elseif($order->status === 'preparing')
                            <span class="chip teal" style="padding: 1px 7px; font-size: 9px;">قيد التحضير</span>
                        @elseif($order->status === 'completed')
                            <span class="chip open" style="padding: 1px 7px; font-size: 9px;">مكتمل</span>
                        @else
                            <span class="chip closed" style="padding: 1px 7px; font-size: 9px;">ملغي</span>
                        @endif
                    </div>
                    <div style="font-weight: 700; font-size: 12px; margin-top: 4px;">{{ $order->customer_name }}</div>
                    <div class="label-meta" style="direction: ltr; text-align: right;">{{ $order->customer_phone }}</div>
                </div>
                <div style="text-align: left;">
                    <div style="font-weight: 900; font-size: 14px; color: var(--navy);">{{ $order->total }}ج</div>
                    <div class="label-meta">{{ $order->placed_at?->diffForHumans() }}</div>
                </div>
            </div>

            <div style="background: var(--gray-100); border-radius: 10px; padding: 8px 10px; margin-top: 8px; font-size: 11px; font-weight: 600; color: var(--gray-700); line-height: 1.6;">
                {{ collect($order->items)->map(fn ($it) => $it['qty'] . '× ' . $it['name'])->implode(' · ') }}
            </div>

            @if(in_array($order->status, ['new', 'preparing']))
                <div style="display: flex; gap: 6px; margin-top: 10px;">
                    <a href="https://wa.me/{{ \App\Support\Phone::forWhatsapp($order->customer_phone) }}?text={{ rawurlencode('تأكيد طلبك ' . ($order->code ?: '#' . $order->id) . ' من ' . $business->name) }}" target="_blank" class="btn btn-wa" style="flex: 1; padding: 8px; font-size: 11px;">
                        <x-icon name="whatsapp" :size="12" stroke="white"/> تواصل واتساب
                    </a>
                    <form method="post" action="{{ route('merchant.orders.update', $order) }}" style="display: contents;">
                        @csrf @method('PATCH')
                        @if($order->status === 'new')
                            <button name="status" value="preparing" class="btn btn-line" style="padding: 8px 10px; font-size: 11px;">قبول</button>
                        @else
                            <button name="status" value="completed" class="btn btn-line" style="padding: 8px 10px; font-size: 11px;">تم</button>
                        @endif
                    </form>
                    <form method="post" action="{{ route('merchant.orders.update', $order) }}" style="display: contents;">
                        @csrf @method('PATCH')
                        <button name="status" value="cancelled" class="btn btn-line" style="padding: 8px 10px; font-size: 11px;">رفض</button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        @php
            $emptyCopy = [
                'new'       => ['title' => 'مفيش طلبات جديدة دلوقتي', 'sub' => 'أول ما عميل يطلب من متجرك هيوصلك هنا.'],
                'preparing' => ['title' => 'مفيش طلبات قيد التنفيذ',   'sub' => 'الطلبات اللي بتقبلها هتظهر هنا.'],
                'completed' => ['title' => 'لسه مفيش طلبات مكتملة',    'sub' => 'الطلبات اللي خلصتها هتتجمع هنا تلقائياً.'],
                'cancelled' => ['title' => 'مفيش طلبات ملغية',          'sub' => 'كله تمام · مفيش طلبات اترفضت.'],
            ];
            $copy = $emptyCopy[$status] ?? $emptyCopy['new'];
        @endphp
        <div class="orders-empty">
            <div class="orders-empty-illu">
                <svg viewBox="0 0 96 96" width="96" height="96" fill="none" stroke="#0D9488" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="14" y="22" width="68" height="58" rx="10" fill="rgba(13,148,136,.06)"/>
                    <path d="M22 32h8l6 26h28l6-20H32"/>
                    <circle cx="38" cy="68" r="3.5" fill="#0D9488" stroke="none"/>
                    <circle cx="62" cy="68" r="3.5" fill="#0D9488" stroke="none"/>
                </svg>
            </div>
            <div class="orders-empty-title">{{ $copy['title'] }}</div>
            <p class="orders-empty-sub">{{ $copy['sub'] }}</p>

            @if($status === 'new')
                <div class="orders-empty-actions">
                    <a href="{{ route('merchant.qr') }}" class="btn btn-teal" style="padding: 11px; font-size: 13px; flex: 1;">
                        <x-icon name="share" :size="14" stroke="white"/> شارك متجرك
                    </a>
                    <a href="{{ route('business.show', $business) }}" target="_blank" class="btn btn-line" style="padding: 11px; font-size: 13px; flex: 1;">
                        <x-icon name="eye" :size="14"/> عرض المتجر
                    </a>
                </div>
            @endif
        </div>

        <style>
        .orders-empty {
            grid-column: 1 / -1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 28px 18px 24px;
        }
        .orders-empty-illu {
            margin-bottom: 14px;
            animation: ordersEmptyFloat 4s ease-in-out infinite;
        }
        @keyframes ordersEmptyFloat {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        .orders-empty-title { font-weight: 900; font-size: 17px; color: var(--ink-1); }
        .orders-empty-sub {
            color: var(--ink-3);
            font-size: 13px;
            line-height: 1.7;
            margin: 6px 0 0;
            max-width: 320px;
            font-weight: 600;
        }
        .orders-empty-actions {
            display: flex;
            gap: 8px;
            width: 100%;
            max-width: 340px;
            margin-top: 18px;
        }
        </style>
    @endforelse
    </div>
</div>

@include('partials.merchant-nav')
@endsection
