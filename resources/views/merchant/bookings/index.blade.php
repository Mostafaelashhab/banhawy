@extends('layouts.mobile')

@section('title', 'الحجوزات · ' . $business->name)
@section('page-title', 'الحجوزات')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">الحجوزات</div>
    <button class="ico-btn"><x-icon name="plus" :size="18"/></button>
</div>

{{-- Mini week strip --}}
<div style="padding: 0 10px 10px;">
    <div style="background: white; border: 1px solid var(--line); border-radius: 14px; padding: 8px 6px; display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
        @php $arabicDays = ['ح','ن','ث','ر','خ','ج','س']; @endphp
        @foreach($weekDays as $i => $d)
            <a href="{{ route('merchant.bookings.index', ['date' => $d['date']->toDateString()]) }}"
               style="display: flex; flex-direction: column; align-items: center; gap: 2px; padding: 5px 0; border-radius: 10px; {{ $d['date']->isSameDay($date) ? 'background: var(--navy); color: white;' : 'color: var(--ink-2);' }}">
                <span class="tiny" style="{{ $d['date']->isSameDay($date) ? 'color: rgba(255,255,255,.6);' : 'color: var(--ink-3);' }} font-weight: 700;">{{ $arabicDays[$d['date']->dayOfWeek] }}</span>
                <span style="font-weight: 900; font-size: 13px;">{{ $d['date']->format('j') }}</span>
                @if($d['count'] > 0)
                    <span style="width: 4px; height: 4px; border-radius: 50%; background: var(--teal);"></span>
                @endif
            </a>
        @endforeach
    </div>
</div>

<div class="scroll" style="padding: 4px 14px 14px;">
    <div class="label-meta" style="margin: 4px 4px 8px;">
        {{ $date->isSameDay(today()) ? 'اليوم' : $date->translatedFormat('l j F') }} · {{ $bookings->count() }} حجوزات
    </div>

    @forelse($bookings as $booking)
        <div class="card" style="padding: 12px; display: flex; gap: 12px; margin-bottom: 8px;">
            @php
                $timeColor = match($booking->status) {
                    'confirmed' => ['bg' => 'var(--teal-50)',           'fg' => 'var(--teal)',     'sub' => 'var(--teal-600)'],
                    'new'       => ['bg' => 'rgba(37,211,102,.12)',     'fg' => 'var(--wa-600)',   'sub' => 'var(--wa-600)'],
                    'cancelled' => ['bg' => '#FEE2E2',                  'fg' => '#B91C1C',         'sub' => '#B91C1C'],
                    default     => ['bg' => 'var(--gray-100)',          'fg' => 'var(--ink-2)',    'sub' => 'var(--ink-3)'],
                };
            @endphp
            <div style="text-align: center; background: {{ $timeColor['bg'] }}; border-radius: 10px; padding: 6px 8px; min-width: 54px;">
                <div style="font-weight: 900; font-size: 13px; color: {{ $timeColor['fg'] }};">{{ $booking->booked_at->format('h:i') }}</div>
                <div class="tiny" style="color: {{ $timeColor['sub'] }};">{{ $booking->booked_at->format('a') === 'am' ? 'ص' : 'م' }}</div>
            </div>
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 800; font-size: 13px;">{{ $booking->customer_name }}</span>
                    @if($booking->status === 'confirmed')
                        <span class="chip teal" style="padding: 1px 7px; font-size: 9px;">مؤكد</span>
                    @elseif($booking->status === 'new')
                        <span class="chip wa-tint" style="padding: 1px 7px; font-size: 9px;">جديد</span>
                    @elseif($booking->status === 'cancelled')
                        <span class="chip closed" style="padding: 1px 7px; font-size: 9px;">ملغي</span>
                    @else
                        <span class="chip" style="padding: 1px 7px; font-size: 9px;">مكتمل</span>
                    @endif
                </div>
                <div class="label-meta">
                    {{ $booking->party_size }} {{ $booking->party_size === 1 ? 'شخص' : 'أفراد' }}
                    @if($booking->service) · {{ $booking->service }} @endif
                </div>

                @if(in_array($booking->status, ['new', 'confirmed']))
                    <div style="display: flex; gap: 5px; margin-top: 8px;">
                        <a href="https://wa.me/{{ \App\Support\Phone::forWhatsapp($booking->customer_phone) }}" target="_blank" class="btn btn-wa" style="padding: 5px 9px; font-size: 10px;">
                            <x-icon name="whatsapp" :size="11" stroke="white"/> واتساب
                        </a>
                        <form method="post" action="{{ route('merchant.bookings.update', $booking) }}" style="display: contents;">
                            @csrf @method('PATCH')
                            @if($booking->status === 'new')
                                <button name="status" value="confirmed" class="btn btn-line" style="padding: 5px 9px; font-size: 10px;">تأكيد</button>
                            @endif
                            <button name="status" value="cancelled" class="btn btn-line" style="padding: 5px 9px; font-size: 10px;">إلغاء</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="bookings-empty">
            <div class="bookings-empty-illu">
                <svg viewBox="0 0 96 96" width="96" height="96" fill="none" stroke="#0D9488" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="14" y="20" width="68" height="62" rx="10" fill="rgba(13,148,136,.06)"/>
                    <line x1="14" y1="38" x2="82" y2="38"/>
                    <line x1="32" y1="14" x2="32" y2="26"/>
                    <line x1="64" y1="14" x2="64" y2="26"/>
                    <circle cx="32" cy="54" r="3" fill="#0D9488" stroke="none"/>
                    <circle cx="48" cy="54" r="3" fill="#0D9488" stroke="none"/>
                    <circle cx="64" cy="54" r="3" fill="#0D9488" stroke="none"/>
                    <circle cx="32" cy="68" r="3" fill="#0D9488" stroke="none"/>
                    <circle cx="48" cy="68" r="3" fill="#0D9488" stroke="none"/>
                </svg>
            </div>
            <div class="bookings-empty-title">مفيش حجوزات في اليوم ده</div>
            <p class="bookings-empty-sub">لما عميل يحجز معاد جديد هيظهر هنا تلقائياً.</p>

            <div class="bookings-empty-actions">
                <a href="{{ route('merchant.qr') }}" class="btn btn-teal" style="padding: 11px; font-size: 13px; flex: 1;">
                    <x-icon name="share" :size="14" stroke="white"/> شارك متجرك
                </a>
                <a href="{{ route('business.show', $business) }}" target="_blank" class="btn btn-line" style="padding: 11px; font-size: 13px; flex: 1;">
                    <x-icon name="eye" :size="14"/> عرض المتجر
                </a>
            </div>
        </div>

        <style>
        .bookings-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 28px 18px 24px;
        }
        .bookings-empty-illu {
            margin-bottom: 14px;
            animation: bookingsEmptyFloat 4s ease-in-out infinite;
        }
        @keyframes bookingsEmptyFloat {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        .bookings-empty-title { font-weight: 900; font-size: 17px; color: var(--ink-1); }
        .bookings-empty-sub {
            color: var(--ink-3);
            font-size: 13px;
            line-height: 1.7;
            margin: 6px 0 0;
            max-width: 320px;
            font-weight: 600;
        }
        .bookings-empty-actions {
            display: flex;
            gap: 8px;
            width: 100%;
            max-width: 340px;
            margin-top: 18px;
        }
        </style>
    @endforelse
</div>

@include('partials.merchant-nav')
@endsection
