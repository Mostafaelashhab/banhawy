@extends('layouts.mobile')

@section('title', 'تتبّع الطلب · بنهاوي')
@section('page-title', 'تتبع طلبك')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ url()->previous() ?: route('home') }}" class="back" aria-label="رجوع"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تتبّع الطلب</div>
</div>

<div class="scroll" style="padding: 16px 14px;">

    {{-- ── Lookup form ────────────────────────────────────── --}}
    <form method="get" action="{{ route('track') }}" class="card" style="padding: 16px;">
        <div class="label-strong" style="margin-bottom: 4px;">تتبّع طلبك</div>
        <p class="muted" style="margin-bottom: 14px; line-height: 1.6;">
            اكتب رقم الطلب اللي وصلك في رسالة الواتساب أو صفحة التأكيد.
        </p>

        <label class="field-label">رقم الطلب</label>
        <div class="field focused">
            <span style="color: var(--teal);"><x-icon name="search" :size="16" stroke="#0D9488"/></span>
            <input type="text" name="code" value="{{ $code }}"
                   placeholder="BNH-XXXXXX"
                   autocomplete="off"
                   autocapitalize="characters"
                   style="text-transform: uppercase; letter-spacing: 1px; direction: ltr; text-align: right;"
                   autofocus required>
        </div>

        <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px; margin-top: 14px;">
            تتبّع الطلب
        </button>
    </form>

    {{-- ── Not-found state ────────────────────────────────── --}}
    @if($notFound)
        <div class="card" style="padding: 24px 16px; text-align: center; margin-top: 12px; border: 1px solid #FECACA;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #FEE2E2; color: #B91C1C; display: grid; place-items: center; margin: 0 auto 10px;">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="label-strong">رقم الطلب مش موجود</div>
            <div class="label-meta" style="margin-top: 4px; line-height: 1.6;">
                تأكد إنك كاتبه صح. الرقم بيكون شكله <span style="font-weight:800;color:var(--navy);direction:ltr;display:inline-block;">BNH-XXXXXX</span>
            </div>
        </div>
    @endif

    {{-- ── Result ─────────────────────────────────────────── --}}
    @if($order)
        @php
            // Map status to visual treatment
            $stages = [
                ['key' => 'new',       'label' => 'استلام الطلب'],
                ['key' => 'preparing', 'label' => 'جاري التحضير'],
                ['key' => 'completed', 'label' => 'تم التسليم'],
            ];
            $isCancelled = $order->status === 'cancelled';
            $statusIndex = match ($order->status) {
                'new'        => 0,
                'preparing'  => 1,
                'completed'  => 2,
                default      => -1,
            };
        @endphp

        {{-- Header card --}}
        <div class="card" style="padding: 16px; margin-top: 12px; background: var(--navy); color: white; border: none; position: relative; overflow: hidden;">
            <div style="position: absolute; top: -30px; left: -30px; width: 130px; height: 130px; border-radius: 50%; background: rgba(13,148,136,.2); filter: blur(40px);"></div>
            <div style="position: relative;">
                <div style="font-size: 10px; color: rgba(255,255,255,.6); font-weight: 800; letter-spacing: 1px; text-transform: uppercase;">رقم الطلب</div>
                <div style="font-weight: 900; font-size: 22px; direction: ltr; text-align: right; letter-spacing: 1px; margin-top: 2px;">{{ $order->code }}</div>
                <div style="margin-top: 12px; font-size: 12px; color: rgba(255,255,255,.7);">
                    من <strong style="color: white;">{{ $order->business->name }}</strong>
                </div>
            </div>
        </div>

        {{-- Status timeline --}}
        <div class="card" style="padding: 18px; margin-top: 12px;">
            <div class="label-strong" style="margin-bottom: 14px;">حالة الطلب</div>

            @if($isCancelled)
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="width: 36px; height: 36px; border-radius: 50%; background: #FEE2E2; color: #B91C1C; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#B91C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </span>
                    <div>
                        <div style="font-weight: 800; color: #B91C1C;">الطلب ملغي</div>
                        <div class="label-meta">للاستفسار، تواصل مع المحل.</div>
                    </div>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 0; position: relative;">
                    @foreach($stages as $i => $stage)
                        @php
                            $isDone    = $statusIndex > $i;
                            $isCurrent = $statusIndex === $i;
                            $reached   = $isDone || $isCurrent;
                        @endphp
                        <div style="display: flex; gap: 12px; align-items: center; padding: 6px 0; position: relative;">
                            {{-- Connector line --}}
                            @if($i < count($stages) - 1)
                                <div style="position: absolute; top: 38px; right: 17px; bottom: -6px; width: 2px; background: {{ $isDone ? 'var(--teal)' : 'var(--gray-200)' }};"></div>
                            @endif
                            <span style="width: 36px; height: 36px; border-radius: 50%;
                                         background: {{ $reached ? 'var(--teal)' : 'var(--gray-100)' }};
                                         color: {{ $reached ? 'white' : 'var(--ink-4)' }};
                                         display: grid; place-items: center; flex-shrink: 0; z-index: 1;
                                         box-shadow: {{ $isCurrent ? '0 0 0 4px rgba(13,148,136,.18)' : 'none' }};">
                                @if($isDone)
                                    <x-icon name="check" :size="16" stroke="white" w="3"/>
                                @elseif($isCurrent)
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: white;"></span>
                                @else
                                    <span style="font-weight: 800; font-size: 12px;">{{ $i + 1 }}</span>
                                @endif
                            </span>
                            <div>
                                <div style="font-weight: 800; font-size: 13px; color: {{ $reached ? 'var(--navy)' : 'var(--ink-4)' }};">{{ $stage['label'] }}</div>
                                @if($isCurrent)
                                    <div class="label-meta" style="color: var(--teal-600); font-weight: 700;">الحالة الحالية</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Order details --}}
        <div class="card" style="padding: 14px; margin-top: 12px;">
            <div class="label-strong" style="margin-bottom: 10px;">تفاصيل الطلب</div>
            @foreach($order->items as $item)
                <div style="display: flex; justify-content: space-between; padding: 5px 0; font-size: 12px;">
                    <span style="color: var(--ink-2); font-weight: 700;">{{ $item['name'] }} <span class="label-meta">×{{ $item['qty'] }}</span></span>
                    <span style="font-weight: 800;">{{ $item['line_total'] }}ج</span>
                </div>
            @endforeach
            <div class="sep" style="margin: 8px 0;"></div>
            <div style="display: flex; justify-content: space-between;">
                <span class="label-strong">الإجمالي</span>
                <span style="font-weight: 900; font-size: 16px; color: var(--navy);">{{ $order->total }}ج</span>
            </div>
            <div class="label-meta" style="margin-top: 8px;">طُلب {{ $order->placed_at?->diffForHumans() }}</div>
        </div>

        {{-- Contact business --}}
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 12px;">
            <a href="{{ route('business.show', $order->business) }}" class="btn btn-line" style="padding: 11px;">
                <x-icon name="eye" :size="14"/> صفحة المحل
            </a>
            <a href="{{ route('business.whatsapp', ['business' => $order->business, 'message' => 'مرحبًا، بخصوص طلبي ' . $order->code]) }}" class="btn btn-wa" style="padding: 11px;">
                <x-icon name="whatsapp" :size="14" stroke="white"/> تواصل مع المحل
            </a>
        </div>
    @endif
</div>

@include('partials.visitor-nav')
@endsection
