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
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->customer_phone) }}?text={{ rawurlencode('تأكيد طلبك ' . ($order->code ?: '#' . $order->id) . ' من ' . $business->name) }}" target="_blank" class="btn btn-wa" style="flex: 1; padding: 8px; font-size: 11px;">
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
        <div class="card card-pad" style="text-align: center; padding: 32px 16px; color: var(--ink-3); grid-column: 1 / -1;">
            <div style="margin-bottom: 8px; color: var(--ink-4);"><x-icon name="cart" :size="32"/></div>
            <div class="label-strong">لا توجد طلبات في هذه القائمة</div>
        </div>
    @endforelse
    </div>
</div>

@include('partials.merchant-nav')
@endsection
